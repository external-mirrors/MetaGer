@foreach($metager->getSumaFile()->foki as $name => $fokus)
<div id="{{$name}}" @if($metager->getFokus() === $name)class="active"@endif>
	<a href="@if($metager->getFokus() === $name)#@else{!!$metager->generateSearchLink($name)!!}@endif" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_self"@endif tabindex="0">@lang($fokus->{"display-name"})</a>
</div>
@endforeach
@if (App\Localization::getLanguage() == "de")
<div id="maps">
	<a href="https://maps.metager.de/map/{{ urlencode($metager->getQ()) }}/9.7380161,52.37119740000003,12" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_blank"@endif>
		Maps
	</a>
</div>
@endif