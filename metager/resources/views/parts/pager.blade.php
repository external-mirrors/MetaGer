{{-- Show pagination --}}
<nav class="mg-pager" aria-label="...">
    <div id="last-search-link" @if($metager->getPage() === 1) class="disabled" @endif>
        <a @if($metager->getPage() !== 1) href="#" @endif>{{ trans('results.zurueck') }}</a>
    </div>
    <div id="next-search-link" @if($metager->nextSearchLink() === "#") class="disabled" @endif>
        <a @if($metager->nextSearchLink() !== "#") href="{{ $metager->nextSearchLink() }}" @endif @if($metager->isFramed() && $metager->getOut() !== "results-with-style")target="_top"@else target="_self"@endif>{{ trans('results.weiter') }}</a>
    </div>
</nav>
