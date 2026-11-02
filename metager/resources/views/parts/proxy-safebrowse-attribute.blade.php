{{-- The data-proxy-link attribute that sends a link to $target (anything with
     a link and a proxyLink) to SafeBrowse: resultpage/proxy.js makes it the
     link's href and opens it in a tab of its own, all of them in one SafeBrowse
     session. Used by the shield (parts/result-proxy-link) and by every link the
     "open results anonymously" setting rewrites (parts/result-href).

     All SafeBrowse parameters travel in the hash (never the query string), which
     never reaches a server or its access logs.
     Only a query login carries the key here. SafeBrowse is same-origin, so a
     cookie login already sends the key on the WebSocket upgrade by itself, and a
     header login gets anonymous-token-key injected by the webextension the same
     way — neither needs it, and SafeBrowse forwards a hash key as ?key= on the
     socket URL, so emitting it anyway would write those keys into access logs.
     A query login has no other transport and its key is in the URL regardless.
     user() is evaluated first on purpose: it is what resolves login_method, which
     otherwise still holds its 'query' default and would match by accident.

     Without data-proxy-link, resultpage/proxy.js leaves the click alone and the
     href (old proxy) is followed — which is the whole opt-out for clients that
     cannot authenticate against SafeBrowse (see ClientCapabilities). Rendering the
     attribute for them instead would boot the SafeBrowse app just to dead-end on
     "Kein Authentifizierungsschlüssel angegeben". --}}
@if (\App\Http\ClientCapabilities::supportsSafebrowse(request())) data-proxy-link="{{ LaravelLocalization::getLocalizedUrl(null, "/proxy/") }}#url={{ rawurlencode($target->link) }}&fallback={{ rawurlencode($target->proxyLink) }}@if (auth()->guard("key")->user() !== null && auth()->guard("key")->login_method === 'query')&key={{ rawurlencode(auth()->guard("key")->user()->key) }}@endif" @endif
