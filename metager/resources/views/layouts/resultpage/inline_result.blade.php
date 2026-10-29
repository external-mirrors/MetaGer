<div class="inline-result" data-count="{{ $result->hash }}" data-index="{{$index}}">  
	{{-- The picture repeats the headline link below it, like .result-link: out of
	     the tab order and out of the accessibility tree, so the result is one stop
	     with one name instead of three, the first of them nameless. --}}
	<a class="result-image" href="{{ $result->link }}" tabindex="-1" aria-hidden="true" @if($metager->isFramed())target="_top"@endif>
        @if( $result->image !== "" )
		<img src="{{ \App\Http\Controllers\Pictureproxy::generateUrl($result->image) }}" alt="" />
        @else
        <img src="/img/news-placeholder.svg" alt="" />
        @endif
	</a>
    <a class="result-link" href="{{ $result->link }}" title="{{ $result->anzeigeLink }}" @if($metager->getNewtab() === "_blank")rel="noopener"@endif target="{{ $metager->getNewtab() }}" tabindex="-1" aria-hidden="true">
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