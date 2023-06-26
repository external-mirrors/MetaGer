<fieldset>
	<form id="searchForm" method={{ $request }} @if(!empty($metager) && $metager->isFramed())target="_top" @endif action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/meta/meta.ger3 ") }}" accept-charset="UTF-8">
		<div class="searchbar {{$class ?? ''}}">
			<div class="search-input-submit">
				<div id="search-key">
					<a id="key-link" @if(app('App\Models\Authorization\Authorization')->canDoAuthenticatedSearch())class="authorized" @else class="unauthorized"@endif href="{{ LaravelLocalization::getLocalizedURL(null, "/keys/key/enter") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif 
						data-tooltip="{{ app('App\Models\Authorization\Authorization')->getKeyTooltip() }}" tabindex="0">
						<img 
							src="{{ app('App\Models\Authorization\Authorization')->getKeyIcon() }}"
							alt="" aria-hidden="true" id="searchbar-img-key"
						>
					</a>
				</div>
				<div class="search-input @if(!\Request::is('/')) search-delete-js-only @endif">
					<input type="search" name="eingabe" value="@if(Request::filled("eingabe")){{Request::input("eingabe")}}@endif" @if(\Request::is('/') && !\Request::filled('mgapp')) autofocus @endif autocomplete="off" class="form-control" placeholder="{{ trans('index.placeholder') }}" >
					<button id="search-delete-btn" name="delete-search-input" type="reset" tabindex="-1">
						&#xd7;
					</button>
				</div>
				<div class="search-submit" id="submit-inputgroup">
					<button type="submit" tabindex="-1" title="@lang('index.searchbutton')" aria-label="@lang('index.searchbutton')">
						<img src="/img/icon-lupe.svg" alt="" aria-hidden="true" id="searchbar-img-lupe">
					</button>
				</div>
			</div>
			<div class="search-hidden">
				@if(Request::filled("token"))
				<input type="hidden" name="token" value={{ Request::input("token") }}>
				@endif
				@if (isset($option_values))
				@foreach($option_values as $option => $value)
				<input type="hidden" name={{ $option }} value={{ $value }}>
				@endforeach
				@endif
				@if (isset($focus) && !empty($focus))
				<input type="hidden" name="focus" value={{ $focus }}>
				@endif
			</div>
			<div class="search-custom-hidden"></div>
		</div>
	</form>
</fieldset>