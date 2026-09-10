#!/usr/bin/env bash
#
# Tests for the entrypoint scripts that ship in the fpm image.
#
#   ./tests/run.sh
#
# queue_worker.sh is the one covered here, and it is worth covering because
# everything it does fails silently when it breaks. If it stops restarting the
# worker, the queue simply stops draining inside a container Kubernetes reports
# as healthy. If it stops forwarding SIGTERM, every rollout kills a job
# part-way — and the jobs on the default queue send mail and talk to PayPal. If
# it restarts a worker that cannot start at all, a crashloop becomes a hot loop
# in a container that never restarts. None of those announce themselves.
#
# `php` is stubbed (see stubs/php), so nothing here needs a PHP toolchain, a
# database or a queue. Runtimes are seconds rather than the chart's five
# minutes, via QUEUE_WORKER_MIN_RUNTIME.
set -uo pipefail

cd "$(dirname "$0")"

SUPERVISOR="$(cd .. && pwd)/queue_worker.sh"
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

echo
if [[ "$failures" -gt 0 ]]; then
    echo "$failures assertion(s) failed." >&2
    exit 1
fi
echo "All entrypoint assertions passed."
