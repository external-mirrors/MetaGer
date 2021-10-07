@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('hilfe.title') !!}</h1>

<section id="startpage">
	<h1>{!! trans('hilfe.title.2') !!}</h1>
	<h2>{!! trans('hilfe.startpage.title') !!}</h2>
	<p>{!! trans('hilfe.startpage.info') !!}</p>
	<h2>{!! trans('hilfe.searchfield.title') !!}</h2>
	<div>
		<p>{!! trans('hilfe.searchfield.info') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.searchfield.memberkey') !!}</li>
			<li>{!! trans('hilfe.searchfield.slot') !!}</li>
			<li>{!! trans('hilfe.searchfield.search') !!}</li>
		</ul>
	</div>

	<h2>{!! trans('hilfe.resultpage.title') !!}</h2>
	<div>
		<ul class="dotlist">
			<li>{!! trans('hilfe.resultpage.foci') !!}</li>
			<li>{!! trans('hilfe.resultpage.choice') !!}</li>
			<ul class="dotlist">
				<li>{!! trans('hilfe.resultpage.filter') !!}</li>
				<li id="difset">{!! trans('hilfe.resultpage.settings.0') !!}</li>
				<ol>
					<li>{!! trans('hilfe.resultpage.settings.1') !!}</li>
					<li>{!! trans('hilfe.resultpage.settings.2') !!}</li>
					<li>{!! trans('hilfe.resultpage.settings.3') !!}</li>
					<li>{!! trans('hilfe.resultpage.settings.4') !!}</li>
				</ol>
			</ul>
		</ul>
	</div>
</section>
<section id="results">
	<h1>{!! trans('hilfe.result.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.result.info.1') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.result.info.open') !!}</li>
			<li>{!! trans('hilfe.result.info.newtab') !!}</li>
			<li>{!! trans('hilfe.result.info.anonym') !!}</li>
			<li>{!! trans('hilfe.result.info.more') !!}</li>
		</ul>
		<p>{!! trans('hilfe.result.info.2') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.result.info.saveresult') !!}</li>
			<li>{!! trans('hilfe.result.info.domainnewsearch') !!}</li>
			<li>{!! trans('hilfe.result.info.hideresult') !!}</li>
		</ul>
	</div>
</section>
<section>
	<h1>{!! trans('hilfe.stopworte.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.stopworte.1') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.stopworte.2') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.stopworte.3') !!}</div>
			</li>
		</ul>
	</div>
</section>
<section id="severalwords">
	<h1>{!! trans('hilfe.mehrwortsuche.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.mehrwortsuche.1') !!}</p>
		<p>{!! trans('hilfe.mehrwortsuche.2') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.mehrwortsuche.3') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.mehrwortsuche.3.example') !!}</div>
			</li>
			<li>{!! trans('hilfe.mehrwortsuche.4') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.mehrwortsuche.4.example') !!}</div>
			</li>
		</ul>
	</div>
</section>

<section id="capitalizationrules">
	<h1>{!! trans('hilfe.grossklein.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.grossklein.1') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.grossklein.2') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.grossklein.2.example') !!}</div>
			</li>
			<li class="nodot">{!! trans('hilfe.grossklein.3') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.grossklein.3.example') !!}</div>
			</li>
		</ul>
	</div>
</section>
<section id="urls">
	<h1>{!! trans('hilfe.urls.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.urls.explanation') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('hilfe.urls.example.1') !!}</li>
			<li class="nodot">
				<div class="search-example">{!! trans('hilfe.urls.example.2') !!}</div>
			</li>
		</ul>
	</div>
</section>
<section id="bangs">
	<h1>{!! trans('hilfe.bang.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.bang.1') !!}</p>
	</div>
</section>
<section id="searchinsearch">
	<h1>{!! trans('hilfe.searchinsearch.title') !!}</h1>
	<div>
		<p>{!! trans('hilfe.searchinsearch.1') !!}</p>
	</div>
</section>
<h1 id="dienste">{!! trans('hilfe.dienste') !!}</h1>
<h2><img class="mg-icon" src="/img/angle-double-right.svg" alt="{{ trans('angle-double-right.alt') }}" aria-hidden="true"> {!! trans('hilfe.dienste.kostenlos') !!}</h2>
<section id="app">
	<div id="mg-app" style="margin-top: -100px"></div>
	<div style="margin-top: 100px"></div>
	<h3>{!! trans('hilfe.app.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.app.1') !!}</p>
	</div>
</section>
<section id="plugin">
	<h3>{!! trans('hilfe.plugin.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.plugin.1') !!}</p>
	</div>
</section>
<section id="torhidden">
	<h3>{!! trans('hilfe.tor.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.tor.1') !!}</p>
		<p>{!! trans('hilfe.tor.2') !!}</p>
	</div>
</section>
<section id="proxy">
	<h3>{!! trans('hilfe.proxy.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.proxy.1') !!}</p>
	</div>
</section>

<section id="maps">
	<h3>{!! trans('hilfe.maps.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.maps.1') !!}</p>
		<p>{!! trans('hilfe.maps.2') !!}</p>
		<p>{!! trans('hilfe.maps.3') !!}</p>
	</div>
</section id="faq">
<section id="asso">
	<h3>{!! trans('hilfe.suchwortassoziator.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.suchwortassoziator.1') !!}</p>
		<p>{!! trans('hilfe.suchwortassoziator.2') !!}</p>
		<p>{!! trans('hilfe.suchwortassoziator.3') !!}</p>
	</div>
</section>
<section id="widget">
	<h3>{!! trans('hilfe.widget.title') !!}</h3>
	<div>
		<p>{!! trans('hilfe.widget.1') !!}</p>
	</div>
</section>
<h1>{!! trans('hilfe.datenschutz.title') !!}</h1>
<section id="factcheck">
	<h2>{!! trans('hilfe.datenschutz.faktencheck.heading') !!}</h2>
	<div>
		<p>@lang('hilfe.datenschutz.faktencheck.body.1')</p>
		<p>@lang('hilfe.datenschutz.faktencheck.body.2')</p>
	</div>
</section>
<section id="tracking">
	<h2>{!! trans('hilfe.datenschutz.1') !!}</h2>
	<div>
		<p>{!! trans('hilfe.datenschutz.2') !!}</p>
		<p>{!! trans('hilfe.datenschutz.3') !!}</p>
	</div>
</section>

<h1>{!! trans('hilfe.faq.title') !!}</h1>
<section>
	<h2>{!! trans('hilfe.metager.title') !!}</h2>
	<p>{!! trans('hilfe.metager.explanation.1') !!}</p>
	<p>{!! trans('hilfe.metager.explanation.2') !!}</p>
</section>
<section>
	<h2>{!! trans('hilfe.searchengine.title') !!}</h2>
	<p>{!! trans('hilfe.searchengine.explanation') !!}</p>
</section>
<section>
	<h2>{!! trans('hilfe.content.title') !!}</h2>
	<p>{!! trans('hilfe.content.explanation.1') !!}</p>
	<p>{!! trans('hilfe.content.explanation.2') !!}</p>
</section>
<section>
	<h2>{!! trans('hilfe.selist.title') !!}</h2>
	<p>{!! trans('hilfe.selist.explanation.1') !!}</p>
	<p>{!! trans('hilfe.selist.explanation.2') !!}</p>
</section>
<section>
	<h2>{!! trans('hilfe.proposal.title') !!}</h2>
	<p>{!! trans('hilfe.proposal.explanation') !!}</p>
</section>
<section>
	<h2>{!! trans('hilfe.assignment.title') !!}</h2>
	<p>{!! trans('hilfe.assignment.explanation.1') !!}</p>
	<p>{!! trans('hilfe.assignment.explanation.2') !!}</p>
</section>
<div class="alert alert-warning" role="alert">{!! trans('hilfe.achtung') !!}</div>
@endsection