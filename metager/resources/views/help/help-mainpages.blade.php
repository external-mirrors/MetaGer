@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-mainpages.title.1') !!}</h1>

<section>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-mainpages.backarrow') !!}</a>
	</div>
	<p>{!! trans('help/help-mainpages.easy-help') !!}</p>
	<section id="h-startpage" class="card">
		<h2>{!! trans('help/help-mainpages.title.2') !!}</h2>
		<h3>{!! trans('help/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/help-mainpages.startpage.info') !!}</p>
		<h3 id="h-searchfield">{!! trans('help/help-mainpages.searchfield.title') !!}</h3>
			<p>{!! trans('help/help-mainpages.searchfield.info') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-mainpages.searchfield.memberkey') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.slot') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.search') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.morefunctions') !!}</li>
			</ul>
	</section>
	<section id="h-resultpage" class="card">
		<h3>{!! trans('help/help-mainpages.resultpage.title') !!}</h3>
			<ul class="dotlist">
				<li>{!! trans('help/help-mainpages.resultpage.foci') !!}</li>
				<li>{!! trans('help/help-mainpages.resultpage.choice') !!}</li>
				<ul class="dotlist">
					<li>{!! trans('help/help-mainpages.resultpage.filter') !!}</li>
					<li>{!! trans('help/help-mainpages.resultpage.settings') !!}</li>
				</ul>
			</ul>
		<h3>{!! trans('help/help-mainpages.result.title') !!}</h3>
			<p>{!! trans('help/help-mainpages.result.info.1') !!}</p>
			@if (App\Localization::getLanguage() == "de")
			<div class="image-container">
				<img class="image-container lm-only" src="/img/help-resultpic-01-lm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
				<img class=" image-container dm-only" src="/img/help-resultpic-01-dm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
			</div>
				@else
				<div class="image-container">
				<img class="image-container lm-only" src="/img/help-result-en-lm-01.png" alt="Screenshot of a result"/>
				<img class="image-container dm-only" src="/img/help-result-en-dm-01.png" alt="Screenshot of a result"/>
			</div>
				@endif
			<ul class = "dotlist">
				<li>{!! trans('help/help-mainpages.result.info.open') !!}</li>
				<li>{!! trans('help/help-mainpages.result.info.newtab') !!}</li>
				<li>{!! trans('help/help-mainpages.result.info.anonym') !!}</li>
				<li>{!! trans('help/help-mainpages.result.info.more') !!}</li>
			</ul>
			@if (App\Localization::getLanguage() == "de")
			<div class="image-container">
				<img class="lm-only" src="/img/help-resultpic-02-lm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
				<img class="dm-only" src="/img/help-resultpic-02-dm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
			</div>
				@else
			<div class="image-container">
				<img class="lm-only" src="/img/help-result-en-lm-02.png" alt="Screenshot of a result"/>
				<img class="dm-only" src="/img/help-result-en-dm-02.png" alt="Screenshot of a result"/>
			</div>
				@endif
			<p>{!! trans('help/help-mainpages.result.info.2') !!}</p>
			<ul class = "dotlist">
				<li>{!! trans('help/help-mainpages.result.info.domainnewsearch') !!}</li>
				<li>{!! trans('help/help-mainpages.result.info.hideresult') !!}</li>
			</ul>
	</section>
	<section id="h-settings" class="card">
		<h3>{!! trans('help/help-mainpages.settings.title') !!}</h3>
		<ul>
			<li>{!! trans('help/help-mainpages.settings.1') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.2') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.3') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.4') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.9') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.5') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.6') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.7') !!}</li>
			<li>{!! trans('help/help-mainpages.settings.8') !!}</li>
	</section>
</section>



@endsection