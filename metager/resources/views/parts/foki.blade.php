@foreach(app()->make(\App\Searchengines::class)->available_foki as $fokus)
<div id="{{$fokus}}" @if($metager->getFokus() === $fokus)class="active"@endif>
	<a href="@if($metager->getFokus() === $fokus)#@else{!!$metager->generateSearchLink($fokus)!!}@endif" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_self"@endif tabindex="0">{{ trans("index.foki.$fokus") }}</a>
</div>
@endforeach
@if (App\Localization::getLanguage() == "de")
<div id="maps">
	<a href="https://maps.metager.de/map/{{ urlencode($metager->getQ()) }}/9.7380161,52.37119740000003,12" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_blank"@endif>
		Maps
	</a>
</div>
@endif