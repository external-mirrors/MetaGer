{{--
  The account pill — the one account control, in the one corner, on every page.

  It replaces three things that each answered part of the question and none of it
  well: the text label inside the startpage search bar, the key icon inside the
  result-page search bar, and the orange dot on the sidebar opener.

  $density decides how much of the answer fits:
    full    — mark + key code + balance      (startpage: room, and it is the
                                              surface where people first learn
                                              where their account lives)
    compact — mark + balance                 (result page: a sticky bar with no
                                              horizontal room to spare)
    mark    — mark only                      (narrow screens; identity is the
                                              part that survives, the rest is one
                                              tap away in the menu)

  It links where the menu goes, because the menu is where the detail is. Sighted
  users read the pill; the aria-label carries the same facts for everyone else,
  since the mark is decorative markup and the code is styled shorthand.
--}}
@php($accountUser = \Auth::guard("key")->user())
@if($accountUser !== null)
  @php($accountDensity = $density ?? 'full')
  @php($accountFingerprint = $accountUser->getKeyFingerprint())
  @php($accountCharge = $accountUser->getCharge())
  @php($accountState = $accountUser->getKeyState())
  @php($accountAnonymous = $accountFingerprint === null && $accountCharge === null)

  {{--
    Anonymous is not a degraded state, it is the webextension working: the key
    never reached us, so there is no balance and nothing to draw. Saying
    "anonym angemeldet" is the honest answer, and the extension shows the real
    account in its own popup.

    Which is also where the pill goes in that state. Everywhere else it leads to
    /keys/key/enter, because that is where the account is managed — but a user
    who is signed in anonymously has no key to enter and would not want to enter
    it here if they had: handing it to us is the one thing the arrangement exists
    to avoid. The extension is the only party that can show that account, so the
    pill opens the extension's popup, the same way the "manage in the extension"
    button in the site menu does.

    `data-extension-settings` is the hook it does that through
    (contentScripts/metagerPage.js in the webextension repo). An attribute rather
    than an id: the result page renders two pills — one in the research bar, one
    in the navigation cluster — and shows whichever the viewport calls for.

    The href is a fallback and has to stand on its own, because a content script
    is not guaranteed to run. It points at the page explaining what an anonymous
    token is, which is the best answer this site can give without the extension.
  --}}
  @php($accountHref = $accountAnonymous
    ? App\Landing\KeymanagerLinks::anonymousToken()
    : App\Landing\KeymanagerLinks::enter())
  <a href="{{ $accountHref }}"
    id="account-pill"
    @if($accountAnonymous) data-extension-settings @endif
    class="account-pill account-pill--{{ $accountDensity }} @if($accountAnonymous) account-pill--anonymous @else account-pill--{{ strtolower($accountState->name) }} @endif {{ $class ?? '' }}"
    @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top" @endif
    aria-label="{{ $accountAnonymous
      ? __('account.pill.aria_anonymous')
      : ($accountFingerprint !== null && $accountCharge !== null
        ? __('account.pill.aria', ['fingerprint' => strtoupper($accountFingerprint), 'charge' => (int) floor($accountCharge)])
        : ($accountFingerprint !== null
          ? __('account.pill.aria_nocharge', ['fingerprint' => strtoupper($accountFingerprint)])
          : __('account.pill.aria_nofingerprint', ['charge' => (int) floor($accountCharge)]))) }}">
    {!! \App\Authentication\KeyIdenticon::render($accountFingerprint) !!}
    @if($accountAnonymous)
      <span class="account-pill__anonymous">@lang('account.pill.anonymous')</span>
    @else
      @if($accountFingerprint !== null)
        <span class="account-pill__code">{{ strtoupper($accountFingerprint) }}</span>
      @else
        <span class="account-pill__code account-pill__code--wordy">@lang('account.pill.signed_in')</span>
      @endif
      @if($accountCharge !== null)
        <span class="account-pill__separator" aria-hidden="true">·</span>
        <span class="account-pill__charge">{{ __('account.pill.charge', ['charge' => (int) floor($accountCharge)]) }}</span>
      @endif
    @endif
  </a>
@endif
