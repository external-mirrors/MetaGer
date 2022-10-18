@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
<h1 class="page-title">{{ trans('prevention-information.head.1') }}</h1>
</div>
<div class="card">
	<h2>{{ trans('prevention-information.head.2') }}</h2>
	<p>{{ trans('prevention-information.text.1') }}</p>

</div>

@endsection