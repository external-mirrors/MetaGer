<div id="options">
	<div id="toggle-box">
		<div id="settings">
			<a id="settings-link" href="{{ route('settings', ["focus" => $metager->getFokus(), "url" => $metager->generateSearchLink($metager->getFokus())]) }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4"/></svg>
				@lang('metaGer.settings.name')&hellip;
				@if($metager->getSavedSettingCount() > 0)<span class="settings-count">{{ $metager->getSavedSettingCount() }}</span>@endif
			</a>
		</div>
		<div id="filter-toggle">
			@if(sizeof(app(\App\SearchSettings::class)->parameterFilter) > 0)
			<div class="option-toggle">
				<label class="navigation-element" for="options-toggle">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>Filter&hellip;
				</label>
			</div>
			@endif
			@if(app(\App\SearchSettings::class)->isTemporaryParameterFilterSet())
			<div id="options-reset">
				<a href="{{$metager->generateSearchLink($metager->getFokus())}}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif><nobr>{{ trans('metaGer.filter.reset') }}</nobr></a>
			</div>
			@endif
		</div>
		@if($metager->getTotalResultCount() > 0)
		<div id="result-count">
			<nobr>~ {{$metager->getTotalResultCount()}}</nobr> {{ trans('metaGer.results.name') }}
		</div>
		@endif
	</div>
	@if(app(\App\SearchSettings::class)->parameterFilter > 0)
	<input type="checkbox" id="options-toggle" @if(app(\App\SearchSettings::class)->isParameterFilterSet())checked @endif />
	<div class="scrollbox">
		<div id="options-box">
			<div id="options-items">
			@foreach(app(\App\SearchSettings::class)->parameterFilter as $filterName => $filter)
				@if(empty($filter->hidden) || $filter->hidden === false)
				<div class="option-selector">
					<div>
						{{-- Prefixed: "color", "language" and "sort" are too plain to be
						     the only element on the page with that id. --}}
						<label for="filter-{{$filterName}}">
							@lang($filter->name)
						</label>
					@if($filterName === "freshness")
						<label for="custom-date" title="@lang('metaGer.filter.customdatetitle')">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
						</label>
					</div>
						<input id="custom-date" type="checkbox" form="searchForm" 
							@if(Request::input('fc', "off") === "on")checked @endif 
							@if(array_key_exists("customfreshness", app(\App\SearchSettings::class)->parameterFilter) && sizeof(app(\App\SearchSettings::class)->parameterFilter["customfreshness"]->{"disabled-values"}) > 0)
							disabled
							@endif
							name="fc"/>
					@else
					</div>
					@endif
					<select id="filter-{{$filterName}}" name="{{$filter->{'get-parameter'} }}" class="custom-select custom-select-sm" form="searchForm">
					@foreach($filter->values as $value => $text)
					@if($value === "nofilter" && Cookie::get($metager->getFokus() . "_setting_" . $filter->{"get-parameter"}) !== null)
					<option value="off" @if(empty($filter->value) || $filter->value === "off")selected @endif>{{trans($text)}}</option>
					@else
					<option 
						value="@if($value !== 'nofilter'){{$value}} @endif"
						@if(!empty($filter->value) && $filter->value === $value ||
                            (empty($filter->value) && $filter->{"default-value"} === $value))
						selected
						@endif
						@if(array_key_exists($value, $filter->{"disabled-values"}) && sizeof($filter->{"disabled-values"}[$value]) > 0)
						disabled
						@endif
					>{{trans($text)}}</option>
					@endif
					@endforeach
				</select>
				@if(!empty($filter->htmlbelow))
					@include($filter->htmlbelow)
				@endif
				</div>
				@endif
			@endforeach
			</div>
		</div>
	</div>
	@endif
</div>
