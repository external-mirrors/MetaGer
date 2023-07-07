@extends('layouts.subPages', [])

@section('title', $title )

@section('content')
<h1>{{ __("newDefaultSetting.heading") }}</h1>
<div>@lang("newDefaultSetting.brave")</div>
<div>@lang("newDefaultSetting.bing")</div>
<div>@lang("newDefaultSetting.advantages")</div>
<div>@lang("newDefaultSetting.settings", ["settingslink" => route('settings', ["focus" => "web"])])</div>
@endsection