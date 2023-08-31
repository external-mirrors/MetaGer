@if(isset($ad)  && !app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
	<div class="result" 
	@if(array_key_exists('ad_data', $ad->additionalInformation))
	data-yiid="{{ $ad->additionalInformation['ad_data']['yiid'] }}" 
	@if($ad->additionalInformation['ad_data']['appns'] !== null && $ad->additionalInformation['ad_data']['k'] !== null) 
	data-appns="{{ $ad->additionalInformation['ad_data']['appns'] }}" 
	data-k="{{ $ad->additionalInformation['ad_data']['k'] }}" 
	@endif
	@endif>
		<div class="result-header">
			<div class="result-headline">
				<h2 class="result-title">
					<a href="{{ $ad->link }}" target="_blank" referrerpolicy="no-referrer-when-downgrade">
						{{ $ad->titel }}
					</a>
				</h2>
				<a class="result-hoster" href="{{ $ad->gefVonLink[0] }}" target="{{ $metager->getNewtab() }}" rel="noopener" referrerpolicy="no-referrer-when-downgrade" tabindex="-1">{{ trans('result.gefVon') . " " . $ad->gefVon[0] }} </a>
			</div>
			<div class="result-subheadline">
				<a class="result-link" href="{{ $ad->link }}" target="_blank" referrerpolicy="no-referrer-when-downgrade" tabindex="-1">
					<span>{{ $ad->anzeigeLink }}</span>
					@if(\App\Localization::getLanguage() === "de")
					<img src="/img/100-de.svg" alt="Mark">
					@else
					<img src="/img/100-en.svg" alt="Mark">
					@endif
				</a>
			</div>
		</div>
		<div class="result-body">
			<div class="result-description">
				{{ $ad->descr }}
			</div>
		</div>
		<div class="result-footer">
		<a class="result-open-newtab" href="{{ $ad->link }}" target="_blank" rel="noopener" referrerpolicy="no-referrer-when-downgrade">
			{!! trans('result.options.6') !!}
		</a>
		<a class="result-open-key" title="@lang('result.metagerkeytext')" href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" target="_blank">
			@lang('result.options.8')
		</a>
	</div>
	</div>
@endif
