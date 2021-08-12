@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section id="results">
		<h3>{!! trans('help/help-results.result.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-results.result.info.1') !!}</p>
			<ul class = "dotlist">
				<li>{!! trans('help/help-results.result.info.open') !!}</li>
				<li>{!! trans('help/help-results.result.info.newtab') !!}</li>
				<li>{!! trans('help/help-results.result.info.anonym') !!}</li>
				<li>{!! trans('help/help-results.result.info.more') !!}</li>
			</ul>
			<p>{!! trans('help/help-results.result.info.2') !!}</p>
			<ul class = "dotlist">
				<li>{!! trans('help/help-results.result.info.saveresult') !!}</li>
				<li>{!! trans('help/help-results.result.info.domainnewsearch') !!}</li>
				<li>{!! trans('help/help-results.result.info.hideresult') !!}</li>
			</ul>
		</div>
	</section>

@endsection
