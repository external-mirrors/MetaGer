<div class="result" data-count="{{ $result->hash }}" data-index="{{$index}}">
	<div class="result-header">
		<div class="result-headline">
			<h2 class="result-title" title="{{ $result->titel }}">
				@if( isset($result->price) && $result->price != 0)
				<span class="result-price">{!! $result->price_text !!}</span>
				@endif
				<a href="{{ $result->link }}" target="{{ $metager->getNewtab() }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif>
					{!! $result->titel !!}
				</a>
			</h2>
			@if(sizeof($result->gefVon)===1)
			<a class="result-hoster" href="{{ $result->gefVonLink[0] }}" target="{{ $metager->getNewtab() }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif tabindex="-1">{{ trans('result.gefVon') . " " . $result->gefVon[0] }} </a>
			@else
			<span title="{{ (implode(', ', $result->gefVon)) }}" class="result-hoster" tabindex="0">
				{{ trans('result.gefVon') . " " . sizeof($result->gefVon) . " " . trans('result.providers') }}
				<ul class="card">
					@foreach($result->gefVon as $index => $gefVon)
					<li><a class="result-hoster" href="{{ $result->gefVonLink[$index] }}" target="{{ $metager->getNewtab() }}" rel="noopener" tabindex="-1">{{ trans('result.gefVon') . " " . $result->gefVon[$index] }} </a></li>
					@endforeach
				</ul>
			</span>

			@endif
		</div>
		<div class="result-subheadline">
			<a class=" result-link" href="{{ $result->link }}" title="{{ $result->anzeigeLink }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif target="{{ $metager->getNewtab() }}" tabindex="-1">
				{{ $result->anzeigeLink }}
			</a>
			@if( isset($result->partnershop) && $result->partnershop === TRUE)
			<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/partnershops") }}" target="_blank" class="badge" rel="noopener">
				<div>{!! trans('result.options.4') !!}</div>
			</a>
			@endif
		</div>
		@if(sizeof($result->deepResults["buttons"]) > 0)
		<ul class="result-deep-buttons">
		@foreach($result->deepResults["buttons"] as $button)
			<li>
				<a href="{{ $button->link }}"  title="{{ $button->link }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif target="{{ $metager->getNewtab() }}">
					{{ $button->title }}
				</a>
			</li>
		@endforeach
		</ul>
		@endif
	</div>
	<div class="result-body {{ (!empty($result->logo) || !empty($result->image) ? "with-image" : "")}}">
		@if( $metager->getFokus() == "nachrichten" )
		<div class="result-description">
			<span class="date">{{ isset($result->additionalInformation["date"])?date("Y-m-d H:i:s", $result->additionalInformation["date"]):"" }}</span> {{ $result->descr }}
		</div>
		@else
		<div class="result-description">
			{{ $result->descr }}
		</div>
		@endif
		@if( isset($result->logo) )
		<div class="result-logo">
			<a href="{{ $result->link }}" @if($metager->isFramed())target="_top"@endif>
				<img src="{{ \App\Http\Controllers\Pictureproxy::generateUrl($result->logo) }}" alt="" />
			</a>
		</div>
		@endif
		@if( $result->image !== "" )
		<div class="result-image">
			<a href="{{ $result->link }}" @if($metager->isFramed())target="_top"@endif>
				<img src="{{ \App\Http\Controllers\Pictureproxy::generateUrl($result->image) }}" alt="" />
			</a>
		</div>
		@endif
		<input type="checkbox" id="result-toggle-{{$result->hash}}" class="result-toggle">
		<div class="result-footer">
			@if($metager->getNewtab() === "_blank")
			<a class="result-open" href="{{ $result->link }}" @if($metager->isFramed())target="_top"@else target="_self"@endif>
				{!! trans('result.options.7') !!}
			</a>
			@else
			<a class="result-open-newtab" href="{{ $result->link }}" target="_blank" rel="noopener">
				{!! trans('result.options.6') !!}
			</a>
			@endif
			@if( isset($result->partnershop) && $result->partnershop === TRUE)
			<a class="result-open-key" title="@lang('result.metagerkeytext')" href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" target="_blank">
				@lang('result.options.8')
			</a>
			@else
			<a class="result-open-proxy" title="@lang('result.proxytext')" href="{{ $result->proxyLink }}" target="{{ $metager->getNewtab() }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif>
				{!! trans('result.options.5') !!}
			</a>
			@endif
			<label class="open-result-options navigation-element" for="result-toggle-{{$result->hash}}" tabindex='0'>
				<img src="/img/ellipsis.svg" alt="settings" height="100%" />
			</label>
			<label class="close-result-options navigation-element" for="result-toggle-{{$result->hash}}" tabindex='0'>
				<img src="/img/ellipsis.svg" alt="settings" height="100%" />
			</label>
		</div>
		<div class="result-options">
			<div class="options">
				<ul class="option-list list-unstyled small">
					@if(strlen($metager->getSite()) === 0)
					<li>
						<a href="{{ $metager->generateSiteSearchLink($result->strippedHost) }}" @if($metager->isFramed())target="_top"@else target="_self"@endif>
							{!! trans('result.options.1') !!}
						</a>
					</li>
					@endif
					<li>
						<a href="{{ $metager->generateRemovedHostLink($result->strippedHost) }}" @if($metager->isFramed())target="_top"@else target="_self"@endif>
							{!! trans('result.options.2', ['host' => $result->strippedHost]) !!}
						</a>
					</li>
					@if( $result->strippedHost !== $result->strippedDomain )
					<li>
						<a href="{{ $metager->generateRemovedDomainLink($result->strippedDomain) }}" @if($metager->isFramed())target="_top"@else target="_self"@endif>
							{!! trans('result.options.3', ['domain' => $result->strippedDomain]) !!}
						</a>
					</li>
					@endif
				</ul>
			</div>
		</div>
	</div>
	@if(sizeof($result->inheritedResults) > 0)
	<div class="result-inherited-results">
		@foreach($result->inheritedResults as $inheritedResult)
		<div class="inherited-result">
			<a class="inherited-title" href="{{ $inheritedResult->link }}" title="{{ $inheritedResult->link }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif target="{{ $metager->getNewtab() }}">{{ $inheritedResult->titel }}</a>
			<div class="inherited-description">{{ $inheritedResult->descr }}</div>
		</div>
		@endforeach
	</div>
	@endif
</div>
<div class="ab-hint result" data-target="{{$index}}">@lang('result.adblocker', ['resultlink' => $result->originalLink, 'infolink' => route('adblocker'), 'host' => $result->host])</div>
