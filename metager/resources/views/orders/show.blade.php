@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Eine Bestellung — App\Http\Controllers\OrderController::show().

	$order kommt validiert aus OrderHistoryIssuer; wem sie gehört, hat der
	Controller schon gegen den Cookie-Schlüssel geprüft. Pro Zahlung eine
	Zeilengruppe (netto / MwSt. / gesamt, plus Wechselkurs bei Fremdwährung),
	Beträge und Stückzahlen gehen durch App\Support\Money::euro() bzw.
	Number::format(): der Keyserver liefert sie als „9.35" und „1000", und so
	standen sie auch auf der Seite — Punkt als Dezimaltrennzeichen und keine
	Tausendertrennung, auf einer Seite, die in zwölf Sprachen ausgeliefert
	wird und deren übrige Beträge („10 €", „1.000 Token") längst lokalisiert
	sind.

	darunter die Auftragsbestätigung als PDF, der Link zur Rechnung
	(InvoiceNinja, App\Http\Controllers\OrderController::invoice()) und,
	sofern refund_available zutrifft, der Link zur Erstattung
	(App\Http\Controllers\OrderController::refund()) — payments[0], dieselbe
	"die eine" Zahlung, die invoice_available schon voraussetzt.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('orders.show.heading', ['reference' => $order['public_id']])</h1>
		@include('partials.key-fingerprint')
	</header>

	<nav class="account-breadcrumb">
		<a href="{{ $lookupUrl }}">← @lang('orders.show.breadcrumb')</a>
		<a href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		@forelse($order['payments'] as $payment)
			@php
				$bookedAt = \Illuminate\Support\Carbon::parse($payment['created_at'] ?? $order['created_at']);
			@endphp
			<div class="orders-detail">
				<h2 class="account-section__heading">@lang('orders.show.order_line', [
					'id' => $payment['public_id'],
					'date' => $bookedAt->isoFormat('L'),
				])</h2>

				<table class="orders-lines">
					<tbody>
						<tr>
							<th scope="row">@lang('orders.show.item')</th>
							<td class="orders-lines__count">{{ \Illuminate\Support\Number::format($payment['token_count'], locale: app()->getLocale()) }}</td>
							<td class="orders-lines__price">{{ \App\Support\Money::euro($payment['net']) }}</td>
						</tr>
						<tr>
							<th scope="row">@lang('orders.show.vat', ['rate' => $payment['vat_rate'] + 0])</th>
							<td></td>
							<td class="orders-lines__price">{{ \App\Support\Money::euro($payment['vat']) }}</td>
						</tr>
						<tr class="orders-lines__total">
							<th scope="row">@lang('orders.show.total')</th>
							<td></td>
							<td class="orders-lines__price">{{ \App\Support\Money::euro($payment['gross']) }}</td>
						</tr>
						@if($payment['converted_currency'] && $payment['converted_currency'] !== 'EUR' && $payment['converted_price'] !== null)
							<tr class="orders-lines__exchange">
								<th scope="row">@lang('orders.show.exchange_rate')</th>
								<td></td>
								<td class="orders-lines__price">{{ number_format($payment['converted_price'], 2, '.', '') }} {{ $payment['converted_currency'] }}</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>
		@empty
			<p class="account-blocked">@lang('orders.show.pending')</p>
		@endforelse

		@if(count($order['payments']) > 0)
			<p class="account-section__lede">@lang('orders.show.thanks')</p>
			<div class="checkout-returned__actions">
				<a class="account-btn account-btn--primary" href="{{ $confirmationUrl }}" target="_blank" rel="noopener">@lang('orders.show.download_confirmation')</a>
				<a class="account-btn account-btn--quiet" href="{{ route('account.orders.invoice', ['reference' => $order['public_id']]) }}">@lang('orders.show.request_invoice')</a>
				@if($order['payments'][0]['refund_available'])
					<a class="account-btn account-btn--quiet" href="{{ route('account.orders.refund', ['reference' => $order['public_id']]) }}">@lang('orders.show.request_refund')</a>
				@endif
			</div>
		@endif

		<p class="account-section__lede">@lang('orders.show.lookup_hint', ['reference' => $order['public_id']])</p>
	</section>
</div>
@endsection
