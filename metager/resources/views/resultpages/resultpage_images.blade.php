@extends('layouts.resultPage', ['js' => [Vite::asset('resources/js/imagesearch.js')]])

@section('results')
    @include('resultpages.results_images')
@endsection
