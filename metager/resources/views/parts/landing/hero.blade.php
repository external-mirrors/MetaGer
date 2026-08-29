{{--
  The landing hero — what a visitor without a key sees first.

  It is the old #searchbar-replacement card grown into a page opening, and it
  keeps that id on purpose: resources/js/accountBreadcrumb.js looks the element
  up by it and then rewrites three strings *inside* it, so all three hooks have
  to stay within this one subtree.

  Which three moved. The card used to carry a small "hook line" above the
  button; the page now opens with a real headline and the headline is the hook,
  so [data-hook-line] is the <h1>. A returning visitor whose key cookie is gone
  gets "Willkommen zurück." as the largest text on the page, which is the
  hierarchy that sentence deserves — index.searchbar-replacement.hook has no
  element left and is gone from lang/.

  Log in stays the primary action. Most people who land here have used MetaGer
  before and only lost the cookie, and a second key splits their token balance;
  creating one is the quiet row under the rule, and again as a full button at
  the end of "how it works", where someone who actually needed the explanation
  comes out.
--}}
<section id="landing-hero" class="landing-section">
  <h1 id="startpage-logo">
    <a class="logo" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}">
      <img src="/img/metager.svg" alt="MetaGer" />
    </a>
    <a class="lang" href="{{ route('lang-selector') }}" aria-label="@lang('index.lang')">
      <span>{{ App\Localization::getRegion() }}</span>
    </a>
  </h1>

  <div id="searchbar-replacement"
    data-welcome-back-hook="@lang('index.searchbar-replacement.welcome_back')"
    data-welcome-back-message="@lang('index.searchbar-replacement.welcome_back_message')"
    data-welcome-back-button="@lang('index.searchbar-replacement.welcome_back_button')">
    <div class="tagline">@lang('index.searchbar-replacement.tagline')</div>
    <h2 class="landing-hero__title" data-hook-line>@lang('index.landing.title')</h2>
    <p class="landing-hero__description">@lang('index.landing.description')</p>
    <ul class="landing-hero__checks">
      <li>@lang('index.landing.advantages.ads')</li>
      <li>@lang('index.landing.advantages.tracking')</li>
      <li>@lang('index.landing.advantages.logging')</li>
      <li>@lang('index.landing.advantages.compromise')</li>
    </ul>
    <div class="landing-hero__actions">
      <a href="{{ App\Landing\KeymanagerLinks::enter(route('startpage')) }}" class="btn startpage-login-btn"
        data-login-button>
        @lang('index.searchbar-replacement.have_key')
      </a>
      <div class="helper-line" data-helper-line>@lang('index.searchbar-replacement.message')</div>
      <div class="first-time-line">
        @lang('index.searchbar-replacement.first_time')
        <a href="{{ App\Landing\KeymanagerLinks::create() }}" class="startpage-create-link">@lang('index.searchbar-replacement.start')</a>
      </div>
    </div>
  </div>

  <a class="landing-hero__cue" href="#how-it-works">
    <span>@lang('index.landing.calltoaction')</span>
    <span class="landing-hero__cue-arrow" aria-hidden="true">↓</span>
  </a>
</section>
