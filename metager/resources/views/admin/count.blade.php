@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="graph">
	<canvas id="chart" width="100%" ></canvas>
</div>
<p class="record">Am <span class="record-date loading"></span> zur gleichen Zeit <span class="record-same-time text-info loading"></span> - insgesamt <span class="record-total text-danger loading"></span></p>
<p class="total-median">Mittelwert der letzten <span class="median-days loading"></span> Tage: <span class="median-value loading"></span></p>
<table id="data-table" class="table table-striped" data-interface="{{ $interface }}">
	<caption>
		<form method="GET" style="display: flex; align-items: center;">
			<div id="daterange" class="form-group" style="max-width: 100px; margin-right: 8px;">
				<label for="days">Zeitraum (von/bis)</label>
				<input class="form-control" type="date" id="start" name="start" value="{{$start->format("Y-m-d")}}" max="{{$end->format('Y-m-d')}}"
				@if((clone $start)->addDays(28)->isToday())
				form="unused"
				@endif/>
				<input class="form-control" type="date" id="end" name="end" value="{{$end->format("Y-m-d")}}" min="{{$start->format("Y-m-d")}}" max="{{Carbon::createMidnightDate()->format('Y-m-d')}}" 
				@if($end->isToday())
				form="unused"
				@endif/>
			</div>
			<div class="form-group" style="max-width: 100px; margin-right: 8px;">
				<label for="interface">Sprache</label>
				<select class="form-control" name="interface" id="interface">
					<option value="all" {{ (Request::input('interface', 'all') == "all" ? "selected" : "")}}>Alle</option>
					<option value="none-german" {{ (Request::input('interface', 'all') == "none-german" ? "selected" : "")}}>Nicht Deutsch</option>
					<option value="none-german-english" {{ (Request::input('interface', 'all') == "none-german-english" ? "selected" : "")}}>Nicht Deutsch oder Englisch</option>
					<option value="de" {{ (Request::input('interface', 'all') == "de" ? "selected" : "")}}>DE</option>
					<option value="at" {{ (Request::input('interface', 'all') == "at" ? "selected" : "")}}>AT</option>
					<option value="pl" {{ (Request::input('interface', 'all') == "pl" ? "selected" : "")}}>PL</option>
					<option value="ch" {{ (Request::input('interface', 'all') == "ch" ? "selected" : "")}}>CH</option>
					<option value="en" {{ (Request::input('interface', 'all') == "en" ? "selected" : "")}}>EN</option>
					<option value="es" {{ (Request::input('interface', 'all') == "es" ? "selected" : "")}}>ES</option>
					<option value="fr" {{ (Request::input('interface', 'all') == "fr" ? "selected" : "")}}>FR</option>
					<option value="da" {{ (Request::input('interface', 'all') == "da" ? "selected" : "")}}>DA</option>
					<option value="fi" {{ (Request::input('interface', 'all') == "fi" ? "selected" : "")}}>FI</option>
					<option value="it" {{ (Request::input('interface', 'all') == "it" ? "selected" : "")}}>IT</option>
					<option value="nl" {{ (Request::input('interface', 'all') == "nl" ? "selected" : "")}}>NL</option>
					<option value="sv" {{ (Request::input('interface', 'all') == "sv" ? "selected" : "")}}>SV</option>
				</select>
			</div>
			<div id="refresh" style="margin-top: 11px; margin-right: 8px;">
				<button type="submit" class="btn btn-sm btn-default">Aktualisieren</button>
			</div>
			<!--
			<div id="export" style="margin-top: 11px;">
				<button type="submit" name="out" value="csv" class="btn btn-sm btn-default">Als CSV exportieren</button>
			</div>
			-->
		</form>
	</caption>
	<thead>
		<tr>
			<th>Datum</th>
			<th>Suchanfragen zur gleichen Zeit</th>
			<th>Suchanfragen insgesamt</th>
			<th>Mittelwert</th>
		</tr>
	</thead>
	<tbody>
		@php
		$date_iterator = clone $end;
		@endphp
		@while($date_iterator->isBetween($start, $end))
		<tr class="@if($date_iterator->weekday() === now()->weekday())same-weekday @endif loading" data-date="{{$date_iterator->format("Y-m-d")}}">
			<td class="date" data-date="{{ $date_iterator->format("Y-m-d") }}" data-date_formatted="{{ $date_iterator->format("d.m.Y")}}">{{ $date_iterator->locale("de_DE")->translatedFormat("d.m.Y - l") }}</td>
			<td class="same-time" data-same_time="0"></td>
			<td class="total"></td>
			<td class="median"></td>
		</tr>
		@php
		$date_iterator->subDay();
		@endphp
		@endwhile
	</tbody>
</table>

@endsection