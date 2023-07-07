<div id="options">
	<div id="toggle-box">
		<div id="settings">
			<a href="{{ route('settings', ["focus" => $metager->getFokus(), "url" => $metager->generateSearchLink($metager->getFokus())]) }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
			<img src="/img/icon-settings.svg"alt="" aria-hidden="true"id="result-img-settings">
				@if($metager->getSavedSettingCount() > 0) <span class="badge badge-primary"></span>{{ $metager->getSavedSettingCount() }}@endif
				@lang('metaGer.settings.name')&hellip;
			</a>
		</div>
		<div id="filter-toggle">
			@if(sizeof(app(\App\SearchSettings::class)->parameterFilter) > 0)
			<div class="option-toggle">
				<label class="navigation-element" for="options-toggle" tabindex="0">
				<img src="/img/icon-filter.svg"alt="" aria-hidden="true"id="result-img-filter"> Filter&hellip;
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
	@if(!app(App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
	<p class="metager-key-hint">@lang('metaGer.settings.metager-key-hint', ["link" => app(App\Models\Authorization\Authorization::class)->getAdfreeLink()])</p>
	@endif
		<div id="options-box">
			<div id="options-items">
			@foreach(app(\App\SearchSettings::class)->parameterFilter as $filterName => $filter)
				@if(empty($filter->hidden) || $filter->hidden === false)
				<div class="option-selector">
					<div>
						<label for="{{$filterName}}">
							@lang($filter->name)
						</label>
					@if($filterName === "freshness")
						<label for="custom-date" title="@lang('metaGer.filter.customdatetitle')">
						<img src="/img/icon-settings.svg"alt="" aria-hidden="true"id="result-img-settings">
						</label>
					</div>
						<input id="custom-date" type="checkbox" form="searchForm" 
							@if(Request::input('fc', "off") === "on")checked @endif 
							@if(sizeof(app(\App\SearchSettings::class)->parameterFilter["customfreshness"]->{"disabled-values"}) > 0)
							disabled
							@endif
							name="fc"/>
					@else
					</div>
					@endif
					<select name="{{$filter->{'get-parameter'} }}" class="custom-select custom-select-sm" form="searchForm">
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
