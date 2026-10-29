{{-- Show pagination --}}
@if($metager->nextSearchLink() !== "#")
<nav class="mg-pager" aria-label="@lang('results.pager')">
    <div id="next-search-link">
        <a @if($metager->nextSearchLink() !== "#") href="{{ $metager->nextSearchLink() }}" @endif @if($metager->isFramed() && $metager->getOut() !== "results-with-style")target="_top"@else target="_self"@endif>{{ trans('results.weiter') }}</a>
    </div>
</nav>
@endif