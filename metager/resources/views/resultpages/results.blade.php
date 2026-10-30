{{-- A labelled region, not a list. It was role="list", but the news and video
     rows, the alteration notice, the blacklist alerts and the pager all live in
     here between and after the results, and a list may only hold list items —
     screen readers announced a list whose item count was wrong and skipped the
     rest. They cannot move out: scriptResultPage.js reaches the results as
     `#results > .result`. Each result is still found by its h2. --}}
<div id="results" role="region"
    aria-label="@lang('results.results.summary', ['resultcount' => sizeof($metager->getResults()), 'totalresults' => $metager->getTotalResultCount()])">
    @include('parts.alteration')
    @foreach ($metager->getResults() as $index => $result)
        @if ($index === 2)
            @include('layouts.resultpage.news', ['news' => $metager->news])
        @endif
        @if ($index === 5)
            @include('layouts.resultpage.videos', ['videos' => $metager->videos])
        @endif
        @include('layouts.result', ['result' => $result, 'index' => $index + 1])
    @endforeach
    @if(sizeof($metager->getUserDomainBlacklist()) > 0)
        <div class="alert alert-warning">
            @if(sizeof($metager->getUserDomainBlacklist()) <= 3)
                @lang('metaGer.formdata.domainBlacklist', ['domain' => implode(", ", $metager->getUserDomainBlacklist())])
            @else
                @lang('metaGer.formdata.domainBlacklistCount', ['count' => sizeof($metager->getUserDomainBlacklist())])
            @endif
        </div>
    @endif
    @if(sizeof($metager->getUserHostBlacklist()) > 0)
        <div class="alert alert-warning">
            @if(sizeof($metager->getUserHostBlacklist()) <= 3)
                @lang('metaGer.formdata.hostBlacklist', ['host' => implode(", ", $metager->getUserHostBlacklist())])
            @else
                @lang('metaGer.formdata.hostBlacklistCount', ['count' => sizeof($metager->getUserHostBlacklist())])
            @endif
        </div>
    @endif
    @include('parts.pager')
</div>