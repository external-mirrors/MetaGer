@extends('layouts.subPages', ['page' => 'firmen'])

@section('title', $title)

@section('content')
{{--
	MetaGer für Firmen und Organisationen.

	Das Ziel jedes B2B-Hinweises auf der Seite (Seitenleiste, /preise, /konto,
	Beitrittsformular) und der einzige Ort, an dem steht, was eine Mitgliedschaft
	für eine Organisation bedeutet. Der Weg von hier führt in denselben Zweig des
	Beitrittsformulars, den es schon gab — `?type=company` —, nur dass ihn
	vorher niemand gefunden hat, weil er nur als Link in einer Überschrift
	existierte.

	Die Beträge kommen aus App\Support\MembershipFee und nicht aus lang/, damit
	Seite und Formular nicht auseinanderlaufen können: dieselbe Klasse
	entscheidet, was die Validierung annimmt.

	Was hier bewusst *nicht* steht, ist eine fertige Konfigurationsdatei zum
	Herunterladen — ein OpenSearch-Deskriptor oder eine policies.json trüge den
	Schlüssel im Klartext, und genau das ist die Zusage dieser Seite: der
	Schlüssel bleibt bei der Organisation. Solange das nicht anders gelöst ist,
	ist die Einrichtung ein Gespräch mit der IT und kein Download.
--}}
<div id="business-page">
	<h1 class="page-title">@lang('business.title')</h1>
	<p class="business-intro">@lang('business.intro')</p>

	<div class="business-actions">
		<a class="account-btn account-btn--primary" href="{{ $linkJoin }}">@lang('business.actions.join')</a>
		<a class="account-btn account-btn--quiet" href="{{ $linkContact }}">@lang('business.actions.contact')</a>
	</div>

	<h2>@lang('business.benefits.heading')</h2>
	<ul class="business-benefits">
		@foreach(trans('business.benefits.items') as $index => $benefit)
			<li>
				<h3>{{ $benefit['heading'] }}</h3>
				<div>{!! __("business.benefits.items.$index.text", ['linkprivacy' => $linkPrivacy]) !!}</div>
			</li>
		@endforeach
	</ul>

	<h2 id="einrichtung">@lang('business.setup.heading')</h2>
	<ol class="business-setup">
		@foreach(trans('business.setup.steps') as $step)
			<li>
				<h3>{{ $step['heading'] }}</h3>
				<div>{{ $step['text'] }}</div>
			</li>
		@endforeach
	</ol>

	<div class="business-hint" id="business-key">
		<h3>@lang('business.setup.hint.heading')</h3>
		<p>@lang('business.setup.hint.text')</p>
	</div>

	<h2 id="beitrag">@lang('business.fee.heading')</h2>
	<p>{!! __('business.fee.text', ['linkfeeorder' => $linkFeeOrder]) !!}</p>

	<table class="business-fee">
		<thead>
			<tr>
				<th scope="col">@lang('business.fee.columns.employees')</th>
				<th scope="col">@lang('business.fee.columns.amount')</th>
			</tr>
		</thead>
		<tbody>
			@foreach($brackets as $employees => $minimum)
				<tr>
					<th scope="row">{{ __("business.fee.brackets.$employees") }}</th>
					<td>{{ number_format($minimum, 0, ",", ".") }}&nbsp;@lang('business.fee.unit')</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<p>{!! __('business.fee.credit', ['linkprice' => $linkPrice]) !!}</p>
	<p>@lang('business.fee.charity')</p>

	<h2 id="bildung">@lang('business.education.heading')</h2>
	<p>@lang('business.education.text')</p>
	<p>@lang('business.education.cta')</p>

	<div class="business-actions">
		<a class="account-btn account-btn--primary" href="{{ $linkJoin }}">@lang('business.actions.join')</a>
		<a class="account-btn account-btn--quiet" href="{{ $linkContact }}">@lang('business.actions.contact')</a>
	</div>
</div>
@endsection
