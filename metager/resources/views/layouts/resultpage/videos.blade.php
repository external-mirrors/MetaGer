@if(sizeof($videos) > 0)
<h3 id="videos-heading" class="inline-heading">@lang('result.videos')</h3>
{{-- See news.blade.php for the frame. --}}
<div class="inline-scroller" data-scroll-back="@lang('result.scroll-back')" data-scroll-forward="@lang('result.scroll-forward')">
    <div id="videos" class="inline-results">
        @foreach($videos as $video_result)
            @include('layouts.resultpage.inline_result', ['result' => $video_result, 'index' => "videos_" . ($index + 1)])
        @endforeach
    </div>
</div>
@endif
