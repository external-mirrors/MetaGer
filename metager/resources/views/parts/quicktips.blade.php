@if ($quicktips !== null && sizeof($quicktips) > 0)
    <div id="additions-container">
        <div id="quicktips">
            @if (app(\App\SearchSettings::class)->quicktips)
                @include('quicktips', ['quicktips', $quicktips])
            @endif
        </div>
    </div>
@endif