#!/usr/bin/env bash
#
# Assertions about the rendered manifests that a golden diff cannot make for you.
#
#   ./tests/assertions.sh
#
# tests/render.sh already byte-compares the whole output, which catches *any*
# change — but it catches them all equally, and a reviewer approving a 400-line
# subchart swap has no way to tell the load-bearing lines from the noise. These
# are the lines that are load-bearing. Each one, if wrong, produces a deployment
# that comes up healthy and then misbehaves under traffic.
#
# Kept as assertions on `helm template` output rather than on the templates
# themselves, because what matters is what Kubernetes receives.
set -euo pipefail

cd "$(dirname "$0")/.."

VALUES="tests/golden-values.yaml"
RELEASE="golden"
NAMESPACE="metager"

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

failures=0

# Capture the output of a pipeline that is *expected* to match nothing when an
# assertion is about to fail. Under `set -e` with `pipefail` a grep with no match
# returns 1 and kills the script — which would abort the run at the first failing
# assertion, printing its header and no verdict, and skipping every assertion
# after it. Exactly the failure path these assertions exist to describe.
capture() { "$@" || true; }

pass() { printf '  ok   %s\n' "$1"; }
fail() {
    printf '  FAIL %s\n' "$1" >&2
    [[ $# -gt 1 ]] && printf '       %s\n' "$2" >&2
    failures=$((failures + 1))
}

render() {
    helm template "$RELEASE" . --namespace "$NAMESPACE" -f "$VALUES" "$@"
}

# ---------------------------------------------------------------------------
# The default Redis connection must reach the master, and only the master.
# ---------------------------------------------------------------------------
#
# This is the assertion worth having. Most of the app talks to the `default`
# connection, not the sentinel-aware one: REDIS_CACHE_CONNECTION is `default` in
# every deployed environment, and the bare Redis:: facade calls on the search
# path — rpush/brpop/lpush/expire in MetaGer, Searchengine and Suggestions — are
# writes. A replica answers a write with -READONLY.
#
# The subchart's plain `<name>` Service selects every pod, replicas included, so
# pointing REDIS_HOST at it yields a deployment that passes every health check
# and then fails roughly two writes in three. `<name>-master` is the HAProxy
# master-proxy Service, which exists only while sentinel.masterProxy.enabled is
# true — hence the second assertion.

render >"$WORK/rendered.yaml"

echo "REDIS_HOST resolves to the master:"

redis_hosts="$(capture grep -A1 'name: REDIS_HOST$' "$WORK/rendered.yaml" |
    capture grep 'value:' | sed 's/.*value: //' | sort -u)"

if [[ "$redis_hosts" == "golden-valkey-master.${NAMESPACE}.svc.cluster.local" ]]; then
    pass "REDIS_HOST is the -master service"
else
    fail "REDIS_HOST is not the -master service" "got: ${redis_hosts//$'\n'/, }"
fi

if grep -q 'name: golden-valkey-master$' "$WORK/rendered.yaml"; then
    pass "the -master service exists"
else
    fail "no -master service is rendered" \
        "valkey.sentinel.masterProxy.enabled must be true, or REDIS_HOST points at nothing"
fi

if grep -q 'targetPort: haproxy' "$WORK/rendered.yaml"; then
    pass "the -master service targets the HAProxy sidecar"
else
    fail "the -master service does not target haproxy"
fi

# ---------------------------------------------------------------------------
# Every password reference resolves to a key that exists.
# ---------------------------------------------------------------------------
#
# The retired chart minted two passwords under the keys REDIS_PASSWORD and
# SENTINEL_PASSWORD. This one mints a single password under the key `password`
# and hands Sentinel the same secret, for both `sentinel auth-pass` and
# Sentinel's own `requirepass`. A secretKeyRef naming a key that does not exist
# does not fail the render, or the install — it leaves the container stuck in
# CreateContainerConfigError long after `helm upgrade` has reported success.

echo
echo "Password references resolve:"

secret_keys="$(capture grep -A3 'secretKeyRef:' "$WORK/rendered.yaml" |
    capture grep -E '^\s+key:' | sed 's/.*key: //' | sort -u)"

if [[ "$secret_keys" == "password" ]]; then
    pass "every secretKeyRef reads the key 'password'"
else
    fail "a secretKeyRef reads a key other than 'password'" "got: ${secret_keys//$'\n'/, }"
fi

if grep -qE '^  password: ' "$WORK/rendered.yaml"; then
    pass "the valkey secret provides that key"
else
    fail "the valkey secret has no 'password' key"
fi

# ---------------------------------------------------------------------------
# The master name the app asks Sentinel for is the one Sentinel monitors.
# ---------------------------------------------------------------------------
#
# predis resolves the master by asking Sentinel for it by name
# (config/database.php, REDIS_SENTINEL_SERVICE). If that name and the
# subchart's sentinel.masterName disagree, Sentinel answers with nothing and
# every sentinel-connection call fails to connect at all.

echo
echo "Sentinel master name agrees:"

app_master_name="$(capture grep -A1 'name: REDIS_SENTINEL_SERVICE$' "$WORK/rendered.yaml" |
    capture grep 'value:' | sed 's/.*value: //' | sort -u)"
chart_master_name="$(capture grep -oE 'sentinel monitor [a-zA-Z0-9_-]+' "$WORK/rendered.yaml" |
    awk '{print $3}' | sort -u)"

if [[ -n "$app_master_name" && "$app_master_name" == "$chart_master_name" ]]; then
    pass "REDIS_SENTINEL_SERVICE matches 'sentinel monitor' ($app_master_name)"
else
    fail "REDIS_SENTINEL_SERVICE does not match the monitored master" \
        "app: ${app_master_name:-<unset>}, chart: ${chart_master_name:-<none>}"
fi

# ---------------------------------------------------------------------------
# Sentinel is actually running.
# ---------------------------------------------------------------------------
#
# sentinel.enabled is only honoured when architecture is "replication" — the
# subchart's default is "standalone", under which the sentinel templates render
# nothing at all and no failover exists. Silent: a standalone Valkey serves
# traffic perfectly well right up until the node holding it drains.

echo
echo "Sentinel is enabled:"

if grep -q 'name: golden-valkey-sentinel$' "$WORK/rendered.yaml"; then
    pass "the sentinel service exists"
else
    fail "no sentinel service is rendered" \
        "valkey.architecture must be 'replication' AND valkey.sentinel.enabled true"
fi

# The StatefulSet is valkey's; the app/queue/reverb workloads are Deployments,
# so scoping to the document after `kind: StatefulSet` picks the right replicas.
replica_count="$(capture awk '/^kind: StatefulSet$/ { inss = 1 }
    inss && /^  replicas: / { print $2; exit }' "$WORK/rendered.yaml")"
if [[ "${replica_count:-0}" -ge 3 ]]; then
    pass "at least 3 replicas, so a quorum can form (${replica_count})"
else
    fail "fewer than 3 valkey replicas (${replica_count:-unknown})" \
        "a 2-node sentinel quorum cannot survive losing a node"
fi

# ---------------------------------------------------------------------------
# The documented name budget is real.
# ---------------------------------------------------------------------------
#
# The subchart appends suffixes to its fullname without re-truncating, and
# Kubernetes rejects any object name over 63 characters. The longest suffix is
# "-prestop-script" (15), so the name must fit in 48 — the figure documented in
# values.yaml, in chart.valkeyFullname's trunc, and in the release-name
# arithmetic in .gitlab/deployment_scripts/update_deployment.sh. Three places
# that have to agree, none of which fails loudly when they stop agreeing: a name
# one character too long is rejected at apply time, on deploy, in production.
#
# Rendering at exactly the documented maximum is what keeps the figure honest.
# If the subchart ever grows a longer suffix, this is what says so.

echo
echo "Name budget holds at the documented maximum:"

max_name="$(printf 'a%.0s' $(seq 1 41))-valkey"
if [[ ${#max_name} -ne 48 ]]; then
    fail "test bug: probe name is ${#max_name} chars, expected 48"
fi

overlong="$(render --set valkeyName="$max_name" --set valkey.fullnameOverride="$max_name" |
    capture grep -oE '^  name: [a-z0-9.-]+' | sed 's/  name: //' | sort -u |
    awk 'length($0) > 63 { print length($0) " " $0 }')"

if [[ -z "$overlong" ]]; then
    pass "a 48-char valkey name renders no object name over 63 chars"
else
    fail "a 48-char valkey name overflows the 63-char cap" "${overlong//$'\n'/; }"
fi

echo
echo "Valkey survives a node drain:"

# ---------------------------------------------------------------------------
# A drain can only take one valkey pod at a time.
# ---------------------------------------------------------------------------
#
# kubectl drain goes through the Eviction API, which honours a
# PodDisruptionBudget; sentinel.quorum above is 2 of 3, so losing two pods to
# back-to-back evictions is a lost quorum, not a failover. Without this PDB a
# drain has no such protection at all.

if grep -q 'name: golden-valkey$' "$WORK/rendered.yaml" && grep -A20 'kind: PodDisruptionBudget' "$WORK/rendered.yaml" | grep -q 'name: golden-valkey$'; then
    pdb_min_available="$(capture awk '
        /^kind: PodDisruptionBudget$/ { inpdb = 1 }
        inpdb && /name: golden-valkey$/ { named = 1 }
        named && /^  minAvailable: /{ print $2; exit }
    ' "$WORK/rendered.yaml")"

    if [[ "${pdb_min_available:-0}" -ge 2 ]]; then
        pass "valkey PodDisruptionBudget keeps quorum (minAvailable ${pdb_min_available})"
    else
        fail "valkey PodDisruptionBudget allows quorum loss" \
            "minAvailable is '${pdb_min_available:-unset}', needs to be at least 2 of 3"
    fi
else
    fail "no PodDisruptionBudget is rendered for valkey" "valkey.pdb.enabled must be true"
fi

# ---------------------------------------------------------------------------
# The pod outlives its own graceful-failover hook.
# ---------------------------------------------------------------------------
#
# The subchart's preStop hook can take ~45s to hand off a master cleanly (a
# 22s write pause, then up to 20s confirming the failover, then settle time).
# terminationGracePeriodSeconds below that figure means a drained master gets
# SIGKILLed mid-handoff instead of completing it — a lost quorum member, not a
# graceful one.

grace_period="$(capture awk '/^kind: StatefulSet$/ { inss = 1 }
    inss && /terminationGracePeriodSeconds: / { print $2; exit }' "$WORK/rendered.yaml")"
if [[ "${grace_period:-0}" -ge 60 ]]; then
    pass "terminationGracePeriodSeconds gives the preStop hook room to finish (${grace_period}s)"
else
    fail "terminationGracePeriodSeconds is too short for a graceful failover" \
        "got ${grace_period:-unset}s, the subchart's own hook can take ~45s"
fi


# ---------------------------------------------------------------------------
# A drain is less likely to expose more than one quorum member at a time,
# because they were steered apart to begin with.
# ---------------------------------------------------------------------------
#
# The PDB above only governs the Eviction API, not a node dying outright — if
# two of the three replicas were co-located, losing that one node is a lost
# quorum regardless of how gracefully it happens. Preferred rather than
# required (same choice the app Deployments make): it doesn't guarantee
# spread under node pressure, but it never leaves a pod stuck Pending either.

has_soft_anti_affinity="$(capture awk '/^kind: StatefulSet$/ { inss = 1 }
    inss && /preferredDuringSchedulingIgnoredDuringExecution:/ { found = 1 }
    inss && found && /topologyKey: kubernetes.io\/hostname/ { print "yes"; exit }' "$WORK/rendered.yaml")"
if [[ "$has_soft_anti_affinity" == "yes" ]]; then
    pass "valkey pods are steered to spread across nodes"
else
    fail "valkey has no pod anti-affinity" \
        "two of the three sentinel-quorum members could land on the same node"
fi

echo
if [[ $failures -eq 0 ]]; then
    echo "All chart assertions passed."
else
    echo "$failures chart assertion(s) failed." >&2
    exit 1
fi
