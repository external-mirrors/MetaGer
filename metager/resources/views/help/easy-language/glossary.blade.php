@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div id="team">
	<h1 class="page-title">{!! trans('help/easy-language/glossary.title') !!}</h1>
	<div class="card">
			<li>
				<p>{!! trans('help/easy-language/glossary.entry.1') !!}
			</li>
			<li>
				<p>{!! trans('help/easy-language/glossary.entry.1') !!}
			</li>
			<li>
				<p>{!! trans('help/easy-language/glossary.entry.1') !!}
			</li>
			<li>
				<p>{!! trans('help/easy-language/glossary.entry.1') !!}
			</li>
			<li>
				<p>{!! trans('help/easy-language/glossary.entry.1') !!}
			</li>




		</ul>
	</div>
</div>
@endsection