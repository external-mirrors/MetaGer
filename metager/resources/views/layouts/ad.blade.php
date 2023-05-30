@if(isset($ad)  && !app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
	<div class="result">
		<div class="result-header">
			<div class="result-headline">
				<h2 class="result-title">
					<a href="{{ $ad->link }}" target="{{ $metager->getNewtab() }}" referrerpolicy="no-referrer-when-downgrade">
						{{ $ad->titel }}
					</a>
				</h2>
				<a class="result-hoster" href="{{ $ad->gefVonLink[0] }}" target="{{ $metager->getNewtab() }}" rel="noopener" referrerpolicy="no-referrer-when-downgrade" tabindex="-1">{{ trans('result.gefVon') . " " . $ad->gefVon[0] }} </a>
			</div>
			<div class="result-subheadline">
				<a class="result-link" href="{{ $ad->link }}" target="{{ $metager->getNewtab() }}" referrerpolicy="no-referrer-when-downgrade" tabindex="-1">
					<span class="mark">@lang('result.advertisement')</span>
					<span>{{ $ad->anzeigeLink }}</span>
				</a>
			</div>
		</div>
		<div class="result-body">
			<div class="result-description">
				{{ $ad->descr }}
			</div>
		</div>
		<div class="result-footer">
		<a class="result-open" href="{{ $ad->link }}" target="_self" rel="noopener" referrerpolicy="no-referrer-when-downgrade">
			{!! trans('result.options.7') !!}
		</a>
		<a class="result-open-newtab" href="{{ $ad->link }}" target="_blank" rel="noopener" referrerpolicy="no-referrer-when-downgrade">
			{!! trans('result.options.6') !!}
		</a>
		<a class="result-open-metagerkey" title="@lang('result.metagerkeytext')" href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" target="_blank">
			@lang('result.options.8')
		</a>
	</div>
	</div>
@endif
