#!/bin/bash
#
# Run a long-lived `php` command as a container's process, so that stopping the
# container actually stops the command.
#
#   php-daemon artisan reverb:start --port=8081
#
# Every argument is passed straight through to `php`.
#
# Two things make `php` unsafe as a daemon container's own command, and they
# compound into a process that cannot be stopped at all.
#
# The signal is not the one you would assume. Kubernetes does not send SIGTERM
# of its own accord: the CRI sends the *image's* STOPSIGNAL, and this image
# inherits `STOPSIGNAL SIGQUIT` from php-fpm — where SIGQUIT is the graceful
# stop and SIGTERM the abrupt one, so it is the right default for the fpm
# container and the wrong one for everything else in the image.
# `.spec.containers[].lifecycle.stopSignal` says otherwise per container, but it
# sits behind the ContainerStopSignals feature gate, which is off on this
# cluster — the API reports it ALPHA and disabled on 1.35.8, and a spec carrying
# the field comes back from a server dry-run with it silently stripped.
#
# PID 1 ignores what it does not handle. For the init process of a PID namespace
# the kernel applies no default action to a signal with no installed handler, so
# an unhandled SIGQUIT there does not mean "terminate", it means nothing at all.
#
# Together those are what left `artisan reverb:start` running until the kubelet
# SIGKILLed it. Laravel\Reverb's StartServer subscribes to SIGINT, SIGTERM and
# SIGTSTP (Servers\Reverb\Console\Commands\StartServer::getSubscribedSignals);
# SIGQUIT is not among them, so handleSignal never ran, the websockets were
# never closed, and the pod sat in Terminating for the whole
# terminationGracePeriodSeconds. Measured in production on 2026-09-10: 301s
# against a 300s grace period, with `Gracefully terminating connections.` never
# reaching the log and /proc/1 showing an untroubled event loop the entire time
# (State S, SigPnd 0, wchan poll_schedule_timeout).
#
# This closes both halves. It traps the signals a runtime might plausibly send
# and forwards SIGTERM, which is what the command does handle; and by running
# that command as a *child* rather than as PID 1 it restores the ordinary
# default dispositions for anything it does not trap, so the failure mode is a
# terminated process rather than an immortal one.
#
# Not a supervisor: unlike queue_worker.sh this does not restart anything. A
# daemon that exits here is a container that exits, which is what should happen
# — the child's own status is propagated so a real failure still crashloops
# visibly.

# Deliberately no `set -e`. `wait` reports the child's status, and a child
# exiting non-zero has to reach the `exit` at the bottom rather than killing
# this shell on the way.
set -uo pipefail

shutdown=0
child=

_forward() {
    shutdown=1

    if [[ -n "$child" ]]; then
        kill -s TERM "$child" 2>/dev/null || true
    fi
}

# SIGQUIT is what this image's containers are actually stopped with, and the
# reason this script exists. SIGTERM is trapped too, because it is what a
# `lifecycle.stopSignal` or a plain `kill` sends and because the compose file
# overrides stop_signal to it; SIGINT for an interactive `docker run`. All three
# mean the same thing here.
trap _forward SIGTERM SIGQUIT SIGINT

php "$@" &
child=$!

status=0

# `wait` returns as soon as a trapped signal has been handled, while the child
# may still be shutting down — so a return from `wait` does not mean the child
# is gone, and its status cannot yet be read as the child's. `kill -0` is the
# test that can: keep waiting while the child still exists, and the last status
# read is then genuinely its exit status.
while :; do
    wait "$child" && status=0 || status=$?
    kill -0 "$child" 2>/dev/null || break
done

# A child that exits because we asked it to is a clean stop, whatever status the
# shell saw. Reporting 143 there would have every rollout log a container that
# "failed" while doing exactly what it was told.
if ((shutdown)); then
    exit 0
fi

exit $status
