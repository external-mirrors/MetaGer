@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">{{ trans('zitatsuche.title') }}</h1>
<div class="card">
	<p>{{ trans('zitatsuche.subtitle') }}</p>
	<form id="searchForm" class="form-inline" accept-charset="UTF-8">
		<input type="text" class="form-control search-input-mini" id="q" name="q" placeholder="Suchworte" value="{{ $q }}"><button type="submit" class="search-btn-mini"><img id="zitatsuche-search-icon" class="mg-icon" src="/img/icon-lupe.svg" alt="{{ trans('icon-lupe.alt') }}"></button>
	</form>
	@if($q !== "")
	<hr />
	<h3>{{ trans('zitatsuche.results-label') }} "{{$q}}":</h3>
	@foreach($results as $author => $quotes)
	<b><a href="{{ action('MetaGerSearch@search', ['eingabe' => $author, 'focus' => 'web', 'encoding' => 'utf8', 'lang' => 'all']) }}" target="_blank">{{$author}}</a>:</b>
	<ul class="list-unstyled">
		@foreach($quotes as $quote)
		<li>
			<quote>"{{ $quote }}"</quote>
		</li>
		@endforeach
	</ul>
	@endforeach
	@endif
</div>
@endsection