@extends('layouts.staticPages', ['page' => 'startpage', 'css' => mix('css/themes/startpage-only.css')])

@section('title', $title )

@section('content')
	<div id="search-block">
    <h1 id="startpage-logo">
		<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}">
			<img src="/img/metager.svg" alt="MetaGer" />
		</a>
	</h1>
	@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	<div id="plugin-btn-div">
    <a id="plugin-btn" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin") }}" title="{{ trans('index.plugin-title') }}"><img src="/img/plug-in.svg" alt="Plus-Zeichen"> {{ trans('index.plugin') }}</a>
	</div>
    <div id="center-scroll-link">
       <div id="scroll-link">
    <a href="#story-privacy" class="four-reasons">{{ trans('mg-story.four-reasons') }}</a>
    <a href="#story-privacy" title="{{ trans('mg-story.privacy.title') }}"><img src="/img/lock.svg" alt="{{ trans('mg-story.privacy.image.alt') }}"></a>
    <a href="#story-ngo" title="{{ trans('mg-story.ngo.title') }}"><img src="/img/heart.svg" alt="{{ trans('mg-story.ngo.image.alt') }}"></a>
    <a href="#story-diversity" title="{{ trans('mg-story.diversity.title') }}"><img src="/img/rainbow.svg" alt="{{ trans('mg-story.diversity.image.alt') }}"></a>
    <a href="#story-eco"title="{{ trans('mg-story.eco.title') }}"><img src="/img/leaf.svg" alt="{{ trans('mg-story.eco.image.alt') }}"></a>
  </div>
    </div>
    </div>
    <div id="story-container">
      <section id="story-privacy">
        <h1>{{ trans('mg-story.privacy.title') }}</h1>
        <ul class="story-links"> 
       <li><a class="story-button" href="https://metager.de/about">{{ trans('mg-story.btn-about-us') }}</a></li>
       <li><a class="story-button" href="https://metager.de/datenschutz">{{ trans('mg-story.btn-data-protection') }}</a></li>
        </ul>
        <figure class="story-icon">
          <img src="/img/lock.svg" alt="{{ trans('mg-story.privacy.image.alt') }}">
        </figure>
        <p>{!! trans('mg-story.privacy.p') !!}</p>
      </section>
      <section id="story-ngo">
        <h1>{{ trans('mg-story.ngo.title') }}</h1>

       <ul class="story-links">
        <li><a class="story-button" href="https://suma-ev.de/">{{ trans('mg-story.btn-SUMA-EV') }}</a></li>
        <li><a class="story-button" href="https://metager.de/spende">{{ trans('mg-story.btn-donate') }}</a></li>
        <li><a class="story-button" href="https://metager.de/beitritt">{{ trans('mg-story.btn-member') }}</a></li>
        <li><a class="story-button" href="https://suma-ev.de/mitglieder/"> {{ trans('mg-story.btn-member-advantage') }}</a></li>       </ul>
        <figure class="story-icon">
        <img src="/img/heart.svg" alt="{{ trans('mg-story.ngo.image.alt') }}">
        </figure>
        <p>{!!trans('mg-story.ngo.p') !!}</p>
      </section>
      <section id="story-diversity">
        <h1>{{ trans('mg-story.diversity.title') }}</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://metager.de/about">{{ trans('mg-story.btn-about-us') }}</a></li>
        <li><a class="story-button" href="https://gitlab.metager.de/open-source/MetaGer">{{ trans('mg-story.btn-mg-code') }}</a></li>
        <!--<li><a class="story-button" href="https://metager.de/about">{{ trans('mg-story.btn-mg-algorithm') }}</a></li>-->
        </ul>
        <figure class="story-icon">
          <img src="/img/rainbow.svg" alt="{{ trans('mg-story.diversity.image.alt') }}">
        </figure>
        <p>{!! trans('mg-story.diversity.p') !!}</p>
      </section>
  
      <section id="story-eco">
        <h1>{{ trans('mg-story.eco.title') }}</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://www.hetzner.de/unternehmen/umweltschutz/">{{ trans('mg-story.btn-more') }}</a></li>
        </ul>
        <figure class="story-icon">
          <img src="/img/leaf.svg" alt="{{ trans('mg-story.eco.image.alt') }}">
        </figure>
        <p>{!! trans('mg-story.eco.p')!!}</p>
      </section>
      <section id="story-plugin">
        <h1>{{ trans('mg-story.plugin.title') }}</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://metager.de/plugin">{{ trans('mg-story.plugin.btn-add') }}</a></li>
        <li><a class="story-button" href="https://metager.de/app">{{ trans('mg-story.plugin.btn-app') }}</a></li>
        </ul>
        <figure class="story-icon">
          <picture>
            <source media="(max-width: 760px)" srcset="/img/App.svg">
                    <img src="/img/story-plugin.svg" alt="{{ trans('mg-story.plugin.image.alt') }}">  
          </picture>

        </figure>
        <p>{{ trans('mg-story.plugin.p') }}</p>
      </section>
    </div> 
@endsection
