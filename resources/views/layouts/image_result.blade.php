@php
$imageData = \App\Models\parserSkripte\BingBilder::generateThumbnailUrl($result)
@endphp
<div class="image height-{{$imageData['height-multiplier']}}" data-width="{{$result->imageDimensions['width']}}" data-height="{{$result->imageDimensions['height']}}">
	<a href="{{ $result->link }}" target="_blank">
		<div title="{{ $result->titel }}">
			<img src="{{ $imageData['link'] }}" alt="{{ $result->titel }}" />
			<!--<div>{{ $result->gefVon[0] }}</div>-->
		</div>
	</a>
</div>