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
# render and `helm template` fails. Fetch it when it is missing — and only then,
# because the fetch reaches over the network and the snapshot check should not.
ensure_dependencies() {
    if ! helm dependency list . 2>/dev/null | grep -q "missing"; then
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

render() {
    # Deliberately not `diff <(helm template …)`: a process substitution discards
    # the exit status, so a chart that fails to render used to come out as an empty
    # document and get reported as "1698 lines removed" rather than as an error.
    if ! helm template golden . --namespace metager -f "$VALUES" >"$WORK/rendered.yaml"; then
        echo >&2
        echo "helm template failed — see the error above. Snapshot left untouched." >&2
        exit 1
    fi
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
