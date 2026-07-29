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
@php
    // A key can be supplied by cookie, by header, or in the query string (KeyAuthGuard). Only the
    // first two ride along on a POST by themselves — a query key lives on the *page* URL and would
    // be dropped by the form's own action, leaving those users unable to chat at all. Carrying it
    // forward is the same thing the proxy links do (layouts/result.blade.php).
    $chatAction = LaravelLocalization::getLocalizedURL(null, '/chat/message');
    if (auth()->guard('key')->login_method === 'query' && auth()->guard('key')->user() !== null) {
        $chatAction .= '?key=' . rawurlencode(auth()->guard('key')->user()->key);
    }
@endphp
{{-- multipart because of the file input. The JS path switches to FormData for the same reason; a
     turn without a file still posts urlencoded, which is cheaper to build and to parse. --}}
<form id="chat-composer" class="chat-composer" method="POST" action="{{ $chatAction }}"
      enctype="multipart/form-data">

    {{-- Tells SearchSettings which focus the no-JS response re-renders as. That drives the body
         class the whole chat stylesheet is scoped to, and whether the search chrome renders at all
         (layouts/researchandtabs.blade.php) — ChatController::renderPage() pins it as well, so this
         field is belt to that braces rather than the only thing holding it up. --}}
    <input type="hidden" name="focus" value="chat">

    @foreach($transcript as $index => $message)
        <input type="hidden" name="messages[{{ $index }}][role]" value="{{ $message['role'] }}">
        <input type="hidden" name="messages[{{ $index }}][content]" value="{{ $message['content'] }}">
        {{-- Only the id and label of an attachment travel; its text stays parked with the chat
             service. That is the whole reason attachments work on the no-JS path at all. --}}
        @foreach($message['attachments'] ?? [] as $attachmentIndex => $attachment)
            <input type="hidden" name="messages[{{ $index }}][attachments][{{ $attachmentIndex }}][id]" value="{{ $attachment['id'] }}">
            <input type="hidden" name="messages[{{ $index }}][attachments][{{ $attachmentIndex }}][name]" value="{{ $attachment['name'] }}">
        @endforeach
    @endforeach

    @if(count($models) > 0)
        {{--
            <details> + radio inputs are pure HTML, so the picker works fully without JS. The
            enhancement bundle upgrades it to a popover that closes on outside-click; it does not
            replace it.
        --}}
        @php
            // Descriptions live in MetaGer's lang files rather than in the chat service's model
            // config: they are prose for users and have to be translated, and this service has no
            // locale. Keyed by model id, so a model the catalog gains before the lang file does
            // simply shows without a description instead of breaking.
            $modelDescriptions = trans('chat.model.descriptions');
        @endphp
        <details class="chat-model-picker" id="chat-model-picker">
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
                            <span class="chat-model-option-text">
                                <span class="chat-model-option-head">
                                    <span class="chat-model-option-name">{{ $model['display_name'] }}</span>
                                    @if(isset($model['speed']) && trans()->has('chat.model.speed.' . $model['speed']))
                                        <span class="chat-model-option-speed chat-model-option-speed--{{ $model['speed'] }}">
                                            @lang('chat.model.speed.' . $model['speed'])
                                        </span>
                                    @endif
                                    @if(isset($model['cost_typical_message']))
                                        {{-- A real ballpark cost, in the same units as the header's
                                             balance widget, so the choice is informed rather than
                                             vibes-based. --}}
                                        <span class="chat-model-option-cost">
                                            @lang('chat.model.cost_per_message', ['cost' => $model['cost_typical_message']])
                                        </span>
                                    @endif
                                </span>
                                @if(is_array($modelDescriptions) && isset($modelDescriptions[$model['id']]))
                                    <span class="chat-model-option-description">{{ $modelDescriptions[$model['id']] }}</span>
                                @endif
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif

    {{-- A plain file input, so attaching a document needs no JS. The enhancement bundle hides the
         input behind a button and shows the chosen file as a removable chip; it does not replace
         it. One file per turn: the transcript can carry any number across turns, but a composer
         that queues several needs JS to manage the queue, and the no-JS path could not follow. --}}
    <div class="chat-attach-row">
        <label for="chat-attachment" class="chat-attach-label">@lang('chat.attachment.label')</label>
        <input type="file" id="chat-attachment" class="chat-attach-input" name="attachment">
    </div>

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
