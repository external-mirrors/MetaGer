{{-- Quicktips that do not answer the query (Quicktip::EXTRA_TYPES), closed
     until asked for. A <details>, so it opens without JavaScript. --}}
@if (sizeof($extras) > 0)
    <details class="quicktip-extras">
        <summary>
            <span>@lang('result.quicktips.extras')</span>
            <span class="quicktip-extras-count">{{ sizeof($extras) }}</span>
        </summary>
        @include('quicktips', ['quicktips' => $extras])
    </details>
@endif
