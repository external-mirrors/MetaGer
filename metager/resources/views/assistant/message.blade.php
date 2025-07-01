<div id="{{ $message->id }}"
    class="message {{ $message->role === App\Models\Assistant\MessageRole::User ? "user" : "agent" }}">
    @foreach($message->getContents() as $content)
        @if($content instanceof App\Models\Assistant\MessageContentText)
            @if($message->role === App\Models\Assistant\MessageRole::Agent)
                {!! Str::of($content->render())->markdown([
                            "html_input" => League\CommonMark\Util\HtmlFilter::ESCAPE,
                        ]) !!}
            @elseif($message->role === App\Models\Assistant\MessageRole::User)
                {{ $content->render() }}
            @endif
        @elseif($content instanceof App\Models\Assistant\MessageContentWebsearch)
            @if(empty($content->render()))
                @lang("assistant.response.content.web_search.loading")
            @else
                @lang("assistant.response.content.web_search.label") <a
                    href="{{ route("resultpage", ["eingabe" => $content->render()]) }}"
                    target="_blank">{{  $content->render() }}</a>
            @endif
        @elseif($content instanceof App\Models\Assistant\MessageContentTyping)
            {!! $content->render() !!}
        @endif
    @endforeach
</div>