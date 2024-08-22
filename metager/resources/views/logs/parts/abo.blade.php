<div id="abo">
    <h3>@lang("logs.overview.abo.heading")</h3>
    <p>@lang("logs.overview.abo.hint")</p>
    <div class="settings">
        <div class="input-group">
            <label for="interval">@lang("logs.overview.abo.interval.label")</label>
            <span>@lang("logs.overview.abo.interval.setting_values." . $abo['interval'])</span>
        </div>
        <div class="input-group">
            <label for="last_invoice">@lang("logs.overview.abo.last_invoice")</label>
            <span>{{ $abo["last_invoice"] ?? __("logs.overview.abo.never") }}</span>
        </div>
        <div class="input-group">
            <label for="next_invoice">@lang("logs.overview.abo.next_invoice")</label>
            <span>{{ $abo["next_invoice"] ?? __("logs.overview.abo.never") }}</span>
        </div>
    </div>
    <a href="{{ route('logs:abo') }}" class="btn btn-default">
        @if($abo["interval"] === "never")
            @lang("logs.overview.abo.create")
        @else
            @lang("logs.overview.abo.update")
        @endif
    </a>
</div>