@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<div id="team">
		<h1 class="page-title">Team</h1>
		<div class="card-heavy">
			<ul class="dotlist">
				<li>
					<p>Hebeler, Dominik - {!! trans('team.role.0') !!} -
					<a href="mailto:dominik@suma-ev.de">dominik@suma-ev.de</a></p>
				</li>
				<li>
					<p>Riel, Carsten - {!! trans('team.role.1') !!} & {!! trans('team.role.7') !!} -
					<a href="carsten@suma-ev.de">carsten@suma-ev.de</a></p>
				</li>
				<li>
					<p>Branz, Manuela - {!! trans('team.role.3') !!} & {!! trans('team.role.2') !!} -
					<a href="mailto:manuela.branz@suma-ev.de">manuela.branz@suma-ev.de</a></p>
				</li>
				<li>
					<p>Höfer, Phil - {!! trans('team.role.5') !!} -
					<a href="mailto:phil@suma-ev.de">phil@suma-ev.de</a></p>
				</li>
				<li>
					<p>Höfer, Kim - {!! trans('team.role.6') !!} -
					<a href="mailto:kim@suma-ev.de">kim@suma-ev.de</a></p>
				</li>
				<li>
					<p><a href="https://de.wikipedia.org/wiki/Wolfgang_Sander-Beuermann" target="_blank" rel="noopener">Sander-Beuermann, Wolfgang</a>, Dr.-Ing - {!! trans('team.role.8') !!} -
					<a href="mailto:wsb@suma-ev.de">wsb@suma-ev.de</a>
				</li>
			</ul>
		</div>
		<div class="card-heavy">
			<p>{!! trans('team.contact.1') !!}</p>
			<p>{!! trans('team.contact.2') !!}</p>
			<p>{!! trans('team.contact.3') !!}</p>
		</div>
	</div>
@endsection
