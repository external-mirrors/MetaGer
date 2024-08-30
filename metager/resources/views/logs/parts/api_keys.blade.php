<div id="api-keys">
    <h2>@lang("logs.api_keys.heading")</h2>
    <p>@lang("logs.api_keys.hint")</p>
    @if(sizeof(app(App\Models\Logs\LogsAccountProvider::class)->client->access_keys) > 0)
        <table>
            <thead>
                <tr>
                    <td>@lang("logs.api_keys.thead.name")</td>
                    <td>@lang("logs.api_keys.thead.key")</td>
                    <td>@lang("logs.api_keys.thead.created_at")</td>
                    <td>@lang("logs.api_keys.thead.accessed_at")</td>
                    <td>@lang("logs.api_keys.thead.actions")</td>
                </tr>
            </thead>
            <tbody>
                @foreach(app(App\Models\Logs\LogsAccountProvider::class)->client->access_keys as $access_key)
                    <tr>
                        <td>{{ $access_key->name }}</td>
                        <td>
                            @if(strpos($access_key->key, "*") === 0)
                                {{ $access_key->key }}
                            @else
                                <div class="copyLink">
                                    <input type="text" class="access-key" value="{{ $access_key->key }}" size="36">
                                    <button class="btn btn-default">@lang("logs.api_keys.copy")</button>
                                </div>
                            @endif
                        </td>
                        <td>{{ $access_key->created_at->format("d.m.Y H:i:s") }}</td>
                        <td>
                            @if(!is_null($access_key->accessed_at))
                                {{ $access_key->accessed_at->format("d.m.Y H:i:s") }}
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('logs:access-key-delete') }}" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <input type="hidden" name="id" value="{{ $access_key->id }}">
                                <button type="submit" class="btn btn-default">@lang("logs.api_keys.delete")</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <h3>@lang("logs.api_keys.new.heading")</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error_message)
                    <li>{{ $error_message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('logs:access-key') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="input-group">
            <label for="name">@lang("logs.api_keys.new.name")</label>
            <input type="text" name="name" id="name" required placeholder="@lang('logs.api_keys.new.placeholder_name')"
                maxlength="25" value="{{ old("name") }}">
        </div>
        <button type=" submit" class="btn btn-default">@lang("logs.api_keys.new.submit")</button>
    </form>
</div>