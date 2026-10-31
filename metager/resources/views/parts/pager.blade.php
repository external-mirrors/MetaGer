{{-- Show pagination --}}
@if($metager->nextSearchLink() !== "#")
<nav class="mg-pager" aria-label="@lang('results.pager')">
    <div id="next-search-link">
        <a href="{{ $metager->nextSearchLink() }}" @if($metager->isFramed() && $metager->getOut() !== "results-with-style")target="_top"@else target="_self"@endif>
            <span>{{ trans('results.pager') }}</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</nav>
@endif
