#!/usr/bin/env bash
#
# Tests for render.sh's dependency step.
#
#   ./tests/render_test.sh
#
# render.sh decides whether the subchart has to be fetched before rendering, and
# both ways of getting that wrong fail somewhere unhelpful: skip a fetch the
# checkout needed and `helm template` fails with "missing in charts/ directory";
# skip one for a stale tarball and the snapshot is silently rendered against the
# wrong subchart version.
#
# helm is stubbed, and render.sh runs against a throwaway copy of a chart, so
# nothing here reaches the network or touches the real snapshot.
set -uo pipefail

cd "$(dirname "$0")"

RENDER_SH="$PWD/render.sh"

failures=0
tests=0

pass() { printf '  ok   %s\n' "$1"; }
fail() {
    printf '  FAIL %s\n' "$1" >&2
    shift
    for line in "$@"; do printf '       %s\n' "$line" >&2; done
    failures=$((failures + 1))
}

# A chart directory with just enough in it for render.sh, and a helm on PATH that
# answers `dependency list` with STUB_DEP_STATUS and records every other call.
scenario() {
    WORK="$(mktemp -d)"
    mkdir -p "$WORK/chart/tests" "$WORK/bin"
    cp "$RENDER_SH" "$WORK/chart/tests/render.sh"
    cat >"$WORK/chart/Chart.yaml" <<'EOF'
dependencies:
  - name: valkey
    repository: oci://registry-1.docker.io/cloudpirates
    version: 0.25.15
EOF
    echo "kind: Snapshot" >"$WORK/chart/tests/golden.yaml"
    : >"$WORK/chart/tests/golden-values.yaml"
    CALLS="$WORK/calls.txt"
    : >"$CALLS"

    cat >"$WORK/bin/helm" <<EOF
#!/usr/bin/env bash
case "\$1 \${2:-}" in
    "version --short") echo "v4.2.4+stub" ;;
    "dependency list")
        [[ -n "\${STUB_DEP_LIST_FAILS:-}" ]] && { echo "Error: stub failure" >&2; exit 1; }
        # Real helm writes the table and its trailing blank line as two separate
        # write(2) calls. The pause widens the gap between them so a reader that
        # stops after the first one loses the race every time instead of rarely.
        printf 'NAME\tVERSION\tREPOSITORY\tSTATUS\nvalkey\t0.25.15\toci://x\t%s\n' "\$STUB_DEP_STATUS"
        sleep 0.3
        printf '\n'
        ;;
    "template "*) echo "\$*" >>"$CALLS"; cat tests/golden.yaml ;;
    *) echo "\$*" >>"$CALLS" ;;
esac
EOF
    chmod +x "$WORK/bin/helm"
}

run_render() {
    OUTPUT="$(PATH="$WORK/bin:$PATH" "$WORK/chart/tests/render.sh" 2>&1)"
    STATUS=$?
}

fetched() { grep -qx "dependency build ." "$CALLS"; }

expect_fetch() {
    local status="$1" name="$2"
    tests=$((tests + 1))
    scenario
    STUB_DEP_STATUS="$status" run_render
    if [[ $STATUS -eq 0 ]] && fetched; then
        pass "$name"
    else
        fail "$name" "exit $STATUS, helm calls: $(tr '\n' ';' <"$CALLS")" "$OUTPUT"
    fi
    rm -rf "$WORK"
}

echo "render.sh dependency step"

# The pipeline job that failed: helm's second write lands after `grep -q` has
# matched and exited, helm dies of SIGPIPE, pipefail turns that into a failed
# check, and the fetch was skipped on a checkout that had nothing in charts/.
expect_fetch "missing" "fetches a missing subchart even when grep stops reading early"

# A tarball for another version left in chart/charts/ is "wrong version", not
# "missing", and used to be rendered as if it were the right one.
expect_fetch "wrong version" "refetches a subchart whose version does not match Chart.yaml"

tests=$((tests + 1))
scenario
STUB_DEP_STATUS="ok" run_render
if [[ $STATUS -eq 0 ]] && ! fetched; then
    pass "does not reach the network when the subchart is already in place"
else
    fail "does not reach the network when the subchart is already in place" \
        "exit $STATUS, helm calls: $(tr '\n' ';' <"$CALLS")" "$OUTPUT"
fi
rm -rf "$WORK"

# If helm cannot even say what is missing, the answer is not "nothing".
tests=$((tests + 1))
scenario
STUB_DEP_LIST_FAILS=1 STUB_DEP_STATUS="ok" run_render
if [[ $STATUS -ne 0 ]] && ! grep -q "^template " "$CALLS"; then
    pass "fails before rendering when helm dependency list fails"
else
    fail "fails before rendering when helm dependency list fails" \
        "exit $STATUS, helm calls: $(tr '\n' ';' <"$CALLS")" "$OUTPUT"
fi
rm -rf "$WORK"

echo
if [[ $failures -gt 0 ]]; then
    echo "$failures of $tests failed." >&2
    exit 1
fi
echo "All $tests passed."
