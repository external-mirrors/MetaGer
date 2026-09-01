@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Die Rechnung (InvoiceNinja) — App\Http\Controllers\OrderController::
	invoice()/invoiceRequest(). $order kommt validiert aus OrderHistoryIssuer;
	wem sie gehört, hat der Controller schon gegen den Cookie-Schlüssel
	geprüft, und dass es überhaupt eine Zahlung gibt.

	$invoiceAvailable spiegelt payments[0].invoice_available — sobald einmal
	eine Rechnung existiert, ersetzt der Downloadlink das Formular; ein
	zweites Absenden würde am Keyserver ohnehin nur dieselbe Rechnung
	zurückgeben (Payment.setReceipt ist WHERE receipt_id IS NULL), aber es
	soll erst gar nicht so aussehen, als entstünde eine zweite.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('orders.invoice.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.orders.show', ['reference' => $order['public_id']]) }}">← @lang('orders.invoice.breadcrumb', ['reference' => $order['public_id']])</a>
		<a class="checkout-back" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		@if($invoiceAvailable)
			<p class="account-section__lede">@lang('orders.invoice.ready')</p>
			<a class="account-btn account-btn--primary" href="{{ $invoicePdfUrl }}" target="_blank" rel="noopener">@lang('orders.invoice.download')</a>
		@else
			<p class="account-section__lede">@lang('orders.invoice.description')</p>

			@if(count($errors) > 0)
				<p class="checkout-consent__error" role="alert">@lang('orders.invoice.error.invalid')</p>
			@endif

			<form method="post" action="{{ route('account.orders.invoice.request', ['reference' => $order['public_id']]) }}" class="orders-invoice">
				<div class="orders-invoice__field @if(in_array('company', $errors)) orders-invoice__field--error @endif">
					<label for="orders-invoice-company">@lang('orders.invoice.field.company')</label>
					<input type="text" name="company" id="orders-invoice-company" autocomplete="organization" value="{{ $fields['company'] }}">
				</div>

				<div class="orders-invoice__row">
					<div class="orders-invoice__field @if(in_array('first_name', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-first-name">@lang('orders.invoice.field.first_name')*</label>
						<input type="text" name="first_name" id="orders-invoice-first-name" autocomplete="given-name" required value="{{ $fields['first_name'] }}">
					</div>
					<div class="orders-invoice__field @if(in_array('last_name', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-last-name">@lang('orders.invoice.field.last_name')*</label>
						<input type="text" name="last_name" id="orders-invoice-last-name" autocomplete="family-name" required value="{{ $fields['last_name'] }}">
					</div>
				</div>

				<div class="orders-invoice__row">
					<div class="orders-invoice__field @if(in_array('address1', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-address1">@lang('orders.invoice.field.address1')*</label>
						<input type="text" name="address1" id="orders-invoice-address1" autocomplete="address-line1" required value="{{ $fields['address1'] }}">
					</div>
					<div class="orders-invoice__field @if(in_array('address2', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-address2">@lang('orders.invoice.field.address2')</label>
						<input type="text" name="address2" id="orders-invoice-address2" autocomplete="address-line2" value="{{ $fields['address2'] }}">
					</div>
				</div>

				<div class="orders-invoice__row">
					<div class="orders-invoice__field @if(in_array('zip', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-zip">@lang('orders.invoice.field.zip')*</label>
						<input type="text" name="zip" id="orders-invoice-zip" autocomplete="postal-code" required value="{{ $fields['zip'] }}">
					</div>
					<div class="orders-invoice__field @if(in_array('city', $errors)) orders-invoice__field--error @endif">
						<label for="orders-invoice-city">@lang('orders.invoice.field.city')*</label>
						<input type="text" name="city" id="orders-invoice-city" autocomplete="address-level2" required value="{{ $fields['city'] }}">
					</div>
				</div>

				<div class="orders-invoice__field @if(in_array('state', $errors)) orders-invoice__field--error @endif">
					<label for="orders-invoice-state">@lang('orders.invoice.field.state')</label>
					<input type="text" name="state" id="orders-invoice-state" autocomplete="address-level1" value="{{ $fields['state'] }}">
				</div>

				<button type="submit" class="account-btn account-btn--primary">@lang('orders.invoice.submit')</button>
			</form>

			<p class="orders-invoice__storage">{!! __('orders.invoice.storage') !!}</p>
		@endif
	</section>
</div>
@endsection
