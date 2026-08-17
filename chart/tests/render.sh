#!/usr/bin/env bash
#
# Golden-file snapshots of `helm template`.
#
#   ./tests/render.sh          compare the current chart against the snapshot
#   ./tests/render.sh --update rewrite the snapshot
#
# A chart refactor is only safe if the manifests come out the same, and "the same"
# is not something you can eyeball across 1,400 lines. Run this before and after:
# a pure refactor shows an empty diff, and a change that moves a container between
# Deployments shows exactly that and nothing else.
#
# The subchart is rendered too, so a dependency swap shows up here in full.
set -euo pipefail

cd "$(dirname "$0")/.."

SNAPSHOT="tests/golden.yaml"
VALUES="tests/golden-values.yaml"

render() {
    helm template golden . --namespace metager -f "$VALUES"
}

if [[ "${1:-}" == "--update" ]]; then
    render >"$SNAPSHOT"
    echo "Updated $SNAPSHOT ($(wc -l <"$SNAPSHOT") lines)."
    exit 0
fi

if [[ ! -f "$SNAPSHOT" ]]; then
    echo "No snapshot at $SNAPSHOT — create one with: $0 --update" >&2
    exit 1
fi

if diff -u "$SNAPSHOT" <(render); then
    echo "Rendered manifests match $SNAPSHOT."
else
    echo >&2
    echo "Rendered manifests differ from $SNAPSHOT." >&2
    echo "If the change is intended, review the diff above and run: $0 --update" >&2
    exit 1
fi
