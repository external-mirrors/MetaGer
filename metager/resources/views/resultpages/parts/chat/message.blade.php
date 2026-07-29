{{--
    One transcript entry.

    Assistant content is rendered from its **Markdown source**, server-side, on every render — the
    HTML is deliberately never round-tripped through the form. Hidden fields are user-editable, so
    accepting HTML from them would be a stored-XSS hole; re-rendering from source costs nothing and
    means the only HTML we ever emit came from our own sanitising renderer (html_input => strip,
    allow_unsafe_links => false — see ChatController::renderMarkdown()).

    User content is shown verbatim as escaped text: it is their literal input, not Markdown.
--}}
<div class="chat-message chat-message--{{ $message['role'] }}">
    @if($message['role'] === 'assistant' && !empty($message['model']))
        <div class="chat-message-model">{{ $message['model'] }}</div>
    @endif

    @if(!empty($message['attachments']))
        {{-- The document's text went to the model, not into the page: showing it here would repeat
             a file the user already has, at whatever length they uploaded. The name is the useful
             part — it says which turn the answer was grounded in. --}}
        <ul class="chat-message-attachments">
            @foreach($message['attachments'] as $attachment)
                <li class="chat-attachment-chip">{{ $attachment['name'] }}</li>
            @endforeach
        </ul>
    @endif

    <div class="chat-message-body">
        @if($message['role'] === 'assistant')
            {!! \Illuminate\Support\Str::markdown($message['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        @else
            {{ $message['content'] }}
        @endif
    </div>
</div>
