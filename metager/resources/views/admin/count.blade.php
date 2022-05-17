@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<div id="graph">
		
	</div>
	<p>Am ??? zur gleichen Zeit <span class="text-info">???</span> - insgesamt <span class="text-danger">???</span></p>
	<p>Mittelwert der letzten ??? Tage: ???</p>
	<table class="table table-striped">
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
			<tr class="today">
				<td class="date" data-date="{{ (new DateTime('midnight'))->format("Y-m-d") }}">{{ (new DateTime('midnight'))->format("d.m.Y") }}</td>
				<td class="loading same-time"></td>
				<td class="total">-</td>
				<td class="median">-</td>
			</tr>
			@for($i = 1; $i < $days; $i++)
			<tr @if($i === 0)class="today loading"@else class="loading"@endif>
				<td class="date" data-date="{{ (new DateTime('midnight'))->modify("-" . $i . " days")->format("Y-m-d") }}">{{ (new DateTime('midnight'))->modify("-" . $i . " days")->format("d.m.Y") }}</td>
				<td class="loading same-time"></td>
				<td class="loading total"></td>
				<td class="loading median"></td>
			</tr>
			@endfor
		</tbody>
	</table>

@endsection
