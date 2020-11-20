@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
	<div id="donation">
		<div class="section">
			<h1>{!! trans('spende.headline.1') !!}</h1>
			<p>{!! trans('spende.headline.2') !!}</p>
		</div>
		<div class="two-col">
			<form class="col-left" action="/" method="get">
				<div class="section">
					<h3>{!! trans('spende.headline.3') !!}</h3>
						<div class="amount-row">
						<input type="radio" value="5" class="amount-radio" name="amount" id="amount-5euro" required="required"> <label for="amount-5euro" class="amount-label">5€</label> 
						<input type="radio" value="10" class="amount-radio" name="amount" id="amount-10euro" required="required"><label for="amount-10euro" class="amount-label">10€</label>
						<input type="radio" value="15" class="amount-radio" name="amount" id="amount-15euro" required="required"><label for="amount-15euro" class="amount-label">15€</label>
						<input type="radio" value="20" class="amount-radio" name="amount" id="amount-20euro" required="required"><label for="amount-20euro" class="amount-label">20€</label>
						<input type="radio" value="25" class="amount-radio" name="amount" id="amount-25euro" required="required"><label for="amount-25euro" class="amount-label">25€</label> <br>
						</div>
						<div class="amount-row">
						<input type="radio" value="50" class="amount-radio" name="amount" id="amount-50euro" required="required"><label for="amount-50euro" class="amount-label">50€</label>
						<input type="radio" value="100" class="amount-radio" name="amount" id="amount-100euro" required="required"><label for="amount-100euro" class="amount-label">100€</label>
						<input type="radio" value="200" class="amount-radio" name="amount" id="amount-200euro" required="required"><label for="amount-200euro" class="amount-label">200€</label>
						<input type="radio" value="250" class="amount-radio" name="amount" id="amount-250euro" required="required"><label for="amount-250euro" class="amount-label">250€</label>
						<input type="radio" value="300" class="amount-radio" name="amount" id="amount-300euro" required="required"><label for="amount-300euro" class="amount-label">300€</label> <br>
						</div>
						<div>
						<input type="radio" name="amount" id="amount-custom" required="required"><label for="amount-custom" class="amount-custom">{!! trans('spende.wunschbetrag') !!}</label> <input id="custom-amount" type="number" step=".01" placeholder="Betrag in €" value="">
						</div>
					
					
					
					<br> 
					<h3>{!! trans('spende.headline.4') !!}</h3>

						<input type="radio" class="frequency-radio" name="frequency" id="once" required="required"><label class="frequency-label" for="once">{!! trans('spende.frequency.1') !!}</label> <br>

						<input type="radio" class="frequency-radio" name="frequency" id="monthly" required="required"><label class="frequency-label" for="monthly">{!! trans('spende.frequency.2') !!}</label> 


						<input type="radio" class="frequency-radio" name="frequency" id="quarterly" required="required"><label class="frequency-label" for="quarterly">{!! trans('spende.frequency.3') !!}</label> <br>

						<input type="radio" class="frequency-radio" name="frequency" id="six-monthly" required="required"><label class="frequency-label" for="six-monthly">{!! trans('spende.frequency.4') !!}</label> 

						<input type="radio" class="frequency-radio" name="frequency" id="annual" required="required"><label class="frequency-label" for="annual">{!! trans('spende.frequency.5') !!}</label> <br>

					<br>
					<h3>{!! trans('spende.headline.5') !!}</h3>

					<input type="radio" class="payment-radio" name="payment-method" id="lastschrift" required="required"><label class="payment-label" for="lastschrift">{!! trans('spende.head.lastschrift') !!}</label><br>

					<div class="sepa-debit-details">
					<p>{!! trans('spende.lastschrift.2') !!}</p>
						<input type="hidden" name="dt" value="{{ md5(date('Y') . date('m') . date('d')) }}">
						<div class="form-group donation-form-group">
							<label for="Name">*{!! trans('spende.lastschrift.3') !!}</label>
							<input type="text" class="form-control" id="Name" name="Name" placeholder="{!! trans('spende.lastschrift.3.placeholder') !!}" @if(isset($data['name'])) value="{{$data['name']}}" @endif />
						</div>
						<div class="form-group donation-form-group">
							<label for="email">{!! trans('spende.lastschrift.4') !!}</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="Email" @if(isset($data['email'])) value="{{$data['email']}}" @endif>
						</div>
						<div class="form-group donation-form-group">
							<label for="iban">*{!! trans('spende.lastschrift.6') !!}</label>
							<input type="text" class="form-control" id="iban" name="iban" placeholder="IBAN" @if(isset($data['iban'])) value="{{$data['iban']}}" @endif>
						</div>
						<div class="form-group donation-form-group">
							<label for="bic">{!! trans('spende.lastschrift.7') !!}</label>
							<input type="text" class="form-control" id="bic" name="bic" placeholder="BIC" @if(isset($data['bic'])) value="{{$data['bic']}}" @endif>
						</div>
						<div class="form-group donation-form-group">
							<label for="msg">{!! trans('spende.lastschrift.8.message')!!}</label>
							<p>{!! trans('spende.bankinfo.3')!!}</p>
							<textarea class="form-control" id="msg" name="Nachricht" placeholder="{!! trans('spende.lastschrift.8.message.placeholder') !!}">@if(isset($data['nachricht'])){{$data['nachricht']}}@endif</textarea>
						</div>

					</div>


					<input type="radio" class="payment-radio" name="payment-method" id="ueberweisung" required="required"><label class="payment-label" for="ueberweisung">{!! trans('spende.ueberweisung') !!}</label> <br>

					<div class="bank-transfer" id="direct-payment">
					<p>{!! trans('spende.bankinfo.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.1') !!}</p>
					<p>{!! trans('spende.bankinfo.2.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.3') !!}</p>
					<p>{!! trans('spende.bankinfo.2.4') !!}</p> <br>
					<p>{!! trans('spende.bankinfo.3') !!}</p></div> 


					<input type="radio" class="payment-radio" name="payment-method" id="paypal" required="required"><label class="payment-label" for="paypal">{!! trans('spende.paypal') !!}</label> <br>
					<div class="paypal-method">
						<p>{!! trans('spende.paypal.1') !!}</p></div>

					<div class="section">
					<input type="submit" value="{!! trans('spende.submit') !!}">
				</div>
				</div>	
				</form>
		
			<div class="col-right">
			
				
				<div class="section">
					<p>{!! trans('spende.lastschrift.10') !!}</p> </div>
					<div class="section">
					<h3>{!! trans('spende.member.1') !!}</h3>
					<p>{!! trans('spende.member.2') !!}</p>
					<ul>
					<li>{!! trans('spende.member.3') !!}</li>
					<li>{!! trans('spende.member.4') !!}</li>
					<li>{!! trans('spende.member.5') !!}</li>
					<li>{!! trans('spende.member.6') !!}</li>
					</ul>
					<a href="">{!! trans('spende.member.7') !!}</a>
				</div>	
		</div>
	
	</div>

@endsection
