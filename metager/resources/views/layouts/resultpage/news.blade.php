@if (sizeof($news) > 0)
    <h3 id="news-heading" class="inline-heading">@lang('result.news') <a
            href="{{ $metager->generateSearchLink('nachrichten') }}">@lang('result.more-news')</a></h3>
    {{-- The frame stays put while the row scrolls; resultpage/scroller.js puts
         its buttons in it. --}}
    <div class="inline-scroller" data-scroll-back="@lang('result.scroll-back')" data-scroll-forward="@lang('result.scroll-forward')">
        <div id="news" class="inline-results">
            @foreach ($news as $news_result)
                @include('layouts.resultpage.inline_result', [
                    'result' => $news_result,
                    'index' => 'news_' . ($index + 1),
                ])
            @endforeach
        </div>
    </div>
@endif
