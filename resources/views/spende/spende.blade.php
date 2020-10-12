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
					<input type="radio" name="amount" id="5euro"><label for="amount">5€</label> <input type="radio" name="amount" id="15euro"><label for="amount">15€</label>
					<input type="radio" name="amount" id="25euro"><label for="amount">25€</label> <input type="radio" name="amount" id="50euro"><label for="amount">50€</label> <br>
					<input type="radio" name="amount" id="100euro"><label for="amount">100€</label> <input type="radio" name="amount" id="200euro"><label for="amount">200€</label>
					<input type="radio" name="amount" id="250euro"><label for="amount">250€</label> <input type="radio" name="amount" id="300euro"><label for="amount">300€</label> <br>
					 <input placeholder="Wunschbetrag in €" value="">
					<br> 
					<h3> Wie regelmäßig wollen Sie spenden?<h3>
					<input type="radio" name="frequency" id="once"><label for="frequency">Einmalig</label> <br>
					<input type="radio" name="frequency" id="monthly"><label for="frequency">Monatlich</label> <br>
					<input type="radio" name="frequency" id="quarterly"><label for="frequency">Vierteljährlich</label> <br>
					<input type="radio" name="frequency" id="six-monthly"><label for="frequency">Halbjährlich</label> <br>
					<input type="radio" name="frequency" id="annual"><label for="frequency">Jährlich</label> <br>
					<br>
					<h3>Wie wollen Sie spenden?<h3>
					<input type="radio" name="payment-method" id="lastschrift"><label for="frequency">Lastschrift</label> <br>
					<input type="radio" name="payment-method" id="Überweisung"><label for="frequency">Überweisung</label> <br>
					<input type="radio" name="payment-method" id="Paypal" ><label for="frequency">Paypal</label> <br>
					<div class="section">
					<button type="button">Fortfahren</button>
				</div>
				</div>	
			</div>
		
			<div class="col-right">
				<!--<div class="section" id="direct-payment">
					<h3>{!! trans('spende.bankinfo.1') !!}</h3>
					<p>{!! trans('spende.bankinfo.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.1') !!}</p>
					<p>{!! trans('spende.bankinfo.2.2') !!}</p>
					<p>{!! trans('spende.bankinfo.2.3') !!}</p>
					<p>{!! trans('spende.bankinfo.2.4') !!}</p>
				</div>-->
				<div class="section">
					<h3>Oder doch lieber Mitglied werden?</h3>
					<p>Es kostet nicht mehr und bietet viele Vorteile.</p>
					<a href="www.suma-ev.de/mitglieder">Vorteile</a> <a href="">Antragsformular</a>
				</div>
				<div class="section">
					<p>{!! trans('spende.lastschrift.10') !!}</p> </div>
		</div>
	</div>
@endsection
