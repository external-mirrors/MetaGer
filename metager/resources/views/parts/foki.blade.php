@foreach(app()->make(\App\Searchengines::class)->available_foki as $fokus)
<div id="{{$fokus}}" @if($metager->getFokus() === $fokus)class="active"@endif>
	<a href="@if($metager->getFokus() === $fokus)#@else{!!$metager->generateSearchLink($fokus)!!}@endif" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_self"@endif @if($metager->getFokus() === $fokus)aria-current="page"@endif>{{ trans("index.foki.$fokus") }}</a>
</div>
@endforeach
<div id="maps">
	<a href="https://maps.metager.de/{{ rawurlencode(app(\App\SearchSettings::class)->q) }}/guess?locale={{ App\Localization::getLanguage() }}" @if(!empty($metager) && $metager->isFramed())target="_top" @else target="_blank"@endif>
		Maps
	</a>
</div>
