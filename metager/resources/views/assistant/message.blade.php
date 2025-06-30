<div class="message {{ $message->role === App\Models\Assistant\MessageRole::User ? "user" : "agent" }}">
    @foreach($message->getContents() as $content)
        @if($content instanceof App\Models\Assistant\MessageContentText)
            @if($message->role === App\Models\Assistant\MessageRole::Agent)
                {!! Str::of($content->render())->markdown([
                            "html_input" => League\CommonMark\Util\HtmlFilter::ESCAPE,
                        ]) !!}
            @elseif($message->role === App\Models\Assistant\MessageRole::User)
                {{ $content->render() }}
            @endif
        @endif
    @endforeach
</div>