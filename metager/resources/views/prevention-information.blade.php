@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
<h1 class="page-title">{{ trans('prevention-information.head.1') }}</h1>
</div>
<div class="card">
	<h2>{{ trans('prevention-information.head.2') }}</h2>
	<p>{{ trans('prevention-information.text.1') }}</p>


    @if (App\Localization::getLanguage() == "de")
        
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
		<a class="country-button" href="#russia-europe" title="{{ trans('prevention-information.russia') }}">🇷🇺</a>
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
		<a class="country-button" href="#china" title="{{ trans('prevention-information.china') }}">🇨🇳</a>
		<a class="country-button" href="#hongkong" title="{{ trans('prevention-information.hongkong') }}">🇭🇰</a>
		<a class="country-button" href="#india" title="{{ trans('prevention-information.india') }}">🇮🇳</a>
		<a class="country-button" href="#iran" title="{{ trans('prevention-information.iran') }}">🇮🇷</a>
		<a class="country-button" href="#israel" title="{{ trans('prevention-information.israel') }}">🇮🇱</a>
		<a class="country-button" href="#japan" title="{{ trans('prevention-information.japan') }}">🇯🇵</a>
		<a class="country-button" href="#pakistan" title="{{ trans('prevention-information.pakistan') }}">🇵🇰</a>
		<a class="country-button" href="#philippines" title="{{ trans('prevention-information.philippines') }}">🇵🇭</a>
		<a class="country-button" href="#russia-asia" title="{{ trans('prevention-information.russia') }}">🇷🇺</a>
		<a class="country-button" href="#singapore" title="{{ trans('prevention-information.singapore') }}">🇸🇬</a>
		<a class="country-button" href="#south-korea" title="{{ trans('prevention-information.south.korea') }}">🇰🇷</a>
		<a class="country-button" href="#taiwan" title="{{ trans('prevention-information.taiwan') }}">🇹🇼</a>


	</div>

	<h2>{{ trans('prevention-information.africa') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#south-africa" title="{{ trans('prevention-information.south.africa') }}">🇿🇦</a>
		<a class="country-button" href="#nigeria" title="{{ trans('prevention-information.nigeria') }}">🇳🇬</a>
		<a class="country-button" href="#kenya" title="{{ trans('prevention-information.kenya') }}">🇰🇪</a>
	</div>

	<h2>{{ trans('prevention-information.australia.continent') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#australia" title="{{ trans('prevention-information.australia') }}">🇦🇺</a>
		<a class="country-button" href="#new-zealand" title="{{ trans('prevention-information.new.zealand') }}">🇳🇿</a>

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

	<h2 id="russia-europe">{{ trans('prevention-information.russia') }}</h2>
	<p>{!! trans('prevention-information.russia.1') !!}</p>

	<h2 id="sweden">{{ trans('prevention-information.sweden') }}</h2>
	<p>{!! trans('prevention-information.sweden.1') !!}</p>

	<h2 id="switzerland">{{ trans('prevention-information.switzerland') }}</h2>
	<p>{!! trans('prevention-information.switzerland.1') !!}</p>

	<h2 id="serbia">{{ trans('prevention-information.serbia') }}</h2>
	<p>{!! trans('prevention-information.serbia.1') !!}</p>

	<h2 id="spain">{{ trans('prevention-information.spain') }}</h2>
	<p>{!! trans('prevention-information.spain.1') !!}</p>

	<h2 id="ukraine">{{ trans('prevention-information.ukraine') }}</h2>
	<p>{!! trans('prevention-information.ukraine.1') !!}</p>

	<h2 id="hungary">{{ trans('prevention-information.hungary') }}</h2>
	<p>{!! trans('prevention-information.hungary.1') !!}</p>

	<h2 id="uk">{{ trans('prevention-information.uk') }}</h2>
	<p>{!! trans('prevention-information.uk.1') !!}</p>

</div>
<div class="card">
	<h1 id="america">{{ trans('prevention-information.america') }}</h1>

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
<div class="card">
	<h1 id="asia">{{ trans('prevention-information.asia') }}</h1>	

	<h2 id="china">{{ trans('prevention-information.china') }}</h2>
	<p>{!! trans('prevention-information.china.1') !!}</p>

	<h2 id="hongkong">{{ trans('prevention-information.hongkong') }}</h2>
	<p>{!! trans('prevention-information.hongkong.1') !!}</p>

	<h2 id="india">{{ trans('prevention-information.india') }}</h2>
	<p>{!! trans('prevention-information.india.1') !!}</p>

	<h2 id="iran">{{ trans('prevention-information.iran') }}</h2>
	<p>{!! trans('prevention-information.iran.1') !!}</p>

	<h2 id="israel">{{ trans('prevention-information.israel') }}</h2>
	<p>{!! trans('prevention-information.israel.1') !!}</p>

	<h2 id="japan">{{ trans('prevention-information.japan') }}</h2>
	<p>{!! trans('prevention-information.japan.1') !!}</p>

	<h2 id="pakistan">{{ trans('prevention-information.pakistan') }}</h2>
	<p>{!! trans('prevention-information.pakistan.1') !!}</p>

	<h2 id="philippines">{{ trans('prevention-information.philippines') }}</h2>
	<p>{!! trans('prevention-information.philippines.1') !!}</p>

	<h2 id="russia-asia">{{ trans('prevention-information.russia') }}</h2>
	<p>{!! trans('prevention-information.russia.1') !!}</p>

	<h2 id="singapore">{{ trans('prevention-information.singapore') }}</h2>
	<p>{!! trans('prevention-information.singapore.1') !!}</p>

	<h2 id="south-korea">{{ trans('prevention-information.south.korea') }}</h2>
	<p>{!! trans('prevention-information.south.korea.1') !!}</p>

	<h2 id="taiwan">{{ trans('prevention-information.taiwan') }}</h2>
	<p>{!! trans('prevention-information.taiwan.1') !!}</p>
</div>

<div class="card">
	<h1 id="africa">{{ trans('prevention-information.africa') }}</h1>

	<h2 id="south-africa">{{ trans('prevention-information.south.africa') }}</h2>
	<p>{!! trans('prevention-information.south.africa.1') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.nigeria') }}</h2>
	<p>{!! trans('prevention-information.nigeria.1') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.kenya') }}</h2>
	<p>{!! trans('prevention-information.kenya.1') !!}</p>
</div>

<div class="card">
	<h1 id="australia-continent">{{ trans('prevention-information.australia.continent') }}</h1>

	<h2 id="australia">{{ trans('prevention-information.australia') }}</h2>
	<p>{!! trans('prevention-information.australia.1') !!}</p>

	<h2 id="new-zealand">{{ trans('prevention-information.new.zealand') }}</h2>
	<p>{!! trans('prevention-information.new.zealand.1') !!}</p>

</div>
	@else
        
	<h2>{{ trans('prevention-information.europe') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#austria" title="{{ trans('prevention-information.austria') }}">🇦🇹</a>
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium') }}">🇧🇪</a>
		<a class="country-button" href="#czech" title="{{ trans('prevention-information.czech') }}">🇨🇿</a>
		<a class="country-button" href="#denmark" title="{{ trans('prevention-information.denmark') }}">🇩🇰</a>
		<a class="country-button" href="#france" title="{{ trans('prevention-information.france') }}">🇫🇷</a>
		<a class="country-button" href="#germany" title="{{ trans('prevention-information.germany') }}">🇩🇪</a>
		<a class="country-button" href="#greece" title="{{ trans('prevention-information.greece') }}">🇬🇷</a>
		<a class="country-button" href="#hungary" title="{{ trans('prevention-information.hungary') }}">🇭🇺</a>
		<a class="country-button" href="#italy" title="{{ trans('prevention-information.italy') }}">🇮🇹</a>
		<a class="country-button" href="#lativa" title="{{ trans('prevention-information.latvia') }}">🇱🇻</a>
		<a class="country-button" href="#lithuania" title="{{ trans('prevention-information.lithuania') }}">🇱🇹</a>
		<a class="country-button" href="#luxembourg" title="{{ trans('prevention-information.luxembourg') }}">🇱🇺</a>
		<a class="country-button" href="#netherlands" title="{{ trans('prevention-information.netherlands') }}">🇳🇱</a>
		<a class="country-button" href="#norway" title="{{ trans('prevention-information.norway') }}">🇳🇴</a>
		<a class="country-button" href="#poland" title="{{ trans('prevention-information.poland') }}">🇵🇱</a>
		<a class="country-button" href="#portugal" title="{{ trans('prevention-information.portugal') }}">🇵🇹</a>
		<a class="country-button" href="#russia-europe" title="{{ trans('prevention-information.russia') }}">🇷🇺</a>
		<a class="country-button" href="#serbia" title="{{ trans('prevention-information.serbia') }}">🇷🇸</a>
		<a class="country-button" href="#spain" title="{{ trans('prevention-information.spain') }}">🇪🇸</a>
		<a class="country-button" href="#sweden" title="{{ trans('prevention-information.sweden') }}">🇸🇪</a>
		<a class="country-button" href="#switzerland" title="{{ trans('prevention-information.switzerland') }}">🇨🇭</a>
		<a class="country-button" href="#ukraine" title="{{ trans('prevention-information.ukraine') }}">🇺🇦</a>
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
	<a class="country-button" href="#china" title="{{ trans('prevention-information.china') }}">🇨🇳</a>
	<a class="country-button" href="#hongkong" title="{{ trans('prevention-information.hongkong') }}">🇭🇰</a>
	<a class="country-button" href="#india" title="{{ trans('prevention-information.india') }}">🇮🇳</a>
	<a class="country-button" href="#iran" title="{{ trans('prevention-information.iran') }}">🇮🇷</a>
	<a class="country-button" href="#israel" title="{{ trans('prevention-information.israel') }}">🇮🇱</a>
	<a class="country-button" href="#japan" title="{{ trans('prevention-information.japan') }}">🇯🇵</a>
	<a class="country-button" href="#pakistan" title="{{ trans('prevention-information.pakistan') }}">🇵🇰</a>
	<a class="country-button" href="#philippines" title="{{ trans('prevention-information.philippines') }}">🇵🇭</a>
	<a class="country-button" href="#russia-asia" title="{{ trans('prevention-information.russia') }}">🇷🇺</a>
	<a class="country-button" href="#singapore" title="{{ trans('prevention-information.singapore') }}">🇸🇬</a>
	<a class="country-button" href="#south-korea" title="{{ trans('prevention-information.south.korea') }}">🇰🇷</a>
	<a class="country-button" href="#taiwan" title="{{ trans('prevention-information.taiwan') }}">🇹🇼</a>
</div>

<h2>{{ trans('prevention-information.africa') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#south-africa" title="{{ trans('prevention-information.south.africa') }}">🇿🇦</a>
		<a class="country-button" href="#nigeria" title="{{ trans('prevention-information.nigeria') }}">🇳🇬</a>
		<a class="country-button" href="#kenya" title="{{ trans('prevention-information.kenya') }}">🇰🇪</a>

	</div>

	<h2>{{ trans('prevention-information.australia.continent') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#australia" title="{{ trans('prevention-information.australia') }}">🇦🇺</a>
		<a class="country-button" href="#new-zealand" title="{{ trans('prevention-information.new.zealand') }}">🇳🇿</a>

	</div>

	<h2>{!! trans('prevention-information.search.helpline') !!}</h2>
	<p>{!! trans('prevention-information.search.helpline.1') !!}</p>

	</div>
<div class="card">
	<h1 id="europe">{{ trans('prevention-information.europe') }}</h1>

	
	<h2 id="austria">{{ trans('prevention-information.austria') }}</h2>
	<p>{!! trans('prevention-information.austria.1') !!}</p>

	<h2 id="belgium">{{ trans('prevention-information.belgium') }}</h2>
	<p>{!! trans('prevention-information.belgium.1') !!}</p>

	<h2 id="czech">{{ trans('prevention-information.czech') }}</h2>
	<p>{!! trans('prevention-information.czech.1') !!}</p>

	<h2 id="denmark">{{ trans('prevention-information.denmark') }}</h2>
	<p>{!! trans('prevention-information.denmark.1') !!}</p>

	<h2 id="france">{{ trans('prevention-information.france') }}</h2>
	<p>{!! trans('prevention-information.france.1') !!}</p>

	<h2 id="germany">{{ trans('prevention-information.germany') }}</h2>
	<p>{!! trans('prevention-information.germany.1') !!}</p>

	<h2 id="greece">{{ trans('prevention-information.greece') }}</h2>
	<p>{!! trans('prevention-information.greece.1') !!}</p>

	<h2 id="hungary">{{ trans('prevention-information.hungary') }}</h2>
	<p>{!! trans('prevention-information.hungary.1') !!}</p>

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

	<h2 id="poland">{{ trans('prevention-information.poland') }}</h2>
	<p>{!! trans('prevention-information.poland.1') !!}</p>

	<h2 id="portugal">{{ trans('prevention-information.portugal') }}</h2>
	<p>{!! trans('prevention-information.portugal.1') !!}</p>

	<h2 id="russia-europe">{{ trans('prevention-information.russia') }}</h2>
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

	<h2 id="uk">{{ trans('prevention-information.uk') }}</h2>
	<p>{!! trans('prevention-information.uk.1') !!}</p>

</div>
<div class="card">
	<h1 id="america">{{ trans('prevention-information.america') }}</h1>

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
<div class="card">
	<h1 id="asia">{{ trans('prevention-information.asia') }}</h1>	

	<h2 id="china">{{ trans('prevention-information.china') }}</h2>
	<p>{!! trans('prevention-information.china.1') !!}</p>

	<h2 id="hongkong">{{ trans('prevention-information.hongkong') }}</h2>
	<p>{!! trans('prevention-information.hongkong.1') !!}</p>

	<h2 id="india">{{ trans('prevention-information.india') }}</h2>
	<p>{!! trans('prevention-information.india.1') !!}</p>

	<h2 id="iran">{{ trans('prevention-information.iran') }}</h2>
	<p>{!! trans('prevention-information.iran.1') !!}</p>

	<h2 id="israel">{{ trans('prevention-information.israel') }}</h2>
	<p>{!! trans('prevention-information.israel.1') !!}</p>

	<h2 id="japan">{{ trans('prevention-information.japan') }}</h2>
	<p>{!! trans('prevention-information.japan.1') !!}</p>

	<h2 id="pakistan">{{ trans('prevention-information.pakistan') }}</h2>
	<p>{!! trans('prevention-information.pakistan.1') !!}</p>

	<h2 id="philippines">{{ trans('prevention-information.philippines') }}</h2>
	<p>{!! trans('prevention-information.philippines.1') !!}</p>

	<h2 id="russia-asia">{{ trans('prevention-information.russia') }}</h2>
	<p>{!! trans('prevention-information.russia.1') !!}</p>

	<h2 id="singapore">{{ trans('prevention-information.singapore') }}</h2>
	<p>{!! trans('prevention-information.singapore.1') !!}</p>

	<h2 id="south-korea">{{ trans('prevention-information.south.korea') }}</h2>
	<p>{!! trans('prevention-information.south.korea.1') !!}</p>

	<h2 id="taiwan">{{ trans('prevention-information.taiwan') }}</h2>
	<p>{!! trans('prevention-information.taiwan.1') !!}</p>
</div>

<div class="card">
	<h1 id="africa">{{ trans('prevention-information.africa') }}</h1>

	<h2 id="south-africa">{{ trans('prevention-information.south.africa') }}</h2>
	<p>{!! trans('prevention-information.south.africa.1') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.nigeria') }}</h2>
	<p>{!! trans('prevention-information.nigeria.1') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.kenya') }}</h2>
	<p>{!! trans('prevention-information.kenya.1') !!}</p>
</div>

<div class="card">
	<h1 id="australia-continent">{{ trans('prevention-information.australia.continent') }}</h1>

	<h2 id="australia">{{ trans('prevention-information.australia') }}</h2>
	<p>{!! trans('prevention-information.australia.1') !!}</p>

	<h2 id="new-zealand">{{ trans('prevention-information.new.zealand') }}</h2>
	<p>{!! trans('prevention-information.new.zealand.1') !!}</p>

</div>
	@endif

@endsection