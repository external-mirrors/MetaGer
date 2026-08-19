#!/usr/bin/env bash
#
# Assertions about the nginx site configs.
#
#   build/nginx/tests/run.sh
#
# Needs nothing but bash. It reads the config files in ../configuration; it does
# not start nginx.
#
# These exist because of one class of bug: a proxy that resolves its upstream
# once and then dials a stale address forever. Nothing about it looks like a
# config error. `nginx -t` passes, the container is healthy, the site serves,
# and only the proxied endpoint is dead -- which reads as "reverb is broken",
# not as "nginx is looking in the wrong place". It cost a morning of debugging
# a websocket that was fine.
set -euo pipefail

cd "$(dirname "$0")"

CONF_DIR="../configuration"
DEV="$CONF_DIR/nginx-default-dev.conf"

failures=0

pass() { printf '  ok   %s\n' "$1"; }
fail() {
    printf '  FAIL %s\n' "$1" >&2
    [[ $# -gt 1 ]] && printf '       %s\n' "$2" >&2
    failures=$((failures + 1))
}

# ---------------------------------------------------------------------------
# A resolver has to be configured, or naming an upstream through a variable
# fails outright.
# ---------------------------------------------------------------------------
#
# The two assertions below both depend on this one. `proxy_pass http://$var...`
# without a `resolver` is not a slow path or a fallback -- nginx refuses the
# request. 127.0.0.11 is Docker's embedded DNS, which is what knows where a
# compose service currently lives.
if grep -qE '^\s*resolver\s+127\.0\.0\.11' "$DEV"; then
    pass "dev config configures Docker's embedded resolver"
else
    fail "dev config has no 'resolver 127.0.0.11'" \
         "every variable-named upstream below will fail with 'no resolver defined'"
fi

# ---------------------------------------------------------------------------
# No compose service may be named literally in a proxy_pass.
# ---------------------------------------------------------------------------
#
# `proxy_pass http://reverb:8080` is resolved when the config is loaded and
# pinned for the life of the worker process. Compose assigns container IPs in
# start order, so recreating the app containers without also recreating nginx
# moves reverb to a new address and leaves nginx dialling whatever container
# inherited the old one -- usually something listening on a different port, so
# the symptom is a 502 rather than a timeout.
#
# It is worse than one dead location. The literal form also registers an
# implicit upstream group named "reverb:8080", and a later
# `proxy_pass http://$var:8080` finds that group by name and reuses its pinned
# address instead of consulting the resolver. So a single literal poisons the
# variable-named blocks too: the websocket and the backend's broadcast POSTs to
# /apps/{id}/events die together.
#
# Literal IPs are exempt -- the SafeBrowse and keymanager dev upstreams are
# fixed addresses set by those projects, not names that can move.
literal_upstreams="$(grep -nvE '^\s*#' "$DEV" \
    | grep -E 'proxy_pass\s+https?://[a-zA-Z_][a-zA-Z0-9_.-]*(:[0-9]+)?' || true)"
if [[ -z "$literal_upstreams" ]]; then
    pass "dev config names every hostname upstream through a variable"
else
    fail "dev config proxies to a literal hostname" \
         "$literal_upstreams"
fi

# ---------------------------------------------------------------------------
# In a location that rewrites with `break`, `set` has to come first.
# ---------------------------------------------------------------------------
#
# `break` ends the rewrite phase, and `set` is a rewrite-phase directive. A
# `set` below the rewrite never runs. The variable is then empty at request
# time and nginx answers 500 with "no host in upstream :8080" -- which looks
# like a different bug entirely from the one the variable was introduced to fix,
# so it is an easy way to "fix" the config into being broken a second way.
awk '
    /location[^{]*\{/ { in_loc = 1; brk = 0; loc = NR; bad = 0 }
    in_loc && /rewrite[^;]*break\s*;/ { brk = 1 }
    in_loc && brk && /^\s*set\s+\$/ { bad = NR }
    in_loc && /^\s*}/ {
        if (bad) printf "%d: set after `rewrite ... break` in location opened at line %d\n", bad, loc
        in_loc = 0
    }
' "$DEV" > /tmp/nginx-set-order.$$ || true

if [[ ! -s /tmp/nginx-set-order.$$ ]]; then
    pass "every 'set' runs before the 'rewrite ... break' in its location"
else
    fail "a 'set' is unreachable, below a 'rewrite ... break'" "$(cat /tmp/nginx-set-order.$$)"
fi
rm -f /tmp/nginx-set-order.$$

# ---------------------------------------------------------------------------
# Both halves of the Reverb path have to be proxied.
# ---------------------------------------------------------------------------
#
# /ws/app is what Echo connects to; /apps/{id}/events is what the Laravel
# backend POSTs broadcasts to. Losing the second one is silent in the browser --
# the socket connects and simply never receives anything.
for location in '/ws/app' 'apps/'; do
    if grep -q "location.*$location" "$DEV"; then
        pass "dev config proxies $location"
    else
        fail "dev config has no location for $location"
    fi
done

echo
if (( failures )); then
    printf '%d assertion(s) failed\n' "$failures" >&2
    exit 1
fi
printf 'all assertions passed\n'
