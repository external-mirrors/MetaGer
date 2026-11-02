{{-- "Open anonymously" for one result, wherever a result is shown: the result
     itself, its grouped and folded results, the news and video cards and the
     image details. $variant is "full" (the label, shortened on a phone),
     "short" (the short label, for cards) or "icon" (the shield alone, labelled
     for assistive technology). $label replaces the long label.

     With the "open results anonymously" setting the result's own links already
     go through the proxy (parts/result-href), so this turns into its opposite:
     "Direkt öffnen", the one way in the row to open the page directly. The icon
     variant, beside a link that is anonymous already, then has nothing to offer.

     SafeBrowse: parts/proxy-safebrowse-attribute. Not rendered on a page that is
     itself being viewed through the proxy. --}}
@php($proxyVariant = $variant ?? 'full')
@if ($metager->opensAnonymously($result))
    @if ($proxyVariant !== 'icon')
        <a class="result-open-direct result-open-direct--{{ $proxyVariant }}" href="{{ $result->link }}"
            target="{{ $metager->getNewtab() }}" @if ($metager->getNewtab() === '_blank') rel="noopener" @endif>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
            @if ($proxyVariant === 'full')
                <span class="action-label-long">@lang('result.options.direct')</span>
                <span class="action-label-short">@lang('result.options.direct-short')</span>
            @else
                <span>@lang('result.options.direct-short')</span>
            @endif
        </a>
    @endif
@elseif (request()->header("is-proxy") !== "true" && !empty($result->proxyLink))
    <a class="result-open-proxy result-open-proxy--{{ $proxyVariant }}" title="@lang('result.proxytext')" href="{{ $result->proxyLink }}" @include('parts.proxy-safebrowse-attribute', ['target' => $result])
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
