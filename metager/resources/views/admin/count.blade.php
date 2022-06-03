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
			<div class="form-group" style="max-width: 100px; margin-right: 8px;">
				<label for="days">Tage</label>
				<input class="form-control" type="number" id="days" name="days" value="{{$days}}" />
			</div>
			<div class="form-group" style="max-width: 100px; margin-right: 8px;">
				<label for="interface">Sprache</label>
				<select class="form-control" name="interface" id="interface">
					<option value="all" {{ (Request::input('interface', 'all') == "all" ? "selected" : "")}}>Alle</option>
					<option value="de" {{ (Request::input('interface', 'all') == "de" ? "selected" : "")}}>DE</option>
					<option value="en" {{ (Request::input('interface', 'all') == "en" ? "selected" : "")}}>EN</option>
					<option value="es" {{ (Request::input('interface', 'all') == "es" ? "selected" : "")}}>ES</option>
				</select>
			</div>
			<div id="refresh" style="margin-top: 11px; margin-right: 8px;">
				<button type="submit" class="btn btn-sm btn-default">Aktualisieren</button>
			</div>
			<div id="export" style="margin-top: 11px;">
				<button type="submit" name="out" value="csv" class="btn btn-sm btn-default">Als CSV exportieren</button>
			</div>
		</form>
	</caption>
	<thead>
		<tr>
			<th>Datum</th>
			<th>Suchanfragen zur gleichen Zeit</th>
			<th>Suchanfragen insgesamt</th>
			<th>Mittelwert (bis zum jeweiligen Tag zurück)</th>
		</tr>
	</thead>
	<tbody>
		@for($i = 0; $i < $days; $i++) <tr class="{{ $i % 7 === 0 ? 'same-day' : ''}}" data-days_ago="{{$i}}">
			@php
			$date = (new Carbon())->subDays($i);
			@endphp
			<td class="date" data-date="{{ $date->format("Y-m-d") }}" data-date_formatted="{{ $date->format("d.m.Y")}}">{{ (new Carbon())->locale("de_DE")->subDays($i)->translatedFormat("d.m.Y - l") }}</td>
			<td class="loading same-time"></td>
			<td class="loading total"></td>
			<td class="loading median"></td>
			</tr>
			@endfor
	</tbody>
</table>

@endsection