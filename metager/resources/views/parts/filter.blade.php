	<div id="options">
		<div id="toggle-box">
			<div id="settings">
				<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $metager->getFokus(), "url" => $metager->generateSearchLink($metager->getFokus())])) }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
				<img src="/img/icon-settings.svg"alt="" aria-hidden="true"id="result-img-settings">
					@if($metager->getSavedSettingCount() > 0) <span class="badge badge-primary"></span>{{ $metager->getSavedSettingCount() }}@endif
					@lang('metaGer.settings')&hellip;
				</a>
			</div>
			<div id="filter-toggle">
				@if(sizeof($metager->getAvailableParameterFilter()) > 0)
				<div class="option-toggle">
					<label class="navigation-element" for="options-toggle" tabindex="0">
					<img src="/img/icon-filter.svg"alt="" aria-hidden="true"id="result-img-filter"> Filter&hellip;
					</label>
				</div>
				@endif
				@if($metager->getManualParameterFilterSet())
				<div id="options-reset">
					<a href="{{$metager->generateSearchLink($metager->getFokus())}}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif><nobr>{{ trans('metaGer.filter.reset') }}</nobr></a>
				</div>
				@endif
			</div>
			@if($metager->getTotalResultCount() > 0)
			<div id="result-count">
				<nobr>~ {{$metager->getTotalResultCount()}}</nobr> {{ trans('metaGer.results') }}
			</div>
			@endif
		</div>
		@if(sizeof($metager->getAvailableParameterFilter()) > 0)
		<input type="checkbox" id="options-toggle" @if(sizeof($metager->getParameterFilter()) > 0)checked @endif />
		<div class="scrollbox">
			<div id="options-box">
				<div id="options-items">
				@foreach($metager->getAvailableParameterFilter() as $filterName => $filter)
					@if(empty($filter->hidden) || $filter->hidden === false)
					<div class="option-selector">
						<div>
							<label for="{{$filterName}}">
								@lang($filter->name)
							</label>
						@if($filter->{'get-parameter'} === "f")
							<label for="custom-date" title="@lang('metaGer.filter.customdatetitle')">
							<img src="/img/icon-settings.svg"alt="" aria-hidden="true"id="result-img-settings">
							</label>
						</div>
							<input id="custom-date" type="checkbox" form="searchForm" @if(Request::input('fc', "off") === "on")checked @endif name="fc" onchange="if(!this.checked){this.form.submit()}"/>
						@else
						</div>
						@endif
						<select name="{{$filter->{'get-parameter'} }}" class="custom-select custom-select-sm" form="searchForm" onchange="this.form.submit()">
						@foreach($filter->values as $value => $text)
						@if($value === "nofilter" && Cookie::get($metager->getFokus() . "_setting_" . $filter->{"get-parameter"}) !== null)
						<option value="off" @if(empty($filter->value) || $filter->value === "off")selected @endif>{{trans($text)}}</option>
						@elseif($value === "nofilter")
						<option value="" @if(!empty($filter->value) && $filter->value === $value)selected @endif>{{trans($text)}}</option>
						@else
						<option value="{{$value}}" @if(!empty($filter->value) && $filter->value === $value)selected @endif>{{trans($text)}}</option>
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
			<div class="scrollfade-right"></div>
		</div>
	@endif
	</div>
