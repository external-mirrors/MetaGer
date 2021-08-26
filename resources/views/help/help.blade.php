@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')

<h2>{!! trans('help/help.tableofcontents.1') !!}</h2>
<div class="help-topic-row">
	<a href="/hilfe/hauptseiten" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.1') !!}</p>
	</a>
	<a href="/hilfe/hauptseiten#suchfeld" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.2') !!}</p>
	</a>
	<a href="/hilfe/hauptseiten#ergebnis" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.3') !!}</p>
	</a>
	<a href="/hilfe/hauptseiten#einstellungen" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.4') !!}</p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.2') !!}</h2>
<div class="help-topic-row">
	<a href="/hilfe/funktionen" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.1') !!}</p>
	</a>
	<a href="/hilfe/funktionen#severalwords" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.2') !!}</p>
	</a>
	<a href="/hilfe/funktionen#capitalizationrules" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.3') !!}<br></p>
	</a>
	<a href="/hilfe/funktionen#urls" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.4') !!}</p>
	</a>
	<a href="/hilfe/funktionen#bangs" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.5') !!}<br></p>
	</a>
	<a href="/hilfe/funktionen#searchinsearch" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.6') !!}<br></p>
	</a>
	<a href="/hilfe/funktionen#selist" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.7') !!}<br></p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.3') !!}</h2>
	<div class="help-topic-row">
		<a href="/hilfe/datensicherheit" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.1') !!}<br></p>
		</a>
		<a href="/hilfe/datensicherheit#tracking" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.2') !!}</p>
		</a>
		<a href="/hilfe/datensicherheit#torhidden" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.3') !!}<br></p>
		</a>
		<a href="/hilfe/datensicherheit#proxy" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.4') !!}<br></p>
		</a>
		<a href="/hilfe/datensicherheit#content" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.5') !!}<br></p>
		</a>
	</div>
		
<h2>{!! trans('help/help.tableofcontents.4') !!}</h2>

	<div class="help-topic-row">
		<a href="/hilfe/dienste" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.1') !!}<br></p>
		</a>
		<a href="/hilfe/dienste#plugin" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.2') !!}<br></p>
		</a>
		<a href="/hilfe/dienste#asso" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.3') !!}<br></p>
		</a>
		<a href="/hilfe/dienste#widget" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.4') !!}<br></p>
		</a>		
		<a href="/hilfe/dienste#maps" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.5') !!}<br></p>
		</a>
		
	</div>


<h2>{!! trans('help/help.faq.title') !!}</h2>
	<section>
		<h3>{!! trans('help/help.metager.title') !!}</h3>
		<p>{!! trans('help/help.metager.explanation.1') !!}</p>
		<p>{!! trans('help/help.metager.explanation.2') !!}</p>
	</section>
	<section>
		<h3>{!! trans('help/help.searchengine.title') !!}</h3>
		<p>{!! trans('help/help.searchengine.explanation') !!}</p>
	</section>
	<section>
		<h3>{!! trans('help/help.content.title') !!}</h3>
		<p>{!! trans('help/help.content.explanation.1') !!}</p>
		<p>{!! trans('help/help.content.explanation.2') !!}</p>
	</section>
	<section>
		<h3>{!! trans('help/help.selist.title') !!}</h3>
		<p>{!! trans('help/help.selist.explanation.1') !!}</p>
		<p>{!! trans('help/help.selist.explanation.2') !!}</p>
	</section>
	<section>
		<h3>{!! trans('help/help.proposal.title') !!}</h3>
		<p>{!! trans('help/help.proposal.explanation') !!}</p>
	</section>
	<section>
		<h3>{!! trans('help/help.assignment.title') !!}</h3>
		<p>{!! trans('help/help.assignment.explanation.1') !!}</p>
		<p>{!! trans('help/help.assignment.explanation.2') !!}</p>
	</section>

@endsection