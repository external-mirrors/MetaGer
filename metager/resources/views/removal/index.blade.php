@extends('layouts.subPages')

@section('title', $title)

@section('content')
{{--
	/entfernen — where a search result can actually be removed.

	Providers, anchors and form URLs come from config/removal_providers.php.
	The anchors are public: suma-crm links to /entfernen#<anchor>, so an id
	here is not just markup. The page links no form of ours: SUMA-EV's own
	searches are removed from at their sources too (the last step).

	The page is complete without a script: the request kinds are <details>, the
	table of contents is plain anchors. resources/js/removal.js only adds hints,
	and pairs a kind with its row on the cards through data-removal-kind.
--}}
<div id="removal">
	<h1 class="page-title">@lang('removal.heading')</h1>
	<p class="removal-lead">@lang('removal.lead')</p>

	<div class="removal-layout">
	<nav class="removal-toc" aria-labelledby="removal-toc-label">
		<span id="removal-toc-label" class="removal-toc-label">@lang('removal.toc.label')</span>
		<ol>
			<li><a href="#{{ $website['anchor'] }}">@lang('removal.website.heading')</a></li>
			<li><a href="#kind">@lang('removal.kind.heading')</a></li>
			<li><a href="#providers">@lang('removal.providers.heading')</a></li>
			<li><a href="#own-searches">@lang('removal.own.heading')</a></li>
		</ol>
	</nav>

	<ol class="removal-steps">
		<li class="removal-step" id="{{ $website['anchor'] }}">
			<h2>@lang('removal.website.heading')</h2>
			<p>@lang('removal.website.text')</p>
			<ul class="removal-tips">
				@foreach(trans('removal.website.tips') as $tip)
					<li>{{ $tip }}</li>
				@endforeach
			</ul>
			<p class="removal-sources">@lang('removal.website.sources', ['sources' => $website['name']]) {{ __($website['note']) }}</p>
		</li>

		<li class="removal-step" id="kind">
			<h2>@lang('removal.kind.heading')</h2>
			<p>@lang('removal.kind.intro')</p>
			<div class="removal-kinds">
				@foreach(['personal_data', 'outdated', 'legal'] as $kind)
					<details class="removal-kind" id="kind-{{ str_replace('_', '-', $kind) }}" data-removal-kind="{{ $kind }}" @if($loop->first) open @endif>
						<summary>@lang("removal.kind.$kind.summary")</summary>
						<p>@lang("removal.kind.$kind.text")</p>
					</details>
				@endforeach
				<details class="removal-kind removal-kind-illegal" id="illegal">
					<summary>@lang('removal.kind.illegal.summary')</summary>
					<p>@lang('removal.kind.illegal.text')</p>
					<p>@lang('removal.kind.illegal.hotlines')</p>
					<ul>
						@foreach($hotlines as $hotline)
							<li><a href="{{ $hotline['url'] }}" rel="noopener noreferrer">{{ $hotline['name'] }}</a></li>
						@endforeach
					</ul>
				</details>
			</div>
		</li>

		<li class="removal-step" id="providers">
			<h2>@lang('removal.providers.heading')</h2>
			<p>@lang('removal.providers.text')</p>
			<p>@lang('removal.providers.which')</p>
			<div class="removal-providers">
				@foreach($providers as $id => $provider)
					<section class="removal-provider" id="{{ $provider['anchor'] }}" aria-labelledby="{{ $provider['anchor'] }}-name">
						<h3 id="{{ $provider['anchor'] }}-name">{{ $provider['name'] }}</h3>
						@if(!empty($foki[$id]))
							<p class="removal-provider-foki">@lang('removal.providers.shows') {{ implode(', ', $foki[$id]) }}</p>
						@endif
						<p>{{ __($provider['note']) }}</p>
						@if($provider['type'] === 'metager')
							<p class="removal-provider-links"><a href="{{ $tootnews }}" rel="noopener noreferrer">@lang('removal.providers.servers')</a></p>
						@elseif(empty(array_filter($provider['links'])))
							{{-- No form for anything: one contact line, not three identical ones. --}}
							<p class="removal-provider-links"><a href="{{ $provider['contact'] }}" rel="noopener noreferrer">@lang('removal.providers.no_form', ['name' => $provider['name']])</a></p>
						@else
							<dl class="removal-provider-links">
								@foreach($kinds as $kind)
									<div data-removal-kind="{{ $kind }}">
										<dt>@lang("removal.providers.kinds.$kind")</dt>
										<dd>
											@if(!empty($provider['links'][$kind]))
												<a href="{{ $provider['links'][$kind] }}" rel="noopener noreferrer">{{ parse_url($provider['links'][$kind], PHP_URL_HOST) }}</a>
											@elseif(!empty($provider['contact']))
												<a href="{{ $provider['contact'] }}" rel="noopener noreferrer">@lang('removal.providers.no_form', ['name' => $provider['name']])</a>
											@else
												<a href="#{{ $website['anchor'] }}">@lang('removal.website.heading')</a>
											@endif
										</dd>
									</div>
								@endforeach
							</dl>
						@endif
					</section>
				@endforeach
			</div>
		</li>

		<li class="removal-step" id="own-searches">
			<h2>@lang('removal.own.heading')</h2>
			<p>@lang('removal.own.text')</p>
			<div class="removal-providers">
				@foreach($own as $search)
					<section class="removal-provider" id="{{ $search['anchor'] }}" aria-labelledby="{{ $search['anchor'] }}-name">
						<h3 id="{{ $search['anchor'] }}-name">{{ $search['name'] }}</h3>
						<p class="removal-provider-foki"><a href="{{ $search['url'] }}" rel="noopener noreferrer">{{ parse_url($search['url'], PHP_URL_HOST) }}</a></p>
						<p>{{ __($search['note']) }}</p>
						<p class="removal-provider-links"><a href="{{ $search['removal'] }}" rel="noopener noreferrer">{{ parse_url($search['removal'], PHP_URL_HOST) }}</a></p>
					</section>
				@endforeach
				<section class="removal-provider" id="own-tootnews" aria-labelledby="own-tootnews-name">
					<h3 id="own-tootnews-name">TootNews / TrötNews</h3>
					<p>@lang('removal.own.tootnews')</p>
					<p class="removal-provider-links"><a href="{{ $tootnews }}" rel="noopener noreferrer">@lang('removal.providers.servers')</a></p>
				</section>
			</div>
		</li>
	</ol>
	</div>
</div>
@endsection
