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
			<div class="col-left">
				<div class="section">
					<h3 id="lastschrift">Welchen Beitrag möchten Sie spenden?</h3>
						<div class="amount-row">
						<input type="radio" class="amount-radio" name="amount" id="amount-5euro"> <label for="amount-5euro" class="amount-label">5€</label> 
						<input type="radio" class="amount-radio" name="amount" id="amount-10euro"><label for="amount-10euro" class="amount-label">10€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-15euro"><label for="amount-15euro" class="amount-label">15€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-20euro"><label for="amount-20euro" class="amount-label">20€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-25euro"><label for="amount-25euro" class="amount-label">25€</label> <br>
						</div>
						<div class="amount-row">
						<input type="radio" class="amount-radio" name="amount" id="amount-50euro"><label for="amount-50euro" class="amount-label">50€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-100euro"><label for="amount-100euro" class="amount-label">100€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-200euro"><label for="amount-200euro" class="amount-label">200€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-250euro"><label for="amount-250euro" class="amount-label">250€</label>
						<input type="radio" class="amount-radio" name="amount" id="amount-300euro"><label for="amount-300euro" class="amount-label">300€</label> <br>
						</div>
						<div>
						<input type="radio" name="amount" id="amount-custom"><label for="amount-custom" class="amount-custom">Wunschbetrag</label> <input id="custom-amount" type="number" step=".01" placeholder="Betrag in €" value="">
						</div>
					
					
					
					<br> 
					<h3> Wie regelmäßig wollen Sie spenden?<h3>

						<input type="radio" class="frequency-radio" name="frequency" id="once"><label class="frequency-label" for="once">Einmalig</label> <br>

						<input type="radio" class="frequency-radio" name="frequency" id="monthly"><label class="frequency-label" for="monthly">Monatlich</label> <br>


						<input type="radio" class="frequency-radio" name="frequency" id="quarterly"><label class="frequency-label" for="quarterly">Vierteljährlich</label> <br>

						<input type="radio" class="frequency-radio" name="frequency" id="six-monthly"><label class="frequency-label" for="six-monthly">Halbjährlich</label> <br>

						<input type="radio" class="frequency-radio" name="frequency" id="annual"><label class="frequency-label" for="annual">Jährlich</label> <br>

					<br>
					<h3>Wie wollen Sie spenden?<h3>

					<input type="radio" class="payment-radio" name="payment-method" id="lastschrift"><label class="payment-label" for="lastschrift">Lastschrift</label><br>

					<div class="sepa-debit-details">blaaarg</div>


					<input type="radio" class="payment-radio" name="payment-method" id="ueberweisung"><label class="payment-label" for="ueberweisung">Überweisung</label> <br>

					<div class="bank-transfer" id="direct-payment">
					<p>{!! trans('spende.bankinfo.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.1') !!}</p>
					<p>{!! trans('spende.bankinfo.2.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.3') !!}</p>
					<p>{!! trans('spende.bankinfo.2.4') !!}</p></div>


					<input type="radio" class="payment-radio" name="payment-method" id="paypal" ><label class="payment-label" for="paypal">Paypal</label> <br>

					<div class="section">
					<button type="button">Fortfahren</button>
				</div>
				</div>	
			</div>
		
			<div class="col-right">
				<!--<div 
				</div>-->
				
				<div class="section">
					<p>{!! trans('spende.lastschrift.10') !!}</p> </div>
					<div class="section">
					<h3>Oder doch lieber Mitglied werden?</h3>
					<p>Es kostet nicht mehr und bietet viele Vorteile.</p>
					<ul>
					<li>Werbefreie Nutzung von MetaGer</li>
					<li>Förderung der Suchmaschine MetaGer</li>
					<li>Mitgliedsbeitrag steuerlich absetzbar</li>
					<li>Mitbestimmungsrechte im Verein</li>
					</ul>
					<a href="">Antragsformular</a>
				</div>	
		</div>
	
	</div>

@endsection
