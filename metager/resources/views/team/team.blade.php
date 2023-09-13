@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="team">
	<h1 class="page-title">Team</h1>
	<div class="card">
		<ul class="dotlist">
			<li>
				<p>Hebeler, Dominik - {!! trans('team.role.0') !!}
			</li>
			<li>
				<p>Riel, Carsten - {!! trans('team.role.1.0') !!} & {!! trans('team.role.7') !!}
			</li>
			<li>
				<p>Branz, Manuela - {!! trans('team.role.1.1') !!} & {!! trans('team.role.3') !!}
			</li>
			<li>
				<p>Höfer, Phil - {!! trans('team.role.5') !!}
			</li>
			<li>
				<p>Höfer, Kim - {!! trans('team.role.6') !!}
			</li>
			<li>
				<p><a href="https://de.wikipedia.org/wiki/Wolfgang_Sander-Beuermann" target="_blank" rel="noopener">Sander-Beuermann, Wolfgang</a>, Dr.-Ing - {!! trans('team.role.8') !!}
			</li>
			<li>
				<p>{!! trans('team.role.2') !!}: Branz, Manuela
			</li>



		</ul>
	</div>
	<div class="card">
		<p>@lang('team.contact.1', ['link_contact' => route('contact')])</p>
		<p>{!! trans('team.contact.2') !!}</p>
		<p>{!! trans('team.contact.3') !!}</p>
	</div>
</div>
@endsection