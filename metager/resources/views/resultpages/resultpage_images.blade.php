@extends('layouts.resultPage', ['js' => [mix('/js/imagesearch.js')]])

@section('results')
    @include('resultpages.results_images')
@endsection
