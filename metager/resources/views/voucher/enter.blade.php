@extends('layouts.subPages', ['page' => 'c'])

@section('title', $title)

@section('content')
{{--
	Einen Gutschein einlösen — der Code.

	Lag als /keys/c im Keymanager (views/campaign/enter.ejs). Die Seite ist die
	kleine Schwester der Anmeldeseite: ein Feld, ein Knopf, und darüber die
	Fehlermeldung des letzten Versuchs.

	Ohne Javascript ist sie vollständig. resources/js/voucher.js schreibt den
	Code beim Tippen groß und setzt die Bindestriche; ein Code ohne sie ist
	derselbe Code, und dass er das ist, muss der Server entscheiden — hier
	kann er es nicht.

	Kein CSRF-Feld: Webrouten haben keine Session (siehe CLAUDE.md), an ihrer
	Stelle steht die Prüfung auf gleiche Herkunft.
--}}
<div id="voucher-page" class="voucher-page--enter">
	<h1 class="page-title">@lang('campaigns.redeem.enter.heading')</h1>
	<p class="voucher-lede">@lang('campaigns.redeem.enter.description')</p>

	<form class="voucher-card" method="POST" action="{{ $action }}">
		@if($error !== null)
			<p class="voucher-card__error" role="alert">@lang("campaigns.redeem.enter.$error")</p>
		@endif

		<div class="voucher-field">
			<label class="voucher-field__label" for="voucher-code">@lang('campaigns.redeem.enter.label')</label>
			{{--
				data-code-length sagt dem Skript, wo der Code zu Ende ist.
				Kein maxlength daneben: ohne Skript soll hier nichts
				abgeschnitten werden, was der Server noch lesen könnte.

				Das Skript schreibt groß und setzt Bindestriche — mehr nicht.
				Die mehrdeutigen Zeichen (I und L werden zu 1, O wird zu 0)
				räumt VoucherController::normalizeCode() weg, und nur dort:
				wer sie schon beim Tippen ersetzt sähe, hielte das für einen
				Tippfehler der Seite und finge von vorne an.
			--}}
			<input class="voucher-field__input" type="text" name="code" id="voucher-code"
				value="{{ $oldCode }}" placeholder="XXXX-XXXX-XX"
				data-code-length="{{ $codeLength }}"
				required autofocus autocomplete="off" autocapitalize="characters"
				spellcheck="false">
		</div>

		<button class="voucher-submit" type="submit">@lang('campaigns.redeem.enter.submit')</button>
	</form>
</div>
@endsection
