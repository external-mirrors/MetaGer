{{--
  The top-right controls, as one thing.

  The account pill and the menu button answer two halves of the same question —
  "who am I" and "where is my account" — and they were two independently
  positioned `fixed` elements that happened to land near each other. That meant
  their alignment was arithmetic (two different box models, two different
  `top`s), their z-order was a coincidence, and anything else on that row had to
  reserve space against two moving targets. The fokus switcher on the startpage
  is centred and full width, so between roughly 545px and 900px it ran straight
  underneath them.

  One flex container fixes all three: the two controls centre on each other by
  construction, the cluster hides as a unit when the sidebar covers the corner,
  and the fokus row has a single footprint to keep clear of.

  Not used by layouts/researchandtabs.blade.php — below 920px the result page
  moves both controls into its sticky research bar, where they are grid items
  rather than a floating cluster.
--}}
<div class="navigation-cluster {{ $class ?? '' }}">
  @include('parts.account-pill', ['density' => $density ?? 'full'])
  @include('parts.sidebar-opener')
</div>
