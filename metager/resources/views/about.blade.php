@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div class="static-page-header">
	<h1>{{ trans('about.head.1') }}</h1>
</div>
<div class="card">
	<h1>{{ trans('about.head.3') }}</h1>
	<h3><img id="about-lock" src="/img/lock.svg"> @lang('about.points.privacy.heading')</h3>
	<p>@lang('about.points.privacy.text')</p>

	<h3><img id= "about-heart" src="/img/heart.svg"> @lang('about.points.association.heading')</h3>
	<p>@lang('about.points.association.text')</p>

	<h3><img id= "about-rainbow" src="/img/rainbow.svg"> @lang('about.points.diverse.heading', ["transparenz" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "transparency")])</h3>
	<p>@lang('about.points.diverse.text')</p>

	<h3><img id="about-leaf" src="/img/leaf.svg"> @lang('about.points.renewable.heading')</h3>
	<p>@lang('about.points.renewable.text')</p>
</div>
<div class="card">
	<h1>{{ trans('about.head.4') }}</h1>
	<p>{{ trans('about.text.2') }}</p>
	<ul>
		<li>{{ trans('about.text.3') }}</li>
		<li>{{ trans('about.text.4') }}</li>
		<li>{{ trans('about.text.5') }}</li>
		<li>{{ trans('about.text.6') }}</li>
		<li>{{ trans('about.text.7') }}</li>
		<li>{{ trans('about.text.8') }}</li>

	</ul>
</div>
<div class="card">
	<h1>{{ trans('about.head.2') }}</h1>
	<div class="timeline-container">
		<div>
			<h2>{{ trans('about.timeline.1.1') }}</h2>
			<p>{{ trans('about.timeline.1.2') }}</p>
		</div>

		<div>
			<h2>{{ trans('about.timeline.2.1') }}</h2>
			<p>{{ trans('about.timeline.2.2') }}</p>

		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.3.1') }}</h2>
			<p>{{ trans('about.timeline.3.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_1997.avif" type="image/avif">
				<img src="/img/startpage_1997.png" alt="MetaGer 1997" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.4.1') }}</h2>
			<p>{!! trans('about.timeline.4.2') !!}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.5.1') }}</h2>
			<p>{{ trans('about.timeline.5.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2006.avif" type="image/avif">
				<img src="/img/startpage_2006.png" alt="MetaGer 2006" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.6.1') }}</h2>
			<p>{{ trans('about.timeline.6.2') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.7.1') }}</h2>
			<p>{{ trans('about.timeline.7.2') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.8.1') }}</h2>
			<p>{{ trans('about.timeline.8.2') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.9.1') }}</h2>
			<p>{{ trans('about.timeline.9.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2015.avif" type="image/avif">
				<img src="/img/startpage_2015.png" alt="MetaGer 2015" style="width:auto;">
			</picture>
		</div>

		<div>
			<h2>{{ trans('about.timeline.10.1') }}</h2>
			<p>{{ trans('about.timeline.10.2') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.11.1') }}</h2>
			<p>{{ trans('about.timeline.11.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2016.avif" type="image/avif">
				<img src="/img/startpage_2016.png" alt="MetaGer 2016" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.12.1') }}</h2>
			<p>{{ trans('about.timeline.12.2') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.13.1') }}</h2>
			<p>{{ trans('about.timeline.13.2') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.14.1') }}</h2>
			<p>{{ trans('about.timeline.14.2') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.15.1') }}</h2>
			<p>{{ trans('about.timeline.15.2') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.16.1') }}</h2>
			<p>{{ trans('about.timeline.16.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2019.avif" type="image/avif">
				<img src="/img/startpage_2019.png" alt="MetaGer 2019">
			</picture>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.17.1') }}</h2>
			<p>{!! trans('about.timeline.17.2') !!}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2020.avif" type="image/avif">
				<img src="/img/startpage_2020.png" alt="MetaGer 2020">
			</picture>
		</div>
		<div class="timeline-item-alternate" >
			<h2>{{ trans('about.timeline.18.1') }}</h2>
			<p>{{ trans('about.timeline.18.2') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/help-page_2022.avif" type="image/avif">
				<img src="/img/help-page_2022.png" alt="MetaGer Hilfe Seite">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.19.1') }}</h2>
			<p>{{ trans('about.timeline.19.2') }}</p>
		</div>
	</div>
</div>

@endsection