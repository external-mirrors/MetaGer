@extends('layouts.staticPages', ['page' => 'startpage'])
@section('title', $title)

@section('content')
<div class="skiplinks">
  <div>@lang('resultPage.skiplinks.heading')</div>
  <a href="#eingabe" id="skipto-search" class="skip-link">@lang('index.skip.search')</a>
  <a href="#sidebarToggle" id="skipto-navigation" class="skip-link">@lang('index.skip.navigation')</a>
  <a href="#foki-switcher" id="skipto-fokus" class="skip-link">@lang('index.skip.fokus')</a>
  <div class="escape">@lang('resultPage.skiplinks.return')</div>
</div>
<div id="search-content">
  <ul id="foki-switcher">
    @foreach(app()->make(\App\Searchengines::class)->available_foki as $index => $fokus)
    <li @if($index > 4) class="hide-xs" @endif>
      <a href="{{ route('startpage', ['focus' => $fokus]) }}" @if(app(\App\SearchSettings::class)->fokus === $fokus)
    class="active" aria-current="page" @endif>@lang("index.foki.$fokus")</a>
    </li>
  @endforeach
    <li class="hide-xs">
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

    @if(Auth::guard("key")->user() === null &&!app(App\Models\Authorization\Authorization::class)->loggedIn)
    <div id="searchbar-replacement" style="">
      <div class="input-group">
      <div class="inputs">
        <div class="not-logged-in-info">
          @lang('index.searchbar-replacement.not_logged_in', ['default' => 'Sie sind aktuell nicht eingeloggt.'])
        </div>
        <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys/key/enter?redirect_success=' . urlencode(route('startpage'))) }}" class="btn btn-default startpage-login-btn">
          @lang('index.searchbar-replacement.login')
        </a>
        <a href="{{ LaravelLocalization::getLocalizedURL(null, '/keys#how-it-works') }}" class="btn btn-primary startpage-create-btn">
          @lang('index.searchbar-replacement.start')
        </a>
      </div>
      <label for="key">
        @lang("index.searchbar-replacement.message", ['anonym_link' => LaravelLocalization::getLocalizedURL(null, '/keys') . "#no-tradeoff"])
        @if(\App\Localization::getLanguage() === "de")
        <a href="https://suma-ev.de/eine-aera-geht-zu-ende/">@lang("index.searchbar-replacement.why")</a>
        @else
        <a href="https://suma-ev.de/en/eine-aera-geht-zu-ende/">@lang("index.searchbar-replacement.why")</a>
        @endif
      </label>
      </div>
      <div>

      </div>
    </div>
  @else
  @include('parts.searchbar', ['class' => 'startpage-searchbar'])
@endif
    @if((\Auth::guard("key")->user() !== null && \Auth::guard("key")->user()->getKeyState() === \App\Authentication\KeyState::EMPTY) || 
    {{-- Phase out old Authorization @deprecated 18.07.2025 --}}
    (app(\App\Models\Authorization\Authorization::class)->availableTokens >= 0 && !app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch(false)))
    <div id="startpage-quicklinks">
      <a class="metager-key" href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}">
      <img src="/img/svg-icons/key-empty.svg" alt="Key Icon" />
      <span>
        @lang("index.key.tooltip.empty")
      </span>
      </a>
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
    <p>{{ trans('mg-story.plugin.p') }}</p>
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