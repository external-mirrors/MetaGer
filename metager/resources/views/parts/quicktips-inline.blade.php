{{-- Quicktips inside the result list, for screens too narrow for the column
     parts/quicktips draws beside it; result-page.less shows one or the other.
     Rendered twice rather than moved, because a quicktip has to land between
     two results without JavaScript, and CSS cannot interleave an element into
     a list it is not part of. The hidden copy is display: none, so a screen
     reader meets each quicktip once.

     $part is "answers" (after the second result) or "extras" (before the
     pager), so the quicktips that do not answer the query stay out of the way. --}}
@if (sizeof($quicktips ?? []) > 0 && app(\App\SearchSettings::class)->quicktips)
    @if ($part === 'answers')
        @php($answers = array_filter($quicktips, fn($quicktip) => !$quicktip->isExtra()))
        @if (sizeof($answers) > 0)
            <section class="quicktips-inline" aria-label="@lang('result.quicktips.heading')">
                @include('quicktips', ['quicktips' => $answers])
            </section>
        @endif
    @else
        @php($extras = array_filter($quicktips, fn($quicktip) => $quicktip->isExtra()))
        @if (sizeof($extras) > 0)
            <div class="quicktips-inline quicktips-inline-extras">
                @include('parts.quicktip-extras', ['extras' => $extras])
            </div>
        @endif
    @endif
@endif
