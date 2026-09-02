@extends('layouts.subPages', ['page' => 'c'])

@section('title', $title)

@section('content')
{{--
	Einen Gutschein einlösen — was drin ist.

	Lag als /keys/c im Keymanager (views/campaign/teaser.ejs). Der Code stimmt,
	eingelöst ist er noch nicht: diese Seite zeigt, was er wert ist, und fragt
	nach. Deshalb die Geschenkkarte mit der großen Zahl — sie ist das Einzige,
	weswegen jemand hier ist, und ein Gutschein, der wie ein Formular aussieht,
	sieht aus wie eine Rechnung.

	Ein Knopf und ein POST und kein Link: das Einlösen verbraucht den Gutschein,
	und was etwas verbraucht, gehört nicht hinter eine Adresse, die ein
	Vorschaudienst von sich aus abruft.

	Die Zahl geht durch Number::format() — wie auf allen Kontoseiten, weil sie
	in zwölf Sprachen ausgeliefert wird und `1000` anderswo `1.000` heißt. Die
	große Zahl und die im Satz darunter durch dieselbe Funktion: zwei
	Schreibweisen derselben Zahl auf einer Seite sind schlimmer als beide
	Schreibweisen einzeln.
--}}
@php
	$number = fn (int|float $value) => \Illuminate\Support\Number::format($value, locale: app()->getLocale());
@endphp
<div id="voucher-page" class="voucher-page--teaser">
	<h1 class="page-title">@lang('campaigns.redeem.teaser.heading')</h1>

	{{--
		Der Name der Aktion, klein und gedämpft: er beantwortet „von wem ist
		der?“ für den, der fragt, und drängt sich dem nicht auf, der es weiß —
		er steht meistens schon auf der Karte in der Hand.
	--}}
	<div class="voucher-gift">
		<span class="voucher-gift__campaign">{{ $campaignName }}</span>
		<span class="voucher-gift__amount">{{ $number($tokens) }}</span>
		<span class="voucher-gift__label">@lang('campaigns.redeem.teaser.tokens')</span>
	</div>

	<p class="voucher-lede">@lang('campaigns.redeem.teaser.description', ['tokens' => $number($tokens)])</p>
	<p class="voucher-hint">@lang('campaigns.redeem.teaser.validity', ['days' => $number($validityDays)])</p>

	<form class="voucher-form" method="POST" action="{{ $action }}">
		<button class="voucher-submit" type="submit">@lang('campaigns.redeem.teaser.submit')</button>
	</form>
</div>
@endsection
