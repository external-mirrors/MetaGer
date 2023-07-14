@if(sizeof($videos) > 0)
<h3 id="videos-heading" class="inline-heading">@lang('result.videos')</h3>
<div id="videos" class="inline-results">
    @foreach($videos as $video_result)
        @include('layouts.resultpage.inline_result', ['result' => $video_result, 'index' => "videos_" . ($index + 1)])
    @endforeach
</div>
@endif