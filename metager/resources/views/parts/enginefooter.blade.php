@php
$searchengines = app(\App\Models\Configuration\Searchengines::class);
$settings = app(\App\SearchSettings::class);
@endphp
<div id="engine-footer">
    @if(sizeof($searchengines->getEnabledSearchengines()) > 0)
    <div class="enabled-engines">
        <h3>@lang('resultPage.engines.queried')</h3>
        <div class="engines disabled">
            @foreach(app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengines() as $sumaName => $suma)
            <div class="engine">{{ $suma->configuration->infos->displayName }} {{ $suma->configuration->cost > 0 ? "(" . $suma->configuration->cost . " Token)" : "" }}</div>
            @endforeach
        </div>
    </div>
    @endif
    @if($searchengines->hasDisabledSearchenginesWithReason(\App\Models\DisabledReason::USER_CONFIGURATION))
    <div class="disabled-engines">
        <h3>@lang('resultPage.engines.disabled')</h3>
        <div class="engines">
            @foreach(app(\App\Models\Configuration\Searchengines::class)->sumas as $sumaName => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::USER_CONFIGURATION)
            <div class="engine disabled-by-configuration">
                <a href="{{ route('resultpage', array_merge(Request::all(), [$settings->fokus . '_engine_' . $sumaName => 'on'])) }}">
                    {{ $suma->configuration->infos->displayName }} {{ $suma->configuration->cost > 0 ? "(" . $suma->configuration->cost . " Token)" : "" }}
                </a>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
    @if($searchengines->hasDisabledSearchenginesWithReason(\App\Models\DisabledReason::PAYMENT_REQUIRED))
    <div class="payment-required-engines">
        <h3>@lang('resultPage.engines.payment_required', ["link" => app(\App\Models\Authorization\Authorization::class)->getAdfreeLink()])</h3>
        <div class="engines disabled">
            @foreach($searchengines->sumas as $sumaName => $suma)
            @if($suma->configuration->disabled && $suma->configuration->disabledReason === \App\Models\DisabledReason::PAYMENT_REQUIRED)
            <div class="engine disabled-by-configuration">{{ $suma->configuration->infos->displayName }} {{ $suma->configuration->cost > 0 ? "(" . $suma->configuration->cost . " Token)" : "" }}</div>
            @endif
            @endforeach
        </div>
    </div>
    @endif
</div>