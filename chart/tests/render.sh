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
# Helm's own version is part of the snapshot. Different helm versions render these
# manifests identically apart from blank lines around document separators — nothing
# Kubernetes can tell apart, but enough to bury a real diff. HELM_VERSION is the
# version CI uses (see .gitlab/ci/chart.yml); run this through the same container
# and the comparison is exact:
#
#   docker run --rm -v "$PWD:/chart" -w /chart --entrypoint bash \
#       alpine/helm:4.2.4 tests/render.sh
set -euo pipefail

cd "$(dirname "$0")/.."

SNAPSHOT="tests/golden.yaml"
VALUES="tests/golden-values.yaml"
HELM_VERSION="v4.2.4"

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

check_helm_version() {
    local actual
    actual="$(helm version --short)"

    if [[ "$actual" != "$HELM_VERSION"* ]]; then
        echo "warning: $SNAPSHOT was generated with helm $HELM_VERSION, this is $actual." >&2
        echo "         Blank lines around document separators differ between helm 3 and 4," >&2
        echo "         so expect a whitespace-only diff. To match CI exactly:" >&2
        echo "         docker run --rm -v \"\$PWD:/chart\" -w /chart --entrypoint bash \\" >&2
        echo "             alpine/helm:4.2.4 tests/render.sh" >&2
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
normalize() {
    sed -E \
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
