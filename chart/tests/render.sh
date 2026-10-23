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
# The subchart is rendered too, so a dependency swap shows up here in full. That
# subchart is not in the repository — chart/charts/*.tgz is gitignored and pulled
# from the Helm registry named in Chart.yaml — so a fresh checkout has to fetch it
# before anything can be rendered. Hence the dependency step below.
#
# Which helm renders the snapshot is decided by the chart job's image in
# .gitlab/ci/chart.yml, and read from there: Renovate bumps that image, and a copy
# of its version here went stale the first time it did. Helm versions have so far
# differed only in blank lines around document separators, which normalize() below
# drops. Anything else a new helm changes shows up as a diff, as it should. To render
# with exactly what CI uses, from chart/ — the whole repository is mounted so that
# render.sh can read the CI file:
#
#   docker run --rm -v "$PWD/..:/repo" -w /repo/chart --entrypoint bash \
#       alpine/helm:<version in .gitlab/ci/chart.yml> tests/render.sh
set -euo pipefail

cd "$(dirname "$0")/.."

SNAPSHOT="tests/golden.yaml"
VALUES="tests/golden-values.yaml"
CI_CONFIG="../.gitlab/ci/chart.yml"

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

check_helm_version() {
    local expected actual
    expected="$(sed -n 's|^[[:space:]]*name:[[:space:]]*alpine/helm:\([^[:space:]]*\).*|\1|p' "$CI_CONFIG" 2>/dev/null | head -n 1 || true)"
    actual="$(helm version --short)"

    if [[ -z "$expected" ]]; then
        echo "warning: no alpine/helm image found in $CI_CONFIG, so cannot tell which helm CI uses." >&2
        echo >&2
    elif [[ "$actual" != "v$expected"* ]]; then
        echo "warning: CI renders $SNAPSHOT with helm v$expected, this is $actual." >&2
        echo "         To match CI exactly:" >&2
        echo "         docker run --rm -v \"\$PWD/..:/repo\" -w /repo/chart --entrypoint bash \\" >&2
        echo "             alpine/helm:$expected tests/render.sh" >&2
        echo >&2
    fi
}

# chart/charts/ is gitignored, so on a fresh checkout there is no subchart to
# render and `helm template` fails. Fetch it when it is not in place — and only
# then, because the fetch reaches over the network and the snapshot check should
# not. "Not in place" is any status but "ok": a tarball left over from an older
# Chart.yaml reports "wrong version", and rendering that would snapshot the wrong
# subchart without a word.
ensure_dependencies() {
    # Captured rather than piped into `grep -q`. helm writes the table and its
    # trailing blank line as two separate writes; grep -q exits as soon as the
    # first one matches, helm's second write then dies of SIGPIPE, pipefail makes
    # the whole check false, and the fetch was skipped on a checkout that had no
    # subchart at all. That is a race, so it failed one CI run in several.
    # tests/render_test.sh holds it open on purpose.
    local status
    status="$(helm dependency list .)"

    if ! awk 'NR > 1 && NF && $NF != "ok" { found = 1 } END { exit !found }' <<<"$status"; then
        return
    fi

    echo "Fetching chart dependencies..." >&2

    # An oci:// dependency resolves straight from the registry and must NOT be
    # registered with `helm repo add` — that command only speaks the classic
    # index.yaml protocol and fails on an OCI URL. A classic https:// repository
    # does need registering: helm 3 resolved one straight from the absolute URL
    # in Chart.yaml, helm 4 refuses ("no repository definition for …") unless it
    # was added first. Branch on the scheme rather than restating the URL, which
    # lives in Chart.yaml and should stay in one place.
    local repository
    repository="$(awk '/^ *repository: */ { print $2; exit }' Chart.yaml)"

    if [[ "$repository" != oci://* ]]; then
        helm repo add metager-charts "$repository" --force-update >&2
    fi

    helm dependency build . >&2
}

# Version stamps are masked, in the snapshot and in what it is compared against.
# Every Renovate bump of the subchart changed exactly these and nothing else — the
# `helm.sh/chart: valkey-x.y.z` label on every object, the digest-pinned images the
# subchart ships, and the app version label when the server moved — and each one
# failed this check until someone ran --update by hand. Those numbers are what the
# Renovate MR itself is about; what this snapshot guards is the shape of the
# manifests, and a release that changes that (an env var, a probe, a new object)
# still fails here as it should.
#
# Only images pinned by digest are masked. Ours are rendered with the tag from
# golden-values.yaml and no digest, so a change to how they are built still shows.
#
# Blank lines next to a `---`, or at the very end, are dropped for the same reason:
# helm 3 and 4 render the same manifests with and without them, and upgrading helm
# is a Renovate bump too. The awk holds blank lines back until it sees what follows.
normalize() {
    awk '
        /^[[:space:]]*$/ { blank = blank $0 "\n"; next }
        /^---/           { blank = ""; print; after_sep = 1; next }
        { if (!after_sep) printf "%s", blank; blank = ""; after_sep = 0; print }
    ' | sed -E \
        -e 's/^([[:space:]]*helm\.sh\/chart: [^[:space:]]+)-[0-9]+\.[0-9]+\.[0-9]+[^[:space:]]*$/\1-<version>/' \
        -e 's/^([[:space:]]*app\.kubernetes\.io\/version: ).+$/\1<version>/' \
        -e 's/^([[:space:]]*(- )?image: "?[^"@[:space:]]+):[^:@"\/[:space:]]+@sha256:[0-9a-f]{64}("?)$/\1:<tag>@sha256:<digest>\3/'
}

render() {
    # Deliberately not `diff <(helm template …)`: a process substitution discards
    # the exit status, so a chart that fails to render used to come out as an empty
    # document and get reported as "1698 lines removed" rather than as an error.
    if ! helm template golden . --namespace metager -f "$VALUES" >"$WORK/raw.yaml"; then
        echo >&2
        echo "helm template failed — see the error above. Snapshot left untouched." >&2
        exit 1
    fi
    normalize <"$WORK/raw.yaml" >"$WORK/rendered.yaml"
}

check_helm_version
ensure_dependencies
render

if [[ "${1:-}" == "--update" ]]; then
    mv "$WORK/rendered.yaml" "$SNAPSHOT"
    echo "Updated $SNAPSHOT ($(wc -l <"$SNAPSHOT") lines)."
    exit 0
fi

if [[ ! -f "$SNAPSHOT" ]]; then
    echo "No snapshot at $SNAPSHOT — create one with: $0 --update" >&2
    exit 1
fi

if diff -u "$SNAPSHOT" "$WORK/rendered.yaml"; then
    echo "Rendered manifests match $SNAPSHOT."
else
    echo >&2
    echo "Rendered manifests differ from $SNAPSHOT." >&2
    echo "If the change is intended, review the diff above and run: $0 --update" >&2
    exit 1
fi
