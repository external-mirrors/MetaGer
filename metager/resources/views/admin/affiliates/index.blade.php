@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div id="blacklist-container">
    <div class="card blacklist">
        <h3><a href="#">Blacklist (?)</a></h3>
        <div class="skeleton"></div>
        <div class="blacklist-items">
        </div>
        <div class="actions pagination">
            <a href="#" class="backward">&lt; Last Page</a>
            <a href="#" class="forward">Next Page &gt;</a>
        </div>
    </div>
    <div class="card whitelist">
        <h3><a href="#">Whitelist (?)</a></h3>
        <div class="skeleton"></div>
        <div class="whitelist-items">
        </div>
        <div class="actions pagination">
            <a href="#" class="backward">&lt; Last Page</a>
            <a href="#" class="forward">Next Page &gt;</a>
        </div>
    </div>
</div>
<div id="affilliate-clicks" class="card">
    <input type="text" name="filter" class="filter" placeholder="Filter Results">
    <div class="heading">
        <div class="host-count">
            ? Hosts
        </div>
        <div class="click-count">
            ? Clicks
        </div>
    </div>
    <div class="skeleton"></div>
    <div class="host-list">

    </div>
    <div class="pagination">
        <a href="#" class="backward">&lt; Last Page</a>
        <div class="current-page">?/?</div>
        <a href="#" class="forward">Next Page &gt;</a>
    </div>
</div>
@endsection