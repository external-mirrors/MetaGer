{{-- The quicktip column beside the results, from 1350px up. Below that it is
     hidden and parts/quicktips-inline puts the same quicktips into the list. --}}
@if ($quicktips !== null && sizeof($quicktips) > 0)
    <div id="additions-container">
        <div id="quicktips">
            @if (app(\App\SearchSettings::class)->quicktips)
                @include('quicktips', ['quicktips' => array_filter($quicktips, fn($quicktip) => !$quicktip->isExtra())])
                @include('parts.quicktip-extras', ['extras' => array_filter($quicktips, fn($quicktip) => $quicktip->isExtra())])
            @endif
        </div>
    </div>
@endif
