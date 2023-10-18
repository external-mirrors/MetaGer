@if (sizeof($news) > 0)
    <h3 id="news-heading" class="inline-heading">@lang('result.news') <a
            href="{{ $metager->generateSearchLink('nachrichten') }}">@lang('result.more-news')</a></h3>
    <div id="news" class="inline-results">
        @foreach ($news as $news_result)
            @include('layouts.resultpage.inline_result', [
                'result' => $news_result,
                'index' => 'news_' . ($index + 1),
            ])
        @endforeach
    </div>
@endif
