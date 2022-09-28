@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div class="static-page-header">
	<h1>{{ trans('about.head.1') }}</h1>
</div>
<div class="card">
	<h1>{{ trans('about.head.3') }}</h1>
	<p>{!! trans('about.text.1', ["transparenz" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "transparency")]) !!}</p>
</div>
<div class="card">
	<h1>{{ trans('about.head.2') }}</h1>
	<div class="timeline-container">
		<div>
			<h2>{{ trans('about.timeline.1') }}</h2>
			<p>{{ trans('about.timeline.1.1') }}</p>
		</div>

		<div>
			<h2>{{ trans('about.timeline.2') }}</h2>
			<p>{{ trans('about.timeline.2.1') }}</p>

		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.3') }}</h2>
			<p>{{ trans('about.timeline.3.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_1997.avif" type="image/avif">
				<img src="/img/startpage_1997.png" alt="MetaGer 1997" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.4') }}</h2>
			<p>{!! trans('about.timeline.4.1') !!}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.5') }}</h2>
			<p>{{ trans('about.timeline.5.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2006.avif" type="image/avif">
				<img src="/img/startpage_2006.png" alt="MetaGer 2006" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.6') }}</h2>
			<p>{{ trans('about.timeline.6.1') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.7') }}</h2>
			<p>{{ trans('about.timeline.7.1') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.8') }}</h2>
			<p>{{ trans('about.timeline.8.1') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.9') }}</h2>
			<p>{{ trans('about.timeline.9.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2015.avif" type="image/avif">
				<img src="/img/startpage_2015.png" alt="MetaGer 2015" style="width:auto;">
			</picture>
		</div>

		<div>
			<h2>{{ trans('about.timeline.10') }}</h2>
			<p>{{ trans('about.timeline.10.1') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.11') }}</h2>
			<p>{{ trans('about.timeline.11.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2016.avif" type="image/avif">
				<img src="/img/startpage_2016.png" alt="MetaGer 2016" style="width:auto;">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.12') }}</h2>
			<p>{{ trans('about.timeline.12.1') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.13') }}</h2>
			<p>{{ trans('about.timeline.13.1') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.14') }}</h2>
			<p>{{ trans('about.timeline.14.1') }}</p>
		</div>
		<div>
			<h2>{{ trans('about.timeline.15') }}</h2>
			<p>{{ trans('about.timeline.15.1') }}</p>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.16') }}</h2>
			<p>{{ trans('about.timeline.16.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2019.avif" type="image/avif">
				<img src="/img/startpage_2019.png" alt="MetaGer 2019">
			</picture>
		</div>
		<div class="timeline-item-alternate">
			<h2>{{ trans('about.timeline.17') }}</h2>
			<p>{!! trans('about.timeline.17.1') !!}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/startpage_2020.avif" type="image/avif">
				<img src="/img/startpage_2020.png" alt="MetaGer 2020">
			</picture>
		</div>
		<div class="timeline-item-alternate" >
			<h2>{{ trans('about.timeline.18') }}</h2>
			<p>{{ trans('about.timeline.18.1') }}</p>
			<picture>
				<source media="(max-width:465px)" srcset="/img/help-page_2022.avif" type="image/avif">
				<img src="/img/help-page_2022.png" alt="MetaGer Hilfe Seite">
			</picture>
		</div>
		<div>
			<h2>{{ trans('about.timeline.19') }}</h2>
			<p>{{ trans('about.timeline.19.1') }}</p>
		</div>
	</div>
</div>

@endsection