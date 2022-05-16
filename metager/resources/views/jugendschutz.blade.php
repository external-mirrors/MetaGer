@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('jugendschutz.title') !!}</h1>
<div class="card">
	<p>{!! trans('jugendschutz.text') !!}</p>
</div>
@endsection