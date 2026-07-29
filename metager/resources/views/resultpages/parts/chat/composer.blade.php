{{--
    The composer is a real <form>. Without JS it submits normally: the whole transcript round-trips
    through hidden fields, the server calls the model, and the page re-renders with the answer.
    With JS (added in the enhancement bundle) the same form is intercepted and the same endpoint is
    asked for a token stream instead. One form, one route, two encodings — see
    docs/llm/metager-integration/native-frontend.md.

    Nothing is stored server-side: the hidden fields *are* the conversation state. A user can edit
    them, which is uninteresting — they can only misrepresent their own conversation to themselves,
    and they pay for the tokens either way. What must never be trusted from here is rendered HTML,
    which is why only Markdown source travels (see parts/chat/message.blade.php).
--}}
<form id="chat-composer" class="chat-composer" method="POST"
      action="{{ LaravelLocalization::getLocalizedURL(null, '/chat/message') }}">

    {{-- Keeps the foki switcher highlighting "Chat" when the no-JS response re-renders. --}}
    <input type="hidden" name="focus" value="chat">

    @foreach($transcript as $index => $message)
        <input type="hidden" name="messages[{{ $index }}][role]" value="{{ $message['role'] }}">
        <input type="hidden" name="messages[{{ $index }}][content]" value="{{ $message['content'] }}">
    @endforeach

    @if(count($models) > 0)
        {{--
            <details> + radio inputs are pure HTML, so the picker works fully without JS. The
            enhancement bundle upgrades it to a popover that closes on outside-click; it does not
            replace it.
        --}}
        <details class="chat-model-picker">
            <summary>
                <span class="chat-model-picker-label">@lang('chat.model.label')</span>
                <span class="chat-model-picker-current">{{ $selectedModelName }}</span>
            </summary>
            <ul class="chat-model-list">
                @foreach($models as $model)
                    <li>
                        <label class="chat-model-option">
                            <input type="radio" name="modelId" value="{{ $model['id'] }}"
                                   @checked($model['id'] === $selectedModelId)>
                            <span class="chat-model-option-name">{{ $model['display_name'] }}</span>
                            @if(isset($model['cost_typical_message']))
                                {{-- A real ballpark cost, in the same units as the header's balance
                                     widget, so the choice is informed rather than vibes-based. --}}
                                <span class="chat-model-option-cost">
                                    @lang('chat.model.cost_per_message', ['cost' => $model['cost_typical_message']])
                                </span>
                            @endif
                        </label>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif

    <div class="chat-input-row">
        <label for="chat-input" class="sr-only">@lang('chat.composer.label')</label>
        <textarea id="chat-input" class="chat-input" name="message" rows="1"
                  placeholder="@lang('chat.composer.placeholder')"
                  autofocus required></textarea>
        <button type="submit" class="chat-send btn btn-primary">@lang('chat.composer.send')</button>
    </div>

    {{-- Only meaningful with JS; hidden until the enhancement bundle enables it. --}}
    <button type="button" id="chat-stop" class="chat-stop btn btn-default" hidden>
        @lang('chat.composer.stop')
    </button>
</form>
