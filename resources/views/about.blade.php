@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div class="timeline-container">
	<div>
		<h2>{{ trans('about.timeline.1') }}</h2>
		<p>{{ trans('about.timeline.1.1') }}</p>
	</div>
	<div>
		<h2>{{ trans('about.timeline.2') }}</h2>
		<p>{{ trans('about.timeline.2.1') }}</p>

	</div>
	<div>
		<h2>{{ trans('about.timeline.3') }}</h2>
		<p>{!! trans('about.timeline.3.1') !!}</p>
	</div>
	<div class="timeline-item-alternate">
		<h2>{{ trans('about.timeline.4') }}</h2>
		<p>{{ trans('about.timeline.4.1') }}</p>
		<picture>
			<img src="/img/startpage_2015.png" alt="MetaGer 2015" style="width:auto;">
		  </picture> 
	</div>
	<div>
		<h2>{{ trans('about.timeline.5') }}</h2>
		<p>{{ trans('about.timeline.5.1') }}</p>
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
	<div>
		<h2>{{ trans('about.timeline.9') }}</h2>
		<p>{{ trans('about.timeline.9.1') }}</p>
	</div>
	<div>
		<h2>{{ trans('about.timeline.10') }}</h2>
		<p>{{ trans('about.timeline.10.1') }}</p>
	</div>
	<div>
		<h2>{{ trans('about.timeline.11') }}</h2>
		<p>{{ trans('about.timeline.11.1') }}</p>
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


</div>
@endsection
