@extends('layouts.subPages')

@section('title', $title )

@section('content')

   <form method="get">
        <input type="text" name="bv_key" id="bv_key" placeholder="Enter BV Key" value="{{ $bv_key }}">
        <input type="submit" value="submit" >
   </form>

   @if(!empty($bv_data))
   <code>
{{ \json_encode($bv_data, JSON_PRETTY_PRINT) }}
   </code>
   @endif
@endsection
