@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="blacklist-container">
    <div class="card blacklist">
        <h3><a href="#">Blacklist (?)</a></h3>
        <div class="skeleton"></div>
        <ul class="blacklist-items">
        </ul>
    </div>
    <div class="card whitelist">
        <h3><a href="#">Whitelist (?)</a></h3>
        <div class="skeleton"></div>
        <ul class="blacklist-items">
        </ul>
    </div>
</div>
<div id="affilliate-clicks" class="card">
    <input type="text" name="filter" id="filter" placeholder="Filter Results">
    <div class="skeleton"></div>
</div>
@endsection
