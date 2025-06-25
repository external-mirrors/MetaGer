@foreach(app()->make(\App\Searchengines::class)->available_foki as $fokus)
<div id="{{$fokus}}" @if(app()->make(\App\SearchSettings::class)->fokus === $fokus)class="active"@endif>
	<a href="@if(app()->make(\App\SearchSettings::class)->fokus === $fokus)#@else{{ route("resultpage", array_merge(request()->only("eingabe", "key"), ["focus" => $fokus])) }}@endif" target="_self" @if(app()->make(\App\SearchSettings::class)->fokus === $fokus)aria-current="page"@endif>{{ trans("index.foki.$fokus") }}</a>
</div>
@endforeach
<div id="maps">
	<a href="https://maps.metager.de/{{ rawurlencode(app(\App\SearchSettings::class)->q) }}/guess?locale={{ App\Localization::getLanguage() }}" target="_blank">
		Maps
	</a>
</div>
