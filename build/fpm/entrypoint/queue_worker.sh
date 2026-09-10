#!/bin/bash
#
# Supervise `artisan queue:work` so the worker process can recycle without the
# container exiting.
#
#   queue-worker --backoff=60 --tries=5 --max-time=300 --queue=default
#   queue-worker redis --tries=1 --max-time=300 --queue=broadcasts
#
# Every argument is passed straight through to `artisan queue:work`.
#
# Why the worker recycles at all: queue:work is a long-lived daemon holding one
# PDO connection, and nothing inside a held connection notices a Postgres
# primary changing under it. After a CNPG failover the worker keeps its socket to
# the old primary; the database queue driver opens a transaction per pop(), and
# once Laravel's transaction counter desyncs from PDO's state every subsequent
# pop() throws "There is already an active transaction" for ever — reported to
# GlitchTip once a second, with pgrep keeping the liveness probe green so
# Kubernetes never restarts it (issue 1387, the cluster outage of 2026-09-09).
# --max-time is the clean self-exit that ends that state without a human.
#
# Why that self-exit needs supervising rather than being the container's own
# process: every container in a Deployment has restartPolicy Always, and the
# kubelet applies CrashLoopBackOff pacing to *every* restart — exit code 0
# included. Measured in production on 2026-09-10, with --max-time=300 as the
# container command: 301s of work, then 81-95s of backoff before the next start.
# No worker at all for ~22% of the time, a restart count climbing ten an hour
# per container, and a continuous stream of "Back-off restarting failed
# container" warnings for a worker that had not failed at all.
#
# So the recycle happens here instead. The child exits every five minutes, this
# process does not, and Kubernetes sees one container that never restarts.

# Deliberately no `set -e`. The whole job of this script is to outlive a child
# that exits, and `wait` reports that child's status — under -e a worker exiting
# non-zero would kill the supervisor, which is the failure mode it exists to
# prevent.
set -uo pipefail

# A recycle is a long run followed by a clean exit. Anything that exits faster
# than this is a real failure — a bad connection name, an unreadable .env, a
# missing config mount — and restarting it in a tight loop would turn a visible
# CrashLoopBackOff into an invisible hot loop inside a container that Kubernetes
# still calls healthy. Below this threshold the supervisor gets out of the way
# and lets the pod crashloop, which is the honest signal.
#
# Overridable so this stays usable with a shorter --max-time than the chart's
# 300s; keep an order of magnitude between the two.
MIN_RUNTIME="${QUEUE_WORKER_MIN_RUNTIME:-30}"

shutdown=0
child=

_forward() {
    shutdown=1

    if [[ -n "$child" ]]; then
        # queue:work traps this, finishes the job in hand and exits. Jobs on the
        # default queue send mail and talk to PayPal, so being killed part-way
        # is worth avoiding; the pod's terminationGracePeriodSeconds is what
        # bounds the wait.
        kill -s TERM "$child" 2>/dev/null || true
    fi
}

# Kubernetes always sends SIGTERM. This image's STOPSIGNAL is SIGQUIT, inherited
# from the php-fpm base image, which is what `docker stop` sends. Both are
# trapped for the same reason entrypoint_production.sh traps both: an untrapped
# signal on this bash PID 1 kills the shell immediately, and the kernel SIGKILLs
# the child the instant PID 1 exits — skipping the graceful drain entirely.
trap _forward SIGTERM SIGQUIT

while :; do
    started=$SECONDS
    status=0

    php artisan queue:work "$@" &
    child=$!

    # `wait` returns as soon as a trapped signal has been handled, while the
    # child is still draining the job in hand — so a return from `wait` does not
    # mean the child is gone, and its status cannot be read as the child's.
    # `kill -0` is the test that can: keep waiting while the child still exists,
    # and the last status read is then genuinely its exit status.
    while :; do
        wait "$child" && status=0 || status=$?
        kill -0 "$child" 2>/dev/null || break
    done

    ran_for=$((SECONDS - started))
    child=

    # Checked before the runtime guard below: a worker signalled while its queue
    # is empty exits at once, and that fast exit is a correct shutdown rather
    # than the failure the guard is looking for.
    if ((shutdown)); then
        echo "queue-worker: stopped after ${ran_for}s (status $status)."
        exit 0
    fi

    if ((ran_for < MIN_RUNTIME)); then
        echo "queue-worker: queue:work exited after ${ran_for}s with status $status," >&2
        echo "queue-worker: too fast to be a --max-time recycle. Not restarting it —" >&2
        echo "queue-worker: this is a real failure and Kubernetes should report it." >&2
        exit $((status == 0 ? 1 : status))
    fi

    if ((status != 0)); then
        echo "queue-worker: queue:work exited with status $status after ${ran_for}s; restarting." >&2
    fi
done
