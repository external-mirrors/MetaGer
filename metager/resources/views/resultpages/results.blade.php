<div id="results" role="list"
    aria-label="@lang('results.results.summary', ['resultcount' => sizeof($metager->getResults()), 'totalresults' => $metager->getTotalResultCount()])">
    @include('parts.alteration')
    {{-- Create results and ongoing ads --}}
    @foreach ($metager->getResults() as $index => $result)
        @if ($index === 2)
            @include('layouts.resultpage.news', ['news' => $metager->news])
        @endif

        @if ($index === 5)
            @include('layouts.resultpage.videos', ['videos' => $metager->videos])
        @endif
        @include('layouts.result', ['result' => $result, 'index' => $index + 1])
    @endforeach
    @include('parts.pager')
</div>