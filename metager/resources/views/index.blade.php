@extends('layouts.staticPages', ['page' => 'startpage'])
@section('title', $title)

@section('content')
<div class="skiplinks">
  <div>@lang('resultPage.skiplinks.heading')</div>
  <a href="#eingabe" id="skipto-search" class="skip-link">@lang('index.skip.search')</a>
  <a href="#sidebarToggle" id="skipto-navigation" class="skip-link">@lang('index.skip.navigation')</a>
  @if(Auth::guard("key")->user() !== null || app(App\Models\Authorization\Authorization::class)->loggedIn)
  <a href="#foki-switcher" id="skipto-fokus" class="skip-link">@lang('index.skip.fokus')</a>
  @endif
  <div class="escape">@lang('resultPage.skiplinks.return')</div>
</div>
<div id="search-content">
  @if(Auth::guard("key")->user() !== null || app(App\Models\Authorization\Authorization::class)->loggedIn)
  <ul id="foki-switcher">
    {{-- Which of these survive a narrow screen is decided in
         pages/startpage/startpage.less, by position: the first three are the
         ones people use, and the active one is never dropped whatever its
         position. It used to be an $index > 4 check here paired with a .hide-xs
         rule that only fired below 350px — by which point the row had been
         running underneath the navigation cluster for 250px of viewport. --}}
    @foreach(app()->make(\App\Searchengines::class)->available_foki as $index => $fokus)
    <li>
      <a href="{{ route('startpage', ['focus' => $fokus]) }}" @if(app(\App\SearchSettings::class)->fokus === $fokus)
    class="active" aria-current="page" @endif>@lang("index.foki.$fokus")</a>
    </li>
  @endforeach
    <li>
      <a href="{{ route('startpage', ['focus' => "maps"]) }}" @if(app(\App\SearchSettings::class)->fokus === "maps")
  class="active" aria-current="page" @endif>@lang("index.foki.maps")</a>
    </li>
  </ul>
  @endif

  <div id="search-wrapper">
    <h1 id="startpage-logo">
      <a class="logo" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}">
        <img src="/img/metager.svg" alt="MetaGer" />
      </a>
      <a class="lang" href="{{ route('lang-selector') }}" aria-label="@lang('index.lang')">
        <span>{{ App\Localization::getRegion() }}</span>
      </a>
    </h1>

    @if(Auth::guard("key")->user() === null &&!app(App\Models\Authorization\Authorization::class)->loggedIn)
    {{-- Five blocks, one action. Everything here used to be said three times: a
         status line stating the obvious, a helper paragraph, and a small-print
         warning below the buttons that was the only text that mattered.

         The strings marked data-* are rewritten in place by
         resources/js/accountBreadcrumb.js when this browser has rendered a
         signed-in page before. Rewritten, not revealed — nothing is inserted or
         hidden, so the layout does not move and there is no CSS rule that can
         accidentally take a sibling with it. Without JS this is what everyone
         gets, and it already leads with "log in". --}}
    <div id="searchbar-replacement"
      data-welcome-back-hook="@lang('index.searchbar-replacement.welcome_back')"
      data-welcome-back-message="@lang('index.searchbar-replacement.welcome_back_message')"
      data-welcome-back-button="@lang('index.searchbar-replacement.welcome_back_button')">
      <div class="tagline">@lang('index.searchbar-replacement.tagline')</div>
      <div class="hook-line" data-hook-line>@lang('index.searchbar-replacement.hook')</div>
      {{-- Log in is the primary action: most people on this page have used MetaGer before and
           just lost the cookie. Creating a second key splits their token balance, and the
           warning about that now lives on the keymanager's create page, which is where the
           second key actually gets made. --}}
      <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter?redirect_success=' . urlencode(route('startpage'))) }}" class="btn startpage-login-btn" data-login-button>
        @lang('index.searchbar-replacement.have_key')
      </a>
      <div class="helper-line" data-helper-line>@lang('index.searchbar-replacement.message')</div>
      <div class="first-time-line">
        @lang('index.searchbar-replacement.first_time')
        <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys') }}" class="startpage-create-link">@lang('index.searchbar-replacement.start')</a>
      </div>
    </div>
  @else
  @include('parts.searchbar', ['class' => 'startpage-searchbar'])
@endif
    {{-- The only place an exhausted key can be told about it. A search with no
         tokens never reaches a result page: every route to /meta/meta.ger3 runs
         AuthenticationValidation, and every unauthorised branch of it redirects
         back here. So the warning belongs before the search, not after it.

         This replaces #startpage-quicklinks, whose second clause read the legacy
         Authorization service and was not gated on the key guard — which is how
         "142 Token" and "Token aufgebraucht" came to render for the same
         visitor on the same screen. --}}
    @if(\Auth::guard("key")->user() !== null && \Auth::guard("key")->user()->getKeyState() === \App\Authentication\KeyState::EMPTY)
    <div id="account-empty-alert">
      {!! \App\Authentication\KeyIdenticon::render(\Auth::guard("key")->user()->getKeyFingerprint()) !!}
      <span class="account-empty-alert__message">@lang('account.empty.message')</span>
      <a class="account-empty-alert__action" href="{{ LaravelLocalization::getLocalizedURL(null, "/keys/key/enter") }}">@lang('account.empty.action')</a>
    </div>
  @endif
  </div>
  <div id="tiles-container">
    <div id="tiles">
      @foreach($tiles as $tile)
      @include("parts.tile", ["tile" => $tile])
    @endforeach
    </div>
  </div>
  <div id="language">
    <a href="{{ route('lang-selector') }}">{{ LaravelLocalization::getCurrentLocaleNative() }}</a>
  </div>
  <div id="scroll-links">
    <a href="#story-privacy" title="{{ trans('mg-story.privacy.title') }}"><img src="/img/svg-icons/lock.svg"
        alt="{{ trans('mg-story.privacy.image.alt') }}">
      <div>@lang("mg-story.privacy.title")</div>
    </a>
    <a href="#story-ngo" title="{{ trans('mg-story.ngo.title') }}"><img src="/img/svg-icons/heart.svg"
        alt="{{ trans('mg-story.ngo.image.alt') }}">
      <div>@lang("mg-story.ngo.title")</div>
    </a>
    <a href="#story-diversity" title="{{ trans('mg-story.diversity.title') }}"><img src="/img/svg-icons/rainbow.svg"
        alt="{{ trans('mg-story.diversity.image.alt') }}">
      <div>@lang("mg-story.diversity.title")</div>
    </a>
    <a href="#story-eco" title="{{ trans('mg-story.eco.title') }}"><img src="/img/svg-icons/leaf.svg"
        alt="{{ trans('mg-story.eco.image.alt') }}">
      <div>@lang("mg-story.eco.title")</div>
    </a>
  </div>
</div>
<div id="story-container">
  <section id="story-privacy">
    <h1>{{ trans('mg-story.privacy.title') }}</h1>
    <figure class="story-icon">
      <img src="/img/svg-icons/lock.svg" alt="{{ trans('mg-story.privacy.image.alt') }}">
    </figure>
    <p>{!! trans('mg-story.privacy.p') !!}</p>
    <ul class="story-links">
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "about") }}">{{ trans('about.head.1') }}</a>
      </li>
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "datenschutz") }}">{{ trans('mg-story.btn-data-protection') }}</a>
      </li>
    </ul>
  </section>
  <section id="story-ngo">
    <h1>{{ trans('mg-story.ngo.title') }}</h1>
    <figure class="story-icon">
      <img src="/img/svg-icons/heart.svg" alt="{{ trans('mg-story.ngo.image.alt') }}">
    </figure>
    <p>{!!trans('mg-story.ngo.p') !!}</p>
    <ul class="story-links">
      <li><a class="story-button" href="https://suma-ev.de/" target="_blank">{{ trans('mg-story.btn-SUMA-EV') }}</a>
      </li>
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "spende") }}">{{ trans('mg-story.btn-donate') }}</a>
      </li>
      <li><a class="story-button" href="{{ route('membership_form') }}"
          target="_blank">{{ trans('mg-story.btn-member') }}</a></li>
      <li><a class="story-button" href="https://suma-ev.de/mitglieder/" target="_blank">
          {{ trans('mg-story.btn-member-advantage') }}</a></li>
    </ul>
  </section>
  <section id="story-diversity">
    <h1>{{ trans('mg-story.diversity.title') }}</h1>
    <figure class="story-icon">
      <img src="/img/svg-icons/rainbow.svg" alt="{{ trans('mg-story.diversity.image.alt') }}">
    </figure>
    <p>{!! trans('mg-story.diversity.p') !!}</p>
    <ul class="story-links">
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "about") }}">{{ trans('about.head.1') }}</a>
      </li>
      <li><a class="story-button" href="https://gitlab.metager.de/open-source/MetaGer"
          target="_blank">{{ trans('mg-story.btn-mg-code') }}</a></li>
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "transparency") }}">{{ trans('mg-story.btn-mg-algorithm') }}</a>
      </li>
    </ul>
  </section>

  <section id="story-eco">
    <h1>{{ trans('mg-story.eco.title') }}</h1>
    <figure class="story-icon">
      <img src="/img/svg-icons/leaf.svg" alt="{{ trans('mg-story.eco.image.alt') }}">
    </figure>
    <p>{!! trans('mg-story.eco.p')!!}</p>
    <ul class="story-links">
      <li><a class="story-button" href="https://www.hetzner.de/unternehmen/umweltschutz/"
          target="_blank">{{ trans('mg-story.btn-more') }}</a></li>
    </ul>
  </section>
  <section id="story-plugin">
    <h1>{{ trans('mg-story.plugin.title') }}</h1>
    <figure class="story-icon">
      <picture>
        <img src="/img/story-plugin.svg" alt="{{ trans('mg-story.plugin.image.alt') }}">
      </picture>

    </figure>
  <p>@lang('mg-story.plugin.p', ['anonymousTokenLink' => url('/help/anonymous-token')])</p>
    <ul class="story-links">
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin") }}">{{ trans('mg-story.plugin.btn-add') }}</a>
      </li>
      <li><a class="story-button"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/app") }}">{{ trans('mg-story.plugin.btn-app') }}</a>
      </li>
    </ul>
  </section>
</div>
@endsection