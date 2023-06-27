<div class="inline-result" data-count="{{ $result->hash }}" data-index="{{$index}}">  
	<a class="result-image" href="{{ $result->link }}" @if($metager->isFramed())target="_top"@endif>
        @if( $result->image !== "" )
		<img src="{{ \App\Http\Controllers\Pictureproxy::generateUrl($result->image) }}" alt="" />
        @else
        <img src="/img/news-placeholder.svg" alt="" />
        @endif
	</a>
    <a class="result-link" href="{{ $result->link }}" title="{{ $result->anzeigeLink }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif target="{{ $metager->getNewtab() }}" tabindex="-1">
		{{ $result->anzeigeLink }}
	</a>
	<a class="result-headline" title="{{$result->titel}}" href="{{ $result->link }}" target="{{ $metager->getNewtab() }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif>
		{!! $result->titel !!}
	</a>
    <div class="age">
    @if(isset($result->age))
    {{ $result->age }}
    @endif
    </div>
</div>