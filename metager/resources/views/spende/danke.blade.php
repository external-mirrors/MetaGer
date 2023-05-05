@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<div id="donation-data" class="card">
	<h2>{{ trans('spende.danke.title') }}</h2>
	<p style="width:100%;" class="text-muted">{{ trans('spende.danke.nachricht') }}</p>
	<h3>{{ trans('spende.danke.kontrolle') }}</h3>
	<div>
		@if($data["person"] === "private")
		<div class="data-element">
			<label for="firstname" style="margin-right: 16px;">{{ trans('spende.lastschrift.3f.placeholder')}}</label>
			<input type="text" name="firstname" id="firstname" value="{{ $data['firstname'] }}" readonly>
		</div>
		<div class="data-element">
			<label for="lastname" style="margin-right: 16px;">{{ trans('spende.lastschrift.3l.placeholder')}}</label>
			<input type="text" name="lastname" id="lastname" value="{{ $data['lastname'] }}" readonly>
		</div>
		@else
		<div class="data-element">
			<label for="company" style="margin-right: 16px;">{{ trans('spende.lastschrift.3c.placeholder')}}</label>
			<input type="text" name="company" id="company" value="{{ $data['company'] }}" readonly>
		</div>
		@endif
		@if(!empty($data['email']))
		<div class="data-element">
			<label for="email" style="margin-right: 16px;">Email</label>
			<input type="text" name="email" id="email" value="{{ $data['email'] }}" readonly>
		</div>
		@endif
		@if(!empty($data['iban']))
		<div class="data-element">
			<label for="iban" style="margin-right: 16px;">{{ trans('spende.iban') }}</label>
			<input type="text" name="iban" id="iban" value="{{ $data['iban'] }}" readonly>
		</div>
		@endif
		@if(!empty($data["bic"]))
		<div class="data-element">
			<label for="bic" style="margin-right: 16px;">{{ trans('spende.bic') }}</label>
			<input type="text" name="bic" id="bic" value="{{ $data['bic'] }}" readonly>
		</div>
		@endif
		<div class="data-element">
			<label for="betrag" style="margin-right: 16px;">{{ trans('spende.betrag') }}</label>
			<input type="text" name="betrag" id="betrag" value="{{ $data['betrag'] }} €" readonly>
		</div>
		@if(!empty($data['frequency']))
		<div class="data-element">
			<label for="frequency" style="margin-right: 16px;">{{ trans('spende.frequency.name') }}</label>
			<input type="text" name="frequency" id="frequency" value="{{ trans('spende.frequency.' . $data['frequency']) }}" readonly>
		</div>
		@endif
		<div class="data-element">
			<label for="nachricht" style="margin-right: 16px;">{{ trans('spende.danke.message') }}</label>
			<textarea name="nachricht" id="nachricht" readonly>{{ $data['nachricht'] }}</textarea>
		</div>
	</div>
	<button type="button" style="margin-top: 16px; margin-bottom: 16px;" class="btn btn-primary noprint print-button js-only">{{ trans('spende.drucken') }}</button>
</div>
@endsection