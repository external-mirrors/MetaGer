@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
<h1 class="page-title">{{ trans('prevention-information.head.a') }}</h1>
</div>
<div class="card">
	<h2>{{ trans('prevention-information.head.b') }}</h2>
	<p>{{ trans('prevention-information.text.a') }}</p>


    @if (App\Localization::getLanguage() == "de")
        
	<h2>{{ trans('prevention-information.europe.name') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium.name') }}">🇧🇪</a>
		<a class="country-button" href="#germany" title="{{ trans('prevention-information.germany.name') }}">🇩🇪</a>
		<a class="country-button" href="#denmark" title="{{ trans('prevention-information.denmark.name') }}">🇩🇰</a>
		<a class="country-button" href="#france" title="{{ trans('prevention-information.france.name') }}">🇫🇷</a>
		<a class="country-button" href="#greece" title="{{ trans('prevention-information.greece.name') }}">🇬🇷</a>
		<a class="country-button" href="#italy" title="{{ trans('prevention-information.italy.name') }}">🇮🇹</a>
		<a class="country-button" href="#lativa" title="{{ trans('prevention-information.latvia.name') }}">🇱🇻</a>
		<a class="country-button" href="#lithuania" title="{{ trans('prevention-information.lithuania.name') }}">🇱🇹</a>
		<a class="country-button" href="#luxembourg" title="{{ trans('prevention-information.luxembourg.name') }}">🇱🇺</a>
		<a class="country-button" href="#netherlands" title="{{ trans('prevention-information.netherlands.name') }}">🇳🇱</a>
		<a class="country-button" href="#norway" title="{{ trans('prevention-information.norway.name') }}">🇳🇴</a>
		<a class="country-button" href="#austria" title="{{ trans('prevention-information.austria.name') }}">🇦🇹</a>
		<a class="country-button" href="#poland" title="{{ trans('prevention-information.poland.name') }}">🇵🇱</a>
		<a class="country-button" href="#portugal" title="{{ trans('prevention-information.portugal.name') }}">🇵🇹</a>
		<a class="country-button" href="#czech" title="{{ trans('prevention-information.czech.name') }}">🇨🇿</a>
		<a class="country-button" href="#russia-europe" title="{{ trans('prevention-information.russia.name') }}">🇷🇺</a>
		<a class="country-button" href="#sweden" title="{{ trans('prevention-information.sweden.name') }}">🇸🇪</a>
		<a class="country-button" href="#switzerland" title="{{ trans('prevention-information.switzerland.name') }}">🇨🇭</a>
		<a class="country-button" href="#serbia" title="{{ trans('prevention-information.serbia.name') }}">🇷🇸</a>
		<a class="country-button" href="#spain" title="{{ trans('prevention-information.spain.name') }}">🇪🇸</a>
		<a class="country-button" href="#ukraine" title="{{ trans('prevention-information.ukraine.name') }}">🇺🇦</a>
		<a class="country-button" href="#hungary" title="{{ trans('prevention-information.hungary.name') }}">🇭🇺</a>
		<a class="country-button" href="#uk" title="{{ trans('prevention-information.uk.name') }}">🇬🇧</a>

	</div>
	<h2>{{ trans('prevention-information.america.name') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#costa-rica" title="{{ trans('prevention-information.costa.rica.name') }}">🇨🇷</a>
		<a class="country-button" href="#canada" title="{{ trans('prevention-information.canada.name') }}">🇨🇦</a>
		<a class="country-button" href="#mexico" title="{{ trans('prevention-information.mexico.name') }}">🇲🇽</a>
		<a class="country-button" href="#usa" title="{{ trans('prevention-information.usa.name') }}">🇺🇸</a>
		<a class="country-button" href="#argentina" title="{{ trans('prevention-information.argentina.name') }}">🇦🇷</a>
		<a class="country-button" href="#brazil" title="{{ trans('prevention-information.brazil.name') }}">🇧🇷</a>
		<a class="country-button" href="#chile" title="{{ trans('prevention-information.chile.name') }}">🇨🇱</a>
	</div>


	<h2>{{ trans('prevention-information.asia.name') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#china" title="{{ trans('prevention-information.china.name') }}">🇨🇳</a>
		<a class="country-button" href="#hongkong" title="{{ trans('prevention-information.hongkong.name') }}">🇭🇰</a>
		<a class="country-button" href="#india" title="{{ trans('prevention-information.india.name') }}">🇮🇳</a>
		<a class="country-button" href="#iran" title="{{ trans('prevention-information.iran.name') }}">🇮🇷</a>
		<a class="country-button" href="#israel" title="{{ trans('prevention-information.israel.name') }}">🇮🇱</a>
		<a class="country-button" href="#japan" title="{{ trans('prevention-information.japan.name') }}">🇯🇵</a>
		<a class="country-button" href="#pakistan" title="{{ trans('prevention-information.pakistan.name') }}">🇵🇰</a>
		<a class="country-button" href="#philippines" title="{{ trans('prevention-information.philippines.name') }}">🇵🇭</a>
		<a class="country-button" href="#russia-asia" title="{{ trans('prevention-information.russia.name') }}">🇷🇺</a>
		<a class="country-button" href="#singapore" title="{{ trans('prevention-information.singapore.name') }}">🇸🇬</a>
		<a class="country-button" href="#south-korea" title="{{ trans('prevention-information.south.korea.name') }}">🇰🇷</a>
		<a class="country-button" href="#taiwan" title="{{ trans('prevention-information.taiwan.name') }}">🇹🇼</a>


	</div>

	<h2>{{ trans('prevention-information.africa.name') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#south-africa" title="{{ trans('prevention-information.south.africa.name') }}">🇿🇦</a>
		<a class="country-button" href="#nigeria" title="{{ trans('prevention-information.nigeria.name') }}">🇳🇬</a>
		<a class="country-button" href="#kenya" title="{{ trans('prevention-information.kenya.name') }}">🇰🇪</a>
	</div>

	<h2>{{ trans('prevention-information.australia.continent') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#australia" title="{{ trans('prevention-information.australia.name') }}">🇦🇺</a>
		<a class="country-button" href="#new-zealand" title="{{ trans('prevention-information.new.zealand.name') }}">🇳🇿</a>

	</div>


	<h2>{!! trans('prevention-information.search.helpline.title') !!}</h2>
	<p>{!! trans('prevention-information.search.helpline.text') !!}</p>
	
</div>
<div class="card">
	<h1 id="europe">{{ trans('prevention-information.europe.name') }}</h1>

	<h2 id="belgium">{{ trans('prevention-information.belgium.name') }}</h2>
	<p>{!! trans('prevention-information.belgium.text') !!}</p>

	<h2 id="germany">{{ trans('prevention-information.germany.name') }}</h2>
	<p>{!! trans('prevention-information.germany.text') !!}</p>

	<h2 id="denmark">{{ trans('prevention-information.denmark.name') }}</h2>
	<p>{!! trans('prevention-information.denmark.text') !!}</p>

	<h2 id="france">{{ trans('prevention-information.france.name') }}</h2>
	<p>{!! trans('prevention-information.france.text') !!}</p>

	<h2 id="greece">{{ trans('prevention-information.greece.name') }}</h2>
	<p>{!! trans('prevention-information.greece.text') !!}</p>

	<h2 id="italy"> {{ trans('prevention-information.italy.name') }}</h2>
	<p>{!! trans('prevention-information.italy.text') !!}</p>

	<h2 id="latvia">{{ trans('prevention-information.latvia.name') }}</h2>
	<p>{!! trans('prevention-information.latvia.text') !!}</p>

	<h2 id="lithuania">{{ trans('prevention-information.lithuania.name') }}</h2>
	<p>{!! trans('prevention-information.lithuania.text') !!}</p>

	<h2 id="luxembourg">{{ trans('prevention-information.luxembourg.name') }}</h2>
	<p>{!! trans('prevention-information.luxembourg.text') !!}</p>

	<h2 id="netherlands">{{ trans('prevention-information.netherlands.name') }}</h2>
	<p>{!! trans('prevention-information.netherlands.text') !!}</p>

	<h2 id="norway">{{ trans('prevention-information.norway.name') }}</h2>
	<p>{!! trans('prevention-information.norway.text') !!}</p>

	<h2 id="austria">{{ trans('prevention-information.austria.name') }}</h2>
	<p>{!! trans('prevention-information.austria.text') !!}</p>

	<h2 id="poland">{{ trans('prevention-information.poland.name') }}</h2>
	<p>{!! trans('prevention-information.poland.text') !!}</p>

	<h2 id="portugal">{{ trans('prevention-information.portugal.name') }}</h2>
	<p>{!! trans('prevention-information.portugal.text') !!}</p>

	<h2 id="czech">{{ trans('prevention-information.czech.name') }}</h2>
	<p>{!! trans('prevention-information.czech.text') !!}</p>

	<h2 id="russia-europe">{{ trans('prevention-information.russia.name') }}</h2>
	<p>{!! trans('prevention-information.russia.text') !!}</p>

	<h2 id="sweden">{{ trans('prevention-information.sweden.name') }}</h2>
	<p>{!! trans('prevention-information.sweden.text') !!}</p>

	<h2 id="switzerland">{{ trans('prevention-information.switzerland.name') }}</h2>
	<p>{!! trans('prevention-information.switzerland.text') !!}</p>

	<h2 id="serbia">{{ trans('prevention-information.serbia.name') }}</h2>
	<p>{!! trans('prevention-information.serbia.text') !!}</p>

	<h2 id="spain">{{ trans('prevention-information.spain.name') }}</h2>
	<p>{!! trans('prevention-information.spain.text') !!}</p>

	<h2 id="ukraine">{{ trans('prevention-information.ukraine.name') }}</h2>
	<p>{!! trans('prevention-information.ukraine.text') !!}</p>

	<h2 id="hungary">{{ trans('prevention-information.hungary.name') }}</h2>
	<p>{!! trans('prevention-information.hungary.text') !!}</p>

	<h2 id="uk">{{ trans('prevention-information.uk.name') }}</h2>
	<p>{!! trans('prevention-information.uk.text') !!}</p>

</div>
<div class="card">
	<h1 id="america">{{ trans('prevention-information.america.name') }}</h1>

	<h2 id="costa-rica">{{ trans('prevention-information.costa.rica.name') }}</h2>
	<p>{!! trans('prevention-information.costa.rica.text') !!}</p>

	<h2 id="canada">{{ trans('prevention-information.canada.name') }}</h2>
	<p>{!! trans('prevention-information.canada.text') !!}</p>

	<h2 id="mexico">{{ trans('prevention-information.mexico.name') }}</h2>
	<p>{!! trans('prevention-information.mexico.text') !!}</p>

	<h2 id="usa">{{ trans('prevention-information.usa.name') }}</h2>
	<p>{!! trans('prevention-information.usa.text') !!}</p>
	
	<h2 id="argentina">{{ trans('prevention-information.argentina.name') }}</h2>
	<p>{!! trans('prevention-information.argentina.text') !!}</p>

	<h2 id="brazil">{{ trans('prevention-information.brazil.name') }}</h2>
	<p>{!! trans('prevention-information.brazil.text') !!}</p>

	<h2 id="chile">{{ trans('prevention-information.chile.name') }}</h2>
	<p>{!! trans('prevention-information.chile.text') !!}</p>

</div>
<div class="card">
	<h1 id="asia">{{ trans('prevention-information.asia.name') }}</h1>	

	<h2 id="china">{{ trans('prevention-information.china.name') }}</h2>
	<p>{!! trans('prevention-information.china.text') !!}</p>

	<h2 id="hongkong">{{ trans('prevention-information.hongkong.name') }}</h2>
	<p>{!! trans('prevention-information.hongkong.text') !!}</p>

	<h2 id="india">{{ trans('prevention-information.india.name') }}</h2>
	<p>{!! trans('prevention-information.india.text') !!}</p>

	<h2 id="iran">{{ trans('prevention-information.iran.name') }}</h2>
	<p>{!! trans('prevention-information.iran.text') !!}</p>

	<h2 id="israel">{{ trans('prevention-information.israel.name') }}</h2>
	<p>{!! trans('prevention-information.israel.text') !!}</p>

	<h2 id="japan">{{ trans('prevention-information.japan.name') }}</h2>
	<p>{!! trans('prevention-information.japan.text') !!}</p>

	<h2 id="pakistan">{{ trans('prevention-information.pakistan.name') }}</h2>
	<p>{!! trans('prevention-information.pakistan.text') !!}</p>

	<h2 id="philippines">{{ trans('prevention-information.philippines.name') }}</h2>
	<p>{!! trans('prevention-information.philippines.text') !!}</p>

	<h2 id="russia-asia">{{ trans('prevention-information.russia.name') }}</h2>
	<p>{!! trans('prevention-information.russia.text') !!}</p>

	<h2 id="singapore">{{ trans('prevention-information.singapore.name') }}</h2>
	<p>{!! trans('prevention-information.singapore.text') !!}</p>

	<h2 id="south-korea">{{ trans('prevention-information.south.korea.name') }}</h2>
	<p>{!! trans('prevention-information.south.korea.text') !!}</p>

	<h2 id="taiwan">{{ trans('prevention-information.taiwan.name') }}</h2>
	<p>{!! trans('prevention-information.taiwan.text') !!}</p>
</div>

<div class="card">
	<h1 id="africa">{{ trans('prevention-information.africa.name') }}</h1>

	<h2 id="south-africa">{{ trans('prevention-information.south.africa.name') }}</h2>
	<p>{!! trans('prevention-information.south.africa.text') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.nigeria.name') }}</h2>
	<p>{!! trans('prevention-information.nigeria.text') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.kenya.name') }}</h2>
	<p>{!! trans('prevention-information.kenya.text') !!}</p>
</div>

<div class="card">
	<h1 id="australia-continent">{{ trans('prevention-information.australia.continent') }}</h1>

	<h2 id="australia">{{ trans('prevention-information.australia.name') }}</h2>
	<p>{!! trans('prevention-information.australia.text') !!}</p>

	<h2 id="new-zealand">{{ trans('prevention-information.new.zealand.name') }}</h2>
	<p>{!! trans('prevention-information.new.zealand.text') !!}</p>

</div>
	@else
        
	<h2>{{ trans('prevention-information.europe.name') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#austria" title="{{ trans('prevention-information.austria.name') }}">🇦🇹</a>
		<a class="country-button" href="#belgium" title="{{ trans('prevention-information.belgium.name') }}">🇧🇪</a>
		<a class="country-button" href="#czech" title="{{ trans('prevention-information.czech.name') }}">🇨🇿</a>
		<a class="country-button" href="#denmark" title="{{ trans('prevention-information.denmark.name') }}">🇩🇰</a>
		<a class="country-button" href="#france" title="{{ trans('prevention-information.france.name') }}">🇫🇷</a>
		<a class="country-button" href="#germany" title="{{ trans('prevention-information.germany.name') }}">🇩🇪</a>
		<a class="country-button" href="#greece" title="{{ trans('prevention-information.greece.name') }}">🇬🇷</a>
		<a class="country-button" href="#hungary" title="{{ trans('prevention-information.hungary.name') }}">🇭🇺</a>
		<a class="country-button" href="#italy" title="{{ trans('prevention-information.italy.name') }}">🇮🇹</a>
		<a class="country-button" href="#lativa" title="{{ trans('prevention-information.latvia.name') }}">🇱🇻</a>
		<a class="country-button" href="#lithuania" title="{{ trans('prevention-information.lithuania.name') }}">🇱🇹</a>
		<a class="country-button" href="#luxembourg" title="{{ trans('prevention-information.luxembourg.name') }}">🇱🇺</a>
		<a class="country-button" href="#netherlands" title="{{ trans('prevention-information.netherlands.name') }}">🇳🇱</a>
		<a class="country-button" href="#norway" title="{{ trans('prevention-information.norway.name') }}">🇳🇴</a>
		<a class="country-button" href="#poland" title="{{ trans('prevention-information.poland.name') }}">🇵🇱</a>
		<a class="country-button" href="#portugal" title="{{ trans('prevention-information.portugal.name') }}">🇵🇹</a>
		<a class="country-button" href="#russia-europe" title="{{ trans('prevention-information.russia.name') }}">🇷🇺</a>
		<a class="country-button" href="#serbia" title="{{ trans('prevention-information.serbia.name') }}">🇷🇸</a>
		<a class="country-button" href="#spain" title="{{ trans('prevention-information.spain.name') }}">🇪🇸</a>
		<a class="country-button" href="#sweden" title="{{ trans('prevention-information.sweden.name') }}">🇸🇪</a>
		<a class="country-button" href="#switzerland" title="{{ trans('prevention-information.switzerland.name') }}">🇨🇭</a>
		<a class="country-button" href="#ukraine" title="{{ trans('prevention-information.ukraine.name') }}">🇺🇦</a>
		<a class="country-button" href="#uk" title="{{ trans('prevention-information.uk.name') }}">🇬🇧</a>

	</div>	

	<h2>{{ trans('prevention-information.america.name') }}</h2>
	<div class="country-button-row">
		<a class="country-button" href="#costa-rica" title="{{ trans('prevention-information.costa.rica.name') }}">🇨🇷</a>
		<a class="country-button" href="#canada" title="{{ trans('prevention-information.canada.name') }}">🇨🇦</a>
		<a class="country-button" href="#mexico" title="{{ trans('prevention-information.mexico.name') }}">🇲🇽</a>
		<a class="country-button" href="#usa" title="{{ trans('prevention-information.usa.name') }}">🇺🇸</a>
		<a class="country-button" href="#argentina" title="{{ trans('prevention-information.argentina.name') }}">🇦🇷</a>
		<a class="country-button" href="#brazil" title="{{ trans('prevention-information.brazil.name') }}">🇧🇷</a>
		<a class="country-button" href="#chile" title="{{ trans('prevention-information.chile.name') }}">🇨🇱</a>
	</div>

	<h2>{{ trans('prevention-information.asia.name') }}</h2>

<div class="country-button-row">
	<a class="country-button" href="#china" title="{{ trans('prevention-information.china.name') }}">🇨🇳</a>
	<a class="country-button" href="#hongkong" title="{{ trans('prevention-information.hongkong.name') }}">🇭🇰</a>
	<a class="country-button" href="#india" title="{{ trans('prevention-information.india.name') }}">🇮🇳</a>
	<a class="country-button" href="#iran" title="{{ trans('prevention-information.iran.name') }}">🇮🇷</a>
	<a class="country-button" href="#israel" title="{{ trans('prevention-information.israel.name') }}">🇮🇱</a>
	<a class="country-button" href="#japan" title="{{ trans('prevention-information.japan.name') }}">🇯🇵</a>
	<a class="country-button" href="#pakistan" title="{{ trans('prevention-information.pakistan.name') }}">🇵🇰</a>
	<a class="country-button" href="#philippines" title="{{ trans('prevention-information.philippines.name') }}">🇵🇭</a>
	<a class="country-button" href="#russia-asia" title="{{ trans('prevention-information.russia.name') }}">🇷🇺</a>
	<a class="country-button" href="#singapore" title="{{ trans('prevention-information.singapore.name') }}">🇸🇬</a>
	<a class="country-button" href="#south-korea" title="{{ trans('prevention-information.south.korea.name') }}">🇰🇷</a>
	<a class="country-button" href="#taiwan" title="{{ trans('prevention-information.taiwan.name') }}">🇹🇼</a>
</div>

<h2>{{ trans('prevention-information.africa.name') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#south-africa" title="{{ trans('prevention-information.south.africa.name') }}">🇿🇦</a>
		<a class="country-button" href="#nigeria" title="{{ trans('prevention-information.nigeria.name') }}">🇳🇬</a>
		<a class="country-button" href="#kenya" title="{{ trans('prevention-information.kenya.name') }}">🇰🇪</a>

	</div>

	<h2>{{ trans('prevention-information.australia.continent') }}</h2>

	<div class="country-button-row">
		<a class="country-button" href="#australia" title="{{ trans('prevention-information.australia.name') }}">🇦🇺</a>
		<a class="country-button" href="#new-zealand" title="{{ trans('prevention-information.new.zealand.name') }}">🇳🇿</a>

	</div>

	<h2>{!! trans('prevention-information.search.helpline.title') !!}</h2>
	<p>{!! trans('prevention-information.search.helpline.text') !!}</p>

	</div>
<div class="card">
	<h1 id="europe">{{ trans('prevention-information.europe.name') }}</h1>

	
	<h2 id="austria">{{ trans('prevention-information.austria.name') }}</h2>
	<p>{!! trans('prevention-information.austria.text') !!}</p>

	<h2 id="belgium">{{ trans('prevention-information.belgium.name') }}</h2>
	<p>{!! trans('prevention-information.belgium.text') !!}</p>

	<h2 id="czech">{{ trans('prevention-information.czech.name') }}</h2>
	<p>{!! trans('prevention-information.czech.text') !!}</p>

	<h2 id="denmark">{{ trans('prevention-information.denmark.name') }}</h2>
	<p>{!! trans('prevention-information.denmark.text') !!}</p>

	<h2 id="france">{{ trans('prevention-information.france.name') }}</h2>
	<p>{!! trans('prevention-information.france.text') !!}</p>

	<h2 id="germany">{{ trans('prevention-information.germany.name') }}</h2>
	<p>{!! trans('prevention-information.germany.text') !!}</p>

	<h2 id="greece">{{ trans('prevention-information.greece.name') }}</h2>
	<p>{!! trans('prevention-information.greece.text') !!}</p>

	<h2 id="hungary">{{ trans('prevention-information.hungary.name') }}</h2>
	<p>{!! trans('prevention-information.hungary.text') !!}</p>

	<h2 id="italy"> {{ trans('prevention-information.italy.name') }}</h2>
	<p>{!! trans('prevention-information.italy.text') !!}</p>

	<h2 id="latvia">{{ trans('prevention-information.latvia.name') }}</h2>
	<p>{!! trans('prevention-information.latvia.text') !!}</p>

	<h2 id="lithuania">{{ trans('prevention-information.lithuania.name') }}</h2>
	<p>{!! trans('prevention-information.lithuania.text') !!}</p>

	<h2 id="luxembourg">{{ trans('prevention-information.luxembourg.name') }}</h2>
	<p>{!! trans('prevention-information.luxembourg.text') !!}</p>

	<h2 id="netherlands">{{ trans('prevention-information.netherlands.name') }}</h2>
	<p>{!! trans('prevention-information.netherlands.text') !!}</p>

	<h2 id="norway">{{ trans('prevention-information.norway.name') }}</h2>
	<p>{!! trans('prevention-information.norway.text') !!}</p>

	<h2 id="poland">{{ trans('prevention-information.poland.name') }}</h2>
	<p>{!! trans('prevention-information.poland.text') !!}</p>

	<h2 id="portugal">{{ trans('prevention-information.portugal.name') }}</h2>
	<p>{!! trans('prevention-information.portugal.text') !!}</p>

	<h2 id="russia-europe">{{ trans('prevention-information.russia.name') }}</h2>
	<p>{!! trans('prevention-information.russia.text') !!}</p>

	<h2 id="serbia">{{ trans('prevention-information.serbia.name') }}</h2>
	<p>{!! trans('prevention-information.serbia.text') !!}</p>

	<h2 id="spain">{{ trans('prevention-information.spain.name') }}</h2>
	<p>{!! trans('prevention-information.spain.text') !!}</p>

	<h2 id="sweden">{{ trans('prevention-information.sweden.name') }}</h2>
	<p>{!! trans('prevention-information.sweden.text') !!}</p>

	<h2 id="switzerland">{{ trans('prevention-information.switzerland.name') }}</h2>
	<p>{!! trans('prevention-information.switzerland.text') !!}</p>

	<h2 id="ukraine">{{ trans('prevention-information.ukraine.name') }}</h2>
	<p>{!! trans('prevention-information.ukraine.text') !!}</p>

	<h2 id="uk">{{ trans('prevention-information.uk.name') }}</h2>
	<p>{!! trans('prevention-information.uk.text') !!}</p>

</div>
<div class="card">
	<h1 id="america">{{ trans('prevention-information.america.name') }}</h1>

	<h2 id="costa-rica">{{ trans('prevention-information.costa.rica.name') }}</h2>
	<p>{!! trans('prevention-information.costa.rica.text') !!}</p>

	<h2 id="canada">{{ trans('prevention-information.canada.name') }}</h2>
	<p>{!! trans('prevention-information.canada.text') !!}</p>

	<h2 id="mexico">{{ trans('prevention-information.mexico.name') }}</h2>
	<p>{!! trans('prevention-information.mexico.text') !!}</p>

	<h2 id="usa">{{ trans('prevention-information.usa.name') }}</h2>
	<p>{!! trans('prevention-information.usa.text') !!}</p>
	
	<h2 id="argentina">{{ trans('prevention-information.argentina.name') }}</h2>
	<p>{!! trans('prevention-information.argentina.text') !!}</p>

	<h2 id="brazil">{{ trans('prevention-information.brazil.name') }}</h2>
	<p>{!! trans('prevention-information.brazil.text') !!}</p>

	<h2 id="chile">{{ trans('prevention-information.chile.name') }}</h2>
	<p>{!! trans('prevention-information.chile.text') !!}</p>

</div>
<div class="card">
	<h1 id="asia">{{ trans('prevention-information.asia.name') }}</h1>	

	<h2 id="china">{{ trans('prevention-information.china.name') }}</h2>
	<p>{!! trans('prevention-information.china.text') !!}</p>

	<h2 id="hongkong">{{ trans('prevention-information.hongkong.name') }}</h2>
	<p>{!! trans('prevention-information.hongkong.text') !!}</p>

	<h2 id="india">{{ trans('prevention-information.india.name') }}</h2>
	<p>{!! trans('prevention-information.india.text') !!}</p>

	<h2 id="iran">{{ trans('prevention-information.iran.name') }}</h2>
	<p>{!! trans('prevention-information.iran.text') !!}</p>

	<h2 id="israel">{{ trans('prevention-information.israel.name') }}</h2>
	<p>{!! trans('prevention-information.israel.text') !!}</p>

	<h2 id="japan">{{ trans('prevention-information.japan.name') }}</h2>
	<p>{!! trans('prevention-information.japan.text') !!}</p>

	<h2 id="pakistan">{{ trans('prevention-information.pakistan.name') }}</h2>
	<p>{!! trans('prevention-information.pakistan.text') !!}</p>

	<h2 id="philippines">{{ trans('prevention-information.philippines.name') }}</h2>
	<p>{!! trans('prevention-information.philippines.text') !!}</p>

	<h2 id="russia-asia">{{ trans('prevention-information.russia.name') }}</h2>
	<p>{!! trans('prevention-information.russia.text') !!}</p>

	<h2 id="singapore">{{ trans('prevention-information.singapore.name') }}</h2>
	<p>{!! trans('prevention-information.singapore.text') !!}</p>

	<h2 id="south-korea">{{ trans('prevention-information.south.korea.name') }}</h2>
	<p>{!! trans('prevention-information.south.korea.text') !!}</p>

	<h2 id="taiwan">{{ trans('prevention-information.taiwan.name') }}</h2>
	<p>{!! trans('prevention-information.taiwan.text') !!}</p>
</div>

<div class="card">
	<h1 id="africa">{{ trans('prevention-information.africa.name') }}</h1>

	<h2 id="south-africa">{{ trans('prevention-information.south.africa.name') }}</h2>
	<p>{!! trans('prevention-information.south.africa.text') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.nigeria.name') }}</h2>
	<p>{!! trans('prevention-information.nigeria.text') !!}</p>

	<h2 id="nigeria">{{ trans('prevention-information.kenya.name') }}</h2>
	<p>{!! trans('prevention-information.kenya.text') !!}</p>
</div>

<div class="card">
	<h1 id="australia-continent">{{ trans('prevention-information.australia.continent') }}</h1>

	<h2 id="australia">{{ trans('prevention-information.australia.name') }}</h2>
	<p>{!! trans('prevention-information.australia.text') !!}</p>

	<h2 id="new-zealand">{{ trans('prevention-information.new.zealand.name') }}</h2>
	<p>{!! trans('prevention-information.new.zealand.text') !!}</p>

</div>
	@endif

@endsection
