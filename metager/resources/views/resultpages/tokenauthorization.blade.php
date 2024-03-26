@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <input type="hidden" id="tokencost" value="{{$cost}}">
    <input type="hidden" id="resultpage" value="{{$resultpage}}">
@endsection