@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Die Erstattung — App\Http\Controllers\OrderController::refund()/
	refundRequest(). $order kommt validiert aus OrderHistoryIssuer; wem sie
	gehört, hat der Controller schon gegen den Cookie-Schlüssel geprüft.

	Bewegt kein Geld: der Keyserver öffnet nur ein Zammad-Ticket und bucht
	das ungenutzte Guthaben vom Schlüssel ab. Die eigentliche Rückzahlung
	ist ein manueller Schritt des Supports über dieses Ticket.

	$refundAvailable spiegelt payments[0].refund_available — sobald keine
	erstattungsfähigen Token mehr auf dem Schlüssel liegen (frisch
	erstattet, oder es waren nie welche da, weil die Zahlungsart keine
	Erstattung unterstützt), ersetzt ein Hinweis das Formular, genau wie
	invoice.blade.php es für eine bereits vorhandene Rechnung tut.

	$justRequested unterscheidet die beiden Gründe für "kein Hinweis mehr
	nötig": ohne ihn sah eine gerade erfolgreich abgeschickte Anfrage genauso
	aus wie "hier gibt es nichts (mehr) zu tun" — derselbe Text nach einem
	Erfolg wie nach einer bereits erledigten oder unmöglichen Anfrage.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('orders.refund.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<nav class="orders-breadcrumb">
		<a href="{{ route('account.orders.show', ['reference' => $order['public_id']]) }}">← @lang('orders.refund.breadcrumb', ['reference' => $order['public_id']])</a>
		<a href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		@if(!$refundAvailable)
			@if($justRequested)
				<p class="account-section__lede">@lang('orders.refund.success')</p>
			@else
				<p class="account-section__lede">@lang('orders.refund.unavailable')</p>
			@endif
		@else
			<p class="account-section__lede">{{ __('orders.refund.description') }}</p>

			@if($refundTokenCount < $order['payments'][0]['token_count'])
				<p class="orders-refund__note">{!! __('orders.refund.partial_note', ['count' => $refundTokenCount, 'total' => $order['payments'][0]['token_count']]) !!}</p>
			@endif

			@if($error !== null)
				<p class="checkout-consent__error" role="alert">@lang('orders.refund.error.' . $error)</p>
			@endif

			<form method="post" action="{{ route('account.orders.refund.request', ['reference' => $order['public_id']]) }}" class="orders-invoice">
				<div class="orders-invoice__field">
					<label for="orders-refund-message">@lang('orders.refund.message.label')</label>
					<textarea name="message" id="orders-refund-message" rows="6">{{ $message }}</textarea>
				</div>

				<button type="submit" class="account-btn account-btn--primary checkout-submit">{{ __('orders.refund.submit', ['amount' => \App\Support\Money::amount($refundAmount)]) }}</button>
			</form>
		@endif
	</section>
</div>
@endsection
