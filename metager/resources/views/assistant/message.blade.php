<div id="{{ $message->id }}"
    class="message {{ $message->role === App\Models\Assistant\MessageRole::User ? "user" : "agent" }}">
    @foreach($message->getContents() as $content)
        @if($content instanceof App\Models\Assistant\MessageContentText)
            {{-- Render the content based on the message role --}}
            {{-- If the message is from the agent, render it as markdown with HTML escaping --}}
            {{-- If the message is from the user, render it as plain text --}}
            {!!  $content->render($message->role) !!}
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