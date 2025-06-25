<fieldset>
	<form id="searchForm" method={{ $request }}
		action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/meta/meta.ger3 ") }}"
		accept-charset="UTF-8">
		<div class="searchbar {{$class ?? ''}}">
			<div class="search-input-submit">
				<div id="search-key">
					<a id="key-link"
						@if(app('App\Models\Authorization\Authorization')->canDoAuthenticatedSearch(false))class="authorized"
						@else class="unauthorized"@endif
						href="{{ LaravelLocalization::getLocalizedURL(null, "/keys/key/enter") }}"
						data-tooltip="{{ app('App\Models\Authorization\Authorization')->getKeyTooltip() }}"
						tabindex="0">
						<img src="{{ app('App\Models\Authorization\Authorization')->getKeyIcon() }}" alt=""
							aria-hidden="true" id="searchbar-img-key">
					</a>
				</div>
				<div id="suggest-exit">&larr;</div>
				<div class="search-input @if(!\Request::is('/')) search-delete-js-only @endif">
					<input type="search" id="eingabe" name="eingabe"
						value="@if(Request::filled("eingabe")){{Request::input("eingabe")}}@endif" @if(\Request::is('/') && !\Request::filled('mgapp')) autofocus @endif autocomplete="off" class="form-control"
							placeholder="{{ trans('index.placeholder') }}" @if(Route::currentRouteName()==="assistant"
						)disabled @endif>
					<button id="search-delete-btn" name="delete-search-input" type="reset"
						title="@lang('index.searchreset')">
						&#xd7;
					</button>
				</div>
				<div class="search-submit" id="submit-inputgroup">
					<button type="submit" title="@lang('index.searchbutton')" aria-label="@lang('index.searchbutton')"
						@if(Route::currentRouteName()==="assistant")disabled @endif>
						<img src="/img/icon-lupe.svg" alt="" aria-hidden="true" id="searchbar-img-lupe">
					</button>
				</div>
			</div>
			<div class="suggestions" data-suggestions="{{ route('suggest') }}">
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
				<div class="suggestion" tabindex="0">
					<a href=""><img src="/img/icon-lupe.svg" alt="search"></a>
					<span></span>
				</div>
			</div>
			<div class="search-hidden">
				@if(Request::filled("token"))
					<input type="hidden" name="token" value={{ Request::input("token") }}>
				@endif
				@if(Request::filled('key'))
					<input type="hidden" name="key" value="{{ Request::input('key', '') }}" form="searchForm">
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
@if(config("metager.metager.admitad.suggestions_enabled") && app(\App\SearchSettings::class)->suggestions !== "off")
	<script src="{{ mix('/js/suggest.js') }}"></script>
@endif