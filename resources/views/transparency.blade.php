@extends('layouts.subPages')

@section('title', $title )

@section('content')
<style>
.card-heavy {
	border-color: #f47216;
	border-width: 2px;
	border-radius: 7px;
	margin-bottom: 30px!important;
}
{{ trans('about.head.4') }}
</style>
	<div id="about">
		<h1 class="page-title">{{ trans('transparency.head.1') }}</h1>
		<div class="card-heavy">
			<h2>MetaGer ist transparent</h2>
			<p> MetaGer ist transparent ... Quellcode frei einlesbar ... </p>
		</div>
		<div class="card-heavy">
			<h2>Was ist eine Meta-Suchmaschine überhaupt?</h2>
			<p> Eine Meta-Suchmaschine ist eine Suchmaschine die mehrere andere Suchmaschinen gleichzeitig nutzt. Sie sammelt alle Ergebnisse der verschiedenen Suchmaschinen und wertet diese erneut nach eigenem Schema. Das heißt, dass die Metasuchmaschine keinen eigenen Index hat, also keinen eigenen Datenbestand.</p>
		</div>
		<div class="card-heavy">
			<h2>Was ist der Vorteil einer Meta-Suchmaschine?</h2>
			<p>Ein klarer Vorteil von Meta-Suchmaschinen ist es, dass der Nutzer nur eine einzige Suchanfrage braucht um an die Ergebnisse mehrerer Suchmaschinen zu kommen. Die Meta-Suchmaschine gibt die relevanten Ergebnisse in einer nochmals sortierten Ergebnisliste aus.</p>
		</div>
		<div class="card-heavy">
		<h2>Wie setzt sich unser Ranking zusammen? </h2>
			<p>Wir übernehmen das Ranking unserer Quell-Suchmaschinen und gewichten diese. Diese Bewertungen werden dann in Punktzahlen umgewandelt.
Außerdem wird das Vorkommen der Suchbegriffe in der URL und im Snippet, sowie das übermäßige Vorkommen von Besonderer Zeichen (andere Schriftzeichen wie kyrillisch) …

Wir verwenden zudem noch eine Sperrliste, um einzelne Seiten von der Ergebnisliste rauszunehmen. Hier werden Anzeigen  gesperrt, die wir aus juristischer Sicht nicht anzeigen dürfen, sowie Ergebnisse, dessen Qualität so schlecht ist, dass diese keinen Nutzen für die Allgemeinheit haben oder dieser sogar schaden könnte.</p>
		</div>
		<div class="card-heavy">
		<p>Sollte es weitere Fragen oder Unklarheiten geben, können Sie gerne unser Kontaktformular nutzen und uns Ihre Fragen stellen!</p>
		</div>
	</div>
@endsection
