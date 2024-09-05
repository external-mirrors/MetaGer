@extends('layouts.subPages')

@section('title', $title)

@section('content')
<img class="hero" src="/img/advertising/search.jpg" alt="Image of a searchbar">
<div id="content">
    <h1>@lang("advertising/overview.heading")</h1>
    <div id="keywords" class="teaser">
        <img src="/img/advertising/marketing.jpg" alt="Image of marketing statistics">
        <div class="text-container">
            <h2>@lang("advertising/overview.keywords.heading")</h2>
            <div>@lang("advertising/overview.keywords.description")</div>
        </div>
    </div>
    <div id="privacy" class="teaser">
        <div class="text-container">
            <h2>@lang("advertising/overview.privacy.heading")</h2>
            <div>@lang("advertising/overview.privacy.description")</div>
        </div>
        <img src="/img/App.svg" alt="Image of marketing statistics">
    </div>
    <div id="free-for-all" class="teaser">
        <img src="/img/svg-icons/heart.svg" alt="Image of a heart">
        <div class="text-container">
            <h2>@lang("advertising/overview.free_for_all.heading")</h2>
            <div>@lang("advertising/overview.free_for_all.description")</div>
        </div>
    </div>
    <div class="call-to-action">
        <a class="btn btn-default" href="#">@lang("advertising/overview.call_to_action")</a>
    </div>
</div>
@endsection