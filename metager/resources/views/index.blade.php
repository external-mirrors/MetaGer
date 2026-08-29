@extends('layouts.staticPages', ['page' => 'startpage'])
@section('title', $title)

@section('content')
{{--
  The startpage, in two states.

  Signed in it is a search engine and nothing else has changed: the fokus row,
  the search bar, the quicklink tiles. Signed out it is the landing page that
  used to live at /keys — hero, "how it works", five benefit cards — because
  that page and this one were explaining the same product to the same people in
  two codebases, and only one of them was on the domain anyone types.

  What both states share is the bottom: who runs MetaGer, and how to install it.
  A signed-in visitor has already bought the thing, so the marketing above is
  not for them; the association that funds it and the extension that keeps them
  signed in are.

  The four bildschirmhohe #story-* sections and the #scroll-links row that
  jumped between them are gone. Three of them are the three cards in
  parts/landing/org.blade.php; #story-privacy is said properly by the benefit
  cards instead, and #story-plugin is the closing band.
--}}
@php($signedIn = Auth::guard("key")->user() !== null || app(App\Models\Authorization\Authorization::class)->loggedIn)
<div class="skiplinks">
  <div>@lang('resultPage.skiplinks.heading')</div>
  @if($signedIn)
  <a href="#eingabe" id="skipto-search" class="skip-link">@lang('index.skip.search')</a>
@endif
  <a href="#sidebarToggle" id="skipto-navigation" class="skip-link">@lang('index.skip.navigation')</a>
  @if($signedIn)
  <a href="#foki-switcher" id="skipto-fokus" class="skip-link">@lang('index.skip.fokus')</a>
@endif
  <div class="escape">@lang('resultPage.skiplinks.return')</div>
</div>

@if(!$signedIn)
  @include('parts.landing.hero')
  @include('parts.landing.how-it-works')
  @include('parts.landing.benefits')
@else
<div id="search-content">
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

  <div id="search-wrapper">
    <h1 id="startpage-logo">
      <a class="logo" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}">
        <img src="/img/metager.svg" alt="MetaGer" />
      </a>
      <a class="lang" href="{{ route('lang-selector') }}" aria-label="@lang('index.lang')">
        <span>{{ App\Localization::getRegion() }}</span>
      </a>
    </h1>

    @include('parts.searchbar', ['class' => 'startpage-searchbar'])

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
      <a class="account-empty-alert__action" href="{{ App\Landing\KeymanagerLinks::enter() }}">@lang('account.empty.action')</a>
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
</div>
@endif

@include('parts.landing.org')
@include('parts.landing.install')
@endsection
