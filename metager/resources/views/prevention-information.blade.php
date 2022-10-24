@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
<h1 class="page-title">{{ trans('prevention-information.head.1') }}</h1>
</div>
<div class="card">
	<h2>{{ trans('prevention-information.head.2') }}</h2>
	<p>{{ trans('prevention-information.text.1') }}</p>

	<h2>{{ trans('prevention-information.europe') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium') }}">🇧🇪</a>
		<a class="country-button" href="#germany" title="{{ trans('prevention-information.germany') }}">🇩🇪</a>
		<a class="country-button" href="#denmark" title="{{ trans('prevention-information.denmark') }}">🇩🇰</a>
		<a class="country-button" href="#france" title="{{ trans('prevention-information.france') }}">🇫🇷</a>
		<a class="country-button" href="#greece" title="{{ trans('prevention-information.greece') }}">🇬🇷</a>
		<a class="country-button" href="#italy" title="{{ trans('prevention-information.italy') }}">🇮🇹</a>
		<a class="country-button" href="#lativa" title="{{ trans('prevention-information.latvia') }}">🇱🇻</a>
		<a class="country-button" href="#lithuania" title="{{ trans('prevention-information.lithuania') }}">🇱🇹</a>
		<a class="country-button" href="#luxembourg" title="{{ trans('prevention-information.luxembourg') }}">🇱🇺</a>
		<a class="country-button" href="#netherlands" title="{{ trans('prevention-information.netherlands') }}">🇳🇱</a>
		<a class="country-button" href="#norway" title="{{ trans('prevention-information.norway') }}">🇳🇴</a>
		<a class="country-button" href="#austria" title="{{ trans('prevention-information.austria') }}">🇦🇹</a>
		<a class="country-button" href="#poland" title="{{ trans('prevention-information.poland') }}">🇵🇱</a>
		<a class="country-button" href="#portugal" title="{{ trans('prevention-information.portugal') }}">🇵🇹</a>
		<a class="country-button" href="#czech" title="{{ trans('prevention-information.czech') }}">🇨🇿</a>
		<a class="country-button" href="#russia" title="{{ trans('prevention-information.russia') }}">🇷🇺</a>
		<a class="country-button" href="#sweden" title="{{ trans('prevention-information.sweden') }}">🇸🇪</a>
		<a class="country-button" href="#switzerland" title="{{ trans('prevention-information.switzerland') }}">🇨🇭</a>
		<a class="country-button" href="#serbia" title="{{ trans('prevention-information.serbia') }}">🇷🇸</a>
		<a class="country-button" href="#spain" title="{{ trans('prevention-information.spain') }}">🇪🇸</a>
		<a class="country-button" href="#ukraine" title="{{ trans('prevention-information.ukraine') }}">🇺🇦</a>
		<a class="country-button" href="#hungary" title="{{ trans('prevention-information.hungary') }}">🇭🇺</a>
		<a class="country-button" href="#uk" title="{{ trans('prevention-information.uk') }}">🇬🇧</a>

	</div>
	<h2>{{ trans('prevention-information.america') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#costa-rica" title="{{ trans('prevention-information.costa.rica') }}">🇨🇷</a>
		<a class="country-button" href="#canada" title="{{ trans('prevention-information.canada') }}">🇨🇦</a>
		<a class="country-button" href="#mexico" title="{{ trans('prevention-information.mexico') }}">🇲🇽</a>
		<a class="country-button" href="#usa" title="{{ trans('prevention-information.usa') }}">🇺🇸</a>
		<a class="country-button" href="#argentina" title="{{ trans('prevention-information.argentina') }}">🇦🇷</a>
		<a class="country-button" href="#brazil" title="{{ trans('prevention-information.brazil') }}">🇧🇷</a>
		<a class="country-button" href="#chile" title="{{ trans('prevention-information.chile') }}">🇨🇱</a>
	</div>


	<h2>{{ trans('prevention-information.asia') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium') }}">🇧🇪</a>
	</div>

	<h2>{{ trans('prevention-information.africa') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium') }}">🇧🇪</a>
	</div>

	<h2>{{ trans('prevention-information.australia') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium') }}">🇧🇪</a>
	</div>


	<h2>{!! trans('prevention-information.search.helpline') !!}</h2>
	<p>{!! trans('prevention-information.search.helpline.1') !!}</p>
	
</div>
<div class="card">
	<h1 id="europe">{{ trans('prevention-information.europe') }}</h1>

	<h2 id="belgium">{{ trans('prevention-information.belgium') }}</h2>
	<p>{!! trans('prevention-information.belgium.1') !!}</p>

	<h2 id="germany">{{ trans('prevention-information.germany') }}</h2>
	<p>{!! trans('prevention-information.germany.1') !!}</p>

	<h2 id="denmark">{{ trans('prevention-information.denmark') }}</h2>
	<p>{!! trans('prevention-information.denmark.1') !!}</p>

	<h2 id="france">{{ trans('prevention-information.france') }}</h2>
	<p>{!! trans('prevention-information.france.1') !!}</p>

	<h2 id="greece">{{ trans('prevention-information.greece') }}</h2>
	<p>{!! trans('prevention-information.greece.1') !!}</p>

	<h2 id="italy"> {{ trans('prevention-information.italy') }}</h2>
	<p>{!! trans('prevention-information.italy.1') !!}</p>

	<h2 id="latvia">{{ trans('prevention-information.latvia') }}</h2>
	<p>{!! trans('prevention-information.latvia.1') !!}</p>

	<h2 id="lithuania">{{ trans('prevention-information.lithuania') }}</h2>
	<p>{!! trans('prevention-information.lithuania.1') !!}</p>

	<h2 id="luxembourg">{{ trans('prevention-information.luxembourg') }}</h2>
	<p>{!! trans('prevention-information.luxembourg.1') !!}</p>

	<h2 id="netherlands">{{ trans('prevention-information.netherlands') }}</h2>
	<p>{!! trans('prevention-information.netherlands.1') !!}</p>

	<h2 id="norway">{{ trans('prevention-information.norway') }}</h2>
	<p>{!! trans('prevention-information.norway.1') !!}</p>

	<h2 id="austria">{{ trans('prevention-information.austria') }}</h2>
	<p>{!! trans('prevention-information.austria.1') !!}</p>

	<h2 id="poland">{{ trans('prevention-information.poland') }}</h2>
	<p>{!! trans('prevention-information.poland.1') !!}</p>

	<h2 id="portugal">{{ trans('prevention-information.portugal') }}</h2>
	<p>{!! trans('prevention-information.portugal.1') !!}</p>

	<h2 id="czech">{{ trans('prevention-information.czech') }}</h2>
	<p>{!! trans('prevention-information.czech.1') !!}</p>

	<h2 id="russia">{{ trans('prevention-information.russia') }}</h2>
	<p>{!! trans('prevention-information.russia.1') !!}</p>

	<h2 id="serbia">{{ trans('prevention-information.serbia') }}</h2>
	<p>{!! trans('prevention-information.serbia.1') !!}</p>

	<h2 id="spain">{{ trans('prevention-information.spain') }}</h2>
	<p>{!! trans('prevention-information.spain.1') !!}</p>

	<h2 id="sweden">{{ trans('prevention-information.sweden') }}</h2>
	<p>{!! trans('prevention-information.sweden.1') !!}</p>

	<h2 id="switzerland">{{ trans('prevention-information.switzerland') }}</h2>
	<p>{!! trans('prevention-information.switzerland.1') !!}</p>

	<h2 id="ukraine">{{ trans('prevention-information.ukraine') }}</h2>
	<p>{!! trans('prevention-information.ukraine.1') !!}</p>

	<h2 id="hungary">{{ trans('prevention-information.hungary') }}</h2>
	<p>{!! trans('prevention-information.hungary.1') !!}</p>

	<h2 id="uk">{{ trans('prevention-information.uk') }}</h2>
	<p>{!! trans('prevention-information.uk.1') !!}</p>

</div>
<div class="card">
	<h1 id="north-america">{{ trans('prevention-information.america') }}</h1>

	<h2 id="costa-rica">{{ trans('prevention-information.costa.rica') }}</h2>
	<p>{!! trans('prevention-information.costa.rica.1') !!}</p>

	<h2 id="canada">{{ trans('prevention-information.canada') }}</h2>
	<p>{!! trans('prevention-information.canada.1') !!}</p>

	<h2 id="mexico">{{ trans('prevention-information.mexico') }}</h2>
	<p>{!! trans('prevention-information.mexico.1') !!}</p>

	<h2 id="usa">{{ trans('prevention-information.usa') }}</h2>
	<p>{!! trans('prevention-information.usa.1') !!}</p>
	
	<h2 id="argentina">{{ trans('prevention-information.argentina') }}</h2>
	<p>{!! trans('prevention-information.argentina.1') !!}</p>

	<h2 id="brazil">{{ trans('prevention-information.brazil') }}</h2>
	<p>{!! trans('prevention-information.brazil.1') !!}</p>

	<h2 id="chile">{{ trans('prevention-information.chile') }}</h2>
	<p>{!! trans('prevention-information.chile.1') !!}</p>

</div>	
@endsection