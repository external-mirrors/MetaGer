{{-- Quicktips that do not answer the query (Quicktip::EXTRA_TYPES): after the
     answers, drawn quieter than them (quicktips.less). Not folded away — they
     are short, and wherever they are shown there is room for them. --}}
@if (sizeof($extras) > 0)
    <div class="quicktip-extras">
        @include('quicktips', ['quicktips' => $extras])
    </div>
@endif
