@php
$searchengines = app(\App\Models\Configuration\Searchengines::class);
$settings = app(\App\SearchSettings::class);
@endphp
<div id="engine-footer">
    @if(sizeof($searchengines->getEnabledSearchengines()) > 0)
    <div class="enabled-engines">
        <h3>Abgefragte Suchdienste</h3>
        <div class="engines disabled">
            @foreach(app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengines() as $sumaName => $suma)
            <div class="engine">{{ $suma->configuration->infos->displayName }}</div>
            @endforeach
        </div>
    </div>
    @endif
    @if($searchengines->hasDisabledSearchenginesWithReason(\App\Models\DisabledReason::USER_CONFIGURATION))
    <div class="disabled-engines">
        <h3>Suchdienste zur Abfrage hinzufügen</h3>
        <div class="engines">
            @foreach(app(\App\Models\Configuration\Searchengines::class)->sumas as $sumaName => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::USER_CONFIGURATION)
            <div class="engine disabled-by-configuration">
                <a href="{{ LaravelLocalization::getLocalizedURL(null, route('resultpage', array_merge(Request::all(), [$settings->fokus . '_engine_' . $sumaName => 'on']))) }}">
                    {{ $suma->configuration->infos->displayName }}
                </a>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
    @if($searchengines->hasDisabledSearchenginesWithReason(\App\Models\DisabledReason::PAYMENT_REQUIRED))
    <div class="payment-required-engines">
        <h3>Suchdienste verfügbar mit <a href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}">MetaGer Schlüssel</a></h3>
        <div class="engines disabled">
            @foreach($searchengines->sumas as $sumaName => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::PAYMENT_REQUIRED)
            <div class="engine disabled-by-configuration">{{ $suma->configuration->infos->displayName }}</div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
</div>