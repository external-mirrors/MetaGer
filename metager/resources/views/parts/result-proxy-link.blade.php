{{-- "Open anonymously" for one result, wherever a result is shown: the result
     itself, its grouped and folded results, the news and video cards and the
     image details. $variant is "full" (the label, shortened on a phone),
     "short" (the short label, for cards) or "icon" (the shield alone, labelled
     for assistive technology). $label replaces the long label.

     All SafeBrowse parameters travel in the hash (never the query string), which
     never reaches a server or its access logs. Each result opens in a tab of its
     own (resultpage/proxy.js), all of them in one SafeBrowse session.
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
     link for them instead would boot the SafeBrowse app just to dead-end on
     "Kein Authentifizierungsschlüssel angegeben".

     Not rendered on a page that is itself being viewed through the proxy. --}}
@php($proxyVariant = $variant ?? 'full')
@if (request()->header("is-proxy") !== "true" && !empty($result->proxyLink))
    <a class="result-open-proxy result-open-proxy--{{ $proxyVariant }}" title="@lang('result.proxytext')" href="{{ $result->proxyLink }}" @if (\App\Http\ClientCapabilities::supportsSafebrowse(request())) data-proxy-link="{{ LaravelLocalization::getLocalizedUrl(null, "/proxy/") }}#url={{ rawurlencode($result->link) }}&fallback={{ rawurlencode($result->proxyLink) }}@if (auth()->guard("key")->user() !== null && auth()->guard("key")->login_method === 'query')&key={{ rawurlencode(auth()->guard("key")->user()->key) }}@endif" @endif
        target="{{ $metager->getNewtab() }}" @if ($metager->getNewtab() === '_blank') rel="noopener" @endif
        @if ($proxyVariant === 'icon') aria-label="@lang('result.options.anonym')" @endif>
        <svg class="result-open-proxy-shield" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6z"/></svg>
        @if ($proxyVariant === 'full')
            <span class="action-label-long">{{ $label ?? trans('result.options.anonym') }}</span>
            <span class="action-label-short">@lang('result.options.anonym-short')</span>
        @elseif ($proxyVariant === 'short')
            <span>@lang('result.options.anonym-short')</span>
        @endif
    </a>
@endif
