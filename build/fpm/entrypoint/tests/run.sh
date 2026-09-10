#!/usr/bin/env bash
#
# Tests for the entrypoint scripts that ship in the fpm image.
#
#   ./tests/run.sh
#
# Both PID-1 scripts are covered here, and they are worth covering because
# everything they do fails silently when it breaks. If it stops restarting the
# worker, the queue simply stops draining inside a container Kubernetes reports
# as healthy. If it stops forwarding SIGTERM, every rollout kills a job
# part-way — and the jobs on the default queue send mail and talk to PayPal. If
# it restarts a worker that cannot start at all, a crashloop becomes a hot loop
# in a container that never restarts. If php_daemon.sh stops translating the
# stop signal, a daemon container becomes unstoppable and every rollout waits
# out the full grace period. None of those announce themselves.
#
# `php` is stubbed (see stubs/php), so nothing here needs a PHP toolchain, a
# database or a queue. Runtimes are seconds rather than the chart's five
# minutes, via QUEUE_WORKER_MIN_RUNTIME.
set -uo pipefail

cd "$(dirname "$0")"

SUPERVISOR="$(cd .. && pwd)/queue_worker.sh"
DAEMON="$(cd .. && pwd)/php_daemon.sh"
STUBS="$PWD/stubs"

failures=0

pass() { printf '  ok   %s\n' "$1"; }
fail() {
    printf '  FAIL %s\n' "$1" >&2
    shift
    for line in "$@"; do printf '       %s\n' "$line" >&2; done
    failures=$((failures + 1))
}

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

# Run the supervisor with the stub on PATH under a hard watchdog, so a test can
# tell "still supervising when we gave up" from "exited on its own".
#
# Sets `status`: 137 means the watchdog had to SIGKILL it, which the supervisor
# never produces itself. Anything else is its own exit status.
#
# SIGKILL rather than `timeout`, whose exit status for a timed-out command is
# 124 under GNU coreutils and 143 under busybox — and CI runs on alpine.
supervise_for() {
    local limit="$1"
    shift

    # In a subshell with stderr closed, and the status carried back out on
    # stdout: bash announces a job it had to report as killed ("Killed ...") on
    # the *shell's* stderr, not the job's, and that lands in the middle of the
    # test output where it reads like a failure.
    status="$(
        exec 2>/dev/null

        PATH="$STUBS:$PATH" "$SUPERVISOR" "$@" >"$WORK/out" 2>&1 &
        local pid=$!

        (sleep "$limit" && kill -s KILL "$pid" 2>/dev/null) &
        local watchdog=$!

        wait "$pid"
        local outcome=$?

        kill "$watchdog" 2>/dev/null
        echo "$outcome"
    )"
}

echo "A worker that recycles is restarted:"

# The whole point of the script. The stub exits by itself after a second, over
# and over; the supervisor must keep replacing it rather than exiting with it.
export STUB_ARGV_FILE="$WORK/recycle.argv" STUB_RUN_SECONDS=1 QUEUE_WORKER_MIN_RUNTIME=0
supervise_for 5 --max-time=1 --queue=default

runs="$(grep -c . "$STUB_ARGV_FILE" 2>/dev/null || echo 0)"
if [[ "$status" -ne 137 ]]; then
    fail "supervisor exited on its own (status $status) after $runs worker runs" \
        "a self-exiting worker must be restarted, not propagated" \
        "$(cat "$WORK/out")"
elif [[ "$runs" -lt 3 ]]; then
    fail "only $runs worker invocations in 5s" "expected each 1s recycle to be replaced"
else
    pass "restarted the worker $runs times in 5s"
fi

echo
echo "Arguments reach queue:work verbatim:"

# The chart passes the connection name, the queue names and --max-time through
# this script. A supervisor that reordered or dropped one would leave a worker
# serving the wrong queue, and nothing else here would catch it — the broadcast
# worker in particular is identified only by its arguments.
export STUB_ARGV_FILE="$WORK/passthrough.argv"
supervise_for 3 redis --tries=1 --sleep=1 --max-time=300 --queue=broadcasts

expected="artisan queue:work redis --tries=1 --sleep=1 --max-time=300 --queue=broadcasts"
actual="$(head -1 "$STUB_ARGV_FILE" 2>/dev/null)"
if [[ "$actual" == "$expected" ]]; then
    pass "argv passed through unchanged"
else
    fail "argv was rewritten" "expected: $expected" "actual:   $actual"
fi

echo
echo "A worker that fails immediately is not restarted:"

# The hazard the supervisor introduces. Without a floor on the runtime, a worker
# that cannot start at all — a bad connection name, an unreadable .env, a
# missing config mount — is respawned in a tight loop inside a container that
# never restarts and so never looks unhealthy. The supervisor has to step aside
# and let the pod crashloop, which is the honest signal.
export STUB_ARGV_FILE="$WORK/fastfail.argv" STUB_RUN_SECONDS=0 STUB_EXIT_STATUS=1 QUEUE_WORKER_MIN_RUNTIME=30
supervise_for 10 --queue=default

runs="$(grep -c . "$STUB_ARGV_FILE" 2>/dev/null || echo 0)"
if [[ "$status" -eq 137 ]]; then
    fail "supervisor kept restarting a worker that fails on startup" \
        "$runs invocations, in a container Kubernetes still reports as healthy"
elif [[ "$status" -eq 0 ]]; then
    fail "supervisor exited 0 on a startup failure" \
        "the container would be reported 'Completed' rather than crashlooping"
elif [[ "$runs" -ne 1 ]]; then
    fail "supervisor retried a startup failure $runs times" "expected exactly one attempt"
else
    pass "gave up after one attempt, exit status $status"
fi

echo
echo "SIGTERM reaches the worker and the drain is awaited:"

# The contract terminationGracePeriodSeconds is set for. The supervisor is PID 1
# in the container, so a SIGTERM it does not forward never reaches the worker at
# all — and one it forwards without waiting is no better, because PID 1 exiting
# takes the worker down with it, mid-job.
#
# This also covers the ordering inside the script: the shutdown check has to come
# before the runtime floor, or a worker signalled while its queue is empty exits
# at once and gets misread as a startup failure. The stub runs for 1s against a
# 30s floor here, so a wrong order shows up as a non-zero status below.
export STUB_ARGV_FILE=/dev/null STUB_EVENT_FILE="$WORK/term.events" \
    STUB_RUN_SECONDS=30 STUB_DRAIN_SECONDS=2 QUEUE_WORKER_MIN_RUNTIME=30
unset STUB_EXIT_STATUS

PATH="$STUBS:$PATH" "$SUPERVISOR" --queue=default >"$WORK/term.out" 2>&1 &
supervisor_pid=$!

# Long enough for the stub to have installed its trap.
sleep 1
kill -s TERM "$supervisor_pid"

start=$SECONDS
wait "$supervisor_pid"
status=$?
waited=$((SECONDS - start))

if ! grep -q "term-received" "$STUB_EVENT_FILE" 2>/dev/null; then
    fail "the worker never received SIGTERM" \
        "an unforwarded signal is a job killed when the grace period runs out"
elif ! grep -q "drained" "$STUB_EVENT_FILE" 2>/dev/null; then
    fail "the supervisor exited before the worker finished draining" \
        "PID 1 exiting takes the worker down with it, mid-job"
elif [[ "$waited" -lt 2 ]]; then
    fail "supervisor returned after ${waited}s, faster than the stub's 2s drain" \
        "it cannot have waited for the worker"
elif [[ "$status" -ne 0 ]]; then
    fail "supervisor exited $status on a clean shutdown" \
        "expected 0; a non-zero status here means the runtime floor read the shutdown as a failure"
else
    pass "forwarded SIGTERM and waited ${waited}s for the drain"
fi

# ---------------------------------------------------------------------------
# php_daemon.sh
# ---------------------------------------------------------------------------

# Whether this shell was entered with SIGQUIT already ignored. That is
# inherited by everything it starts and cannot be undone from a shell — signals
# ignored on entry cannot be trapped or reset — so the SIGQUIT test below would
# report a perfectly correct wrapper as broken. busybox ash does exactly this
# when it is PID 1, which is how a CI job's container may well be started
# (measured: SigIgn 0x4 for every descendant under `docker run alpine sh -c`,
# and 0 when the script is exec'd directly).
sigquit_ignored() {
    local mask
    mask="$(awk '/^SigIgn:/ {print $2}' "/proc/$$/status" 2>/dev/null)"
    [[ -n "$mask" ]] && ((0x$mask & 0x4))
}

# Start the wrapper with SIGQUIT restored to its default disposition, which is
# what it has as PID 1 in a container. Only reached when the harness needs it,
# so the normal path depends on nothing but a shell.
run_daemon() {
    if ! sigquit_ignored; then
        PATH="$STUBS:$PATH" "$DAEMON" "$@"
        return $?
    fi

    if ! command -v python3 >/dev/null 2>&1; then
        # Louder than a skip on purpose: silently not testing this is how the
        # reverb wedge would come back.
        fail "cannot deliver SIGQUIT: this shell was entered with it ignored" \
            "and no python3 is available to restore the default disposition" \
            "run the tests from a shell that is not busybox ash as PID 1"
        return 111
    fi

    PATH="$STUBS:$PATH" python3 -c 'import os, signal, sys
signal.signal(signal.SIGQUIT, signal.SIG_DFL)
os.execvp(sys.argv[1], sys.argv[1:])' "$DAEMON" "$@"
}
#
# The bug it exists for: the CRI stops a container with the *image's*
# STOPSIGNAL, which here is SIGQUIT (inherited from php-fpm), and a PID 1 with
# no handler for a signal has no default action applied to it. `artisan
# reverb:start` subscribes to SIGINT/SIGTERM/SIGTSTP and so did nothing at all
# with the SIGQUIT it was sent — 301s in Terminating against a 300s grace
# period, in production on 2026-09-10.

echo
echo "SIGQUIT stops a php daemon:"

# The regression test proper. Sent the signal the container runtime really
# sends, the wrapper has to turn it into the one the command handles.
export STUB_ARGV_FILE=/dev/null STUB_EVENT_FILE="$WORK/quit.events" \
    STUB_RUN_SECONDS=30 STUB_DRAIN_SECONDS=2
unset STUB_EXIT_STATUS

# Run in the foreground and signalled from the stub (STUB_SIGNAL_PARENT), not
# backgrounded and signalled from here. A shell sets SIGINT and SIGQUIT to
# SIG_IGN for asynchronous commands, and a signal ignored on entry cannot be
# trapped — so a `&` here would give the wrapper a disposition it never has as
# PID 1 and fail against correct code. `set -m` fixes that only where job
# control can really be established: under alpine with no controlling terminal
# the child still comes up with SigIgn 0x4, while `$-` claims `m`. The stub's
# header has the detail.
export STUB_SIGNAL_PARENT=QUIT

start=$SECONDS
run_daemon artisan reverb:start --port=8081 >"$WORK/quit.out" 2>&1
status=$?
waited=$((SECONDS - start))
unset STUB_SIGNAL_PARENT

if [[ "$status" -eq 111 ]]; then
    : # run_daemon already reported why it could not deliver the signal
elif ! grep -q "term-received" "$WORK/quit.events" 2>/dev/null; then
    fail "SIGQUIT never reached the command as SIGTERM" \
        "this is the reverb wedge: the daemon would run until SIGKILL"
elif ! grep -q "drained" "$WORK/quit.events" 2>/dev/null; then
    fail "the wrapper exited before the command finished shutting down" \
        "PID 1 exiting takes the child with it, mid-shutdown"
elif [[ "$waited" -lt 2 ]]; then
    fail "wrapper returned after ${waited}s, faster than the stub's 2s drain" \
        "it cannot have waited for the child"
elif [[ "$status" -ne 0 ]]; then
    fail "wrapper exited $status on a clean stop" \
        "expected 0; a rollout would log every container as having failed"
else
    pass "translated SIGQUIT to SIGTERM and waited ${waited}s"
fi

echo
echo "SIGTERM stops a php daemon too:"

# What a `lifecycle.stopSignal`, a plain `kill`, or the compose file's
# stop_signal override sends. Trapped for the same reason and must behave the
# same way, so that fixing the signal at the platform level later is a no-op
# here rather than a second code path.
export STUB_EVENT_FILE="$WORK/term-daemon.events" STUB_SIGNAL_PARENT=TERM

PATH="$STUBS:$PATH" "$DAEMON" artisan reverb:start >"$WORK/term-daemon.out" 2>&1
status=$?
unset STUB_SIGNAL_PARENT

if ! grep -q "drained" "$WORK/term-daemon.events" 2>/dev/null; then
    fail "SIGTERM did not reach the command, or the drain was not awaited"
elif [[ "$status" -ne 0 ]]; then
    fail "wrapper exited $status on a clean stop" "expected 0"
else
    pass "forwarded SIGTERM and awaited the drain"
fi

echo
echo "Arguments reach php verbatim:"

# The wrapper is generic — it prepends nothing, unlike queue-worker. A dropped
# or reordered argument would start the wrong command, or the right one on the
# wrong port, and the probe would still pass.
export STUB_ARGV_FILE="$WORK/daemon.argv" STUB_EVENT_FILE=/dev/null STUB_RUN_SECONDS=0
unset STUB_DRAIN_SECONDS

PATH="$STUBS:$PATH" "$DAEMON" artisan reverb:start --port=8081 >/dev/null 2>&1

expected="artisan reverb:start --port=8081"
actual="$(head -1 "$WORK/daemon.argv" 2>/dev/null)"
if [[ "$actual" == "$expected" ]]; then
    pass "argv passed through unchanged"
else
    fail "argv was rewritten" "expected: $expected" "actual:   $actual"
fi

echo
echo "A daemon that fails on its own is not masked:"

# The wrapper must not become a supervisor. A daemon that cannot start has to
# take the container down with it so Kubernetes reports the crashloop; swallowing
# the status would leave a container reported "Completed" and a component simply
# absent.
export STUB_ARGV_FILE=/dev/null STUB_RUN_SECONDS=0 STUB_EXIT_STATUS=3

PATH="$STUBS:$PATH" "$DAEMON" artisan reverb:start >/dev/null 2>&1
status=$?

if [[ "$status" -eq 3 ]]; then
    pass "propagated the command's exit status (3)"
else
    fail "wrapper exited $status for a command that exited 3" \
        "a startup failure has to reach Kubernetes as a failure"
fi
unset STUB_EXIT_STATUS

echo
if [[ "$failures" -gt 0 ]]; then
    echo "$failures assertion(s) failed." >&2
    exit 1
fi
echo "All entrypoint assertions passed."
