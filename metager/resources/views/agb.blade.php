@extends('layouts.subPages', ['page' => 'agb'])

@section('title', $title)

@section('content')
{{--
	Die AGB für die Token-Aufladung — Vertragstext.

	Lag als /keys/agb im Keymanager, dessen Bezahlvorgänge weiter hierher
	verlinken (templates/agb.ejs und templates/revocation.ejs). Der Text wurde
	beim Umzug nicht angefasst; Tests\Feature\AgbTextUnchangedTest vergleicht
	die gerenderte deutsche Fassung mit einem Abzug von vor dem Umzug.

	Die Anker sind der eigentliche Grund, warum dieses Blade mehr tut, als eine
	Liste auszugeben: der Checkout im anderen Repository verlinkt einzelne
	Klauseln, und auf der alten Seite gab es die verlinkten ids überhaupt
	nicht — /keys/agb#refund landete am Seitenanfang.

	Die ids stehen hier und nicht in lang/, weil ein Anker in allen Sprachen
	derselbe sein muss. Die Zuordnung ist über die Position, und
	AgbAnchorsTest prüft, dass Liste und Vertragstext gleich viele Abschnitte
	haben — sonst verschieben sie sich stillschweigend, sobald jemand einen
	Abschnitt einfügt.
--}}
@php
	$sectionIds = [
		"anbieter",
		"vertragsschluss",
		"gewaehrleistung",
		"schluessel",
		"token",
		"haftung",
		"schlussbestimmungen",
	];

	// Absatzanker, die von außen verlinkt werden: [Abschnitt, Absatz] => id.
	// „Rückerstattung“ ist die freiwillige 30-Tage-Rückgabe, auf die
	// templates/revocation.ejs im Keymanager zeigt.
	$paragraphIds = [
		"5.2" => "rueckerstattung",
	];

	$vars = [
		// Der Vertragstext nennt seine eigene Fundstelle. Dort stand wörtlich
		// „metager.de/keys/agb“; ein Vertrag, der auf eine Weiterleitung zeigt,
		// ist die einzige Stelle, die beim Umzug angepasst werden musste.
		"agburl" => "metager.de/agb",
		"linkGerman" => LaravelLocalization::getLocalizedURL("de-DE", "/agb"),
	];

	$sections = trans("agb.paragraphs");
@endphp
<div id="agb">
	@if(App\Localization::getLanguage() !== "de")
		<div class="alert alert-warning" role="note">
			{!! __('agb.translationNotice', $vars) !!}
		</div>
	@endif

	<h1 class="page-title">@lang('agb.heading')</h1>

	@foreach($sections as $s => $section)
		<section id="{{ $sectionIds[$s] ?? 'abschnitt-' . ($s + 1) }}">
			<h2>{{ $section['heading'] }}</h2>
			<ol>
				@foreach($section['paragraphs'] as $p => $paragraph)
					@if(is_array($paragraph))
						{{-- Eine Aufzählung innerhalb einer Klausel, z. B. die
						     Kaufoptionen. Sie gehört zum Absatz davor und
						     bekommt darum keine eigene Nummer — aber ein <ol>
						     darf nur <li> enthalten, also steht sie in einem
						     ungezählten Listenpunkt. --}}
						<li class="agb-sublist-item">
							<ul class="agb-sublist">
								@foreach($paragraph as $q => $item)
									<li>{{ __("agb.paragraphs.$s.paragraphs.$p.$q", $vars) }}</li>
								@endforeach
							</ul>
						</li>
					@else
						<li @isset($paragraphIds["$s.$p"]) id="{{ $paragraphIds["$s.$p"] }}" @endisset>
							{{-- Der Kontaktblock in Abschnitt 1 ist mehrzeilig und
							     muss es bleiben; nl2br statt <pre>, damit er wie
							     Fließtext umbricht statt seitlich zu scrollen. --}}
							{!! nl2br(e(__("agb.paragraphs.$s.paragraphs.$p", $vars))) !!}
						</li>
					@endif
				@endforeach
			</ol>
		</section>
	@endforeach

	<p class="agb-date">@lang('agb.date')</p>
</div>
@endsection
