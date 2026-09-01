<fieldset>
	<form id="searchForm" method={{ $request }} @if(!empty($metager) && $metager->isFramed())target="_top" @endif action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/meta/meta.ger3") }}" accept-charset="UTF-8">
		<div class="searchbar {{$class ?? ''}}">
			<div class="search-input-submit">
				<div id="suggest-exit">&larr;</div>
				<div class="search-input @if(!\Request::is('/')) search-delete-js-only @endif">
					<input type="search" id="eingabe" name="eingabe" value="@if(Request::filled("eingabe")){{Request::input("eingabe")}}@endif" @if(\Request::is('/') && !\Request::filled('mgapp')) autofocus @endif autocomplete="off" class="form-control" placeholder="{{ trans('index.placeholder') }}">
					<button id="search-delete-btn" name="delete-search-input" type="reset" title="@lang('index.searchreset')">
						&#xd7;
					</button>
				</div>
				<div class="search-submit" id="submit-inputgroup">
					<button type="submit" title="@lang('index.searchbutton')" aria-label="@lang('index.searchbutton')">
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
				{{--
					This form is method="GET": submitting it replaces the
					query string on `action` outright, it does not merge
					with it — unlike a route() link, which App\Http\SettingsCarry
					already gets folded into. Without these, a cookie-blind
					visitor's settings would silently reset the moment they
					actually searched. SettingsCarry excludes `key` itself
					(handled above) and CookieSupport::MARKER, so there is no
					collision with the input just above.
				--}}
				@foreach (app(\App\Http\SettingsCarry::class)->all() as $carryName => $carryValue)
				<input type="hidden" name="{{ $carryName }}" value="{{ $carryValue }}" form="searchForm">
				@endforeach
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
