<div id="abo">
    <h3>@lang("logs.overview.abo.heading")</h3>
    <p>@lang("logs.overview.abo.hint")</p>
    <div class="settings">
        <div class="input-group">
            <label for="interval">@lang("logs.overview.abo.interval.label")</label>
            <span>@lang("logs.overview.abo.interval.setting_values." . app(App\Models\Logs\LogsAccountProvider::class)->abo->interval)
                @if(app(App\Models\Logs\LogsAccountProvider::class)->abo->interval !== "never")
                    ({{ app(App\Models\Logs\LogsAccountProvider::class)->abo->getIntervalPrice() }}€)
                @endif
            </span>
        </div>
        <div class="input-group">
            <label for="last_invoice">@lang("logs.overview.abo.last_invoice")</label>
            <span>{{ is_null(app(App\Models\Logs\LogsAccountProvider::class)->abo->getLastInvoiceDate()) ? __("logs.overview.abo.never") : app(App\Models\Logs\LogsAccountProvider::class)->abo->getLastInvoiceDate()->format("d.m.Y") }}</span>
        </div>
        <div class="input-group">
            <label for="next_invoice">@lang("logs.overview.abo.next_invoice")</label>
            <span>{{ is_null(app(App\Models\Logs\LogsAccountProvider::class)->abo->getNextInvoiceDate()) ? __("logs.overview.abo.never") : app(App\Models\Logs\LogsAccountProvider::class)->abo->getNextInvoiceDate()->format("d.m.Y") }}</span>
        </div>
        @if(!is_null($nda))
            <div class="input-group">
                <a href="{{ $nda }}" target="_blank">@lang("logs.create_abo.nda")</a>
            </div>
        @endif
    </div>
    <a href="{{ route('logs:abo') }}" class="btn btn-default">
        @if(app(App\Models\Logs\LogsAccountProvider::class)->abo->interval === "never")
            @lang("logs.overview.abo.create")
        @else
            @lang("logs.overview.abo.update")
        @endif
    </a>
</div>