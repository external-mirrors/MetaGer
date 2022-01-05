<div class="image" data-width="{{$result->imageDimensions['width']}}" data-height="{{$result->imageDimensions['height']}}">
	<a href="{{ $result->link }}" target="_blank">
		<div title="{{ $result->titel }}">
			<img src="{{ \App\Models\parserSkripte\BingBilder::generateThumbnailUrl($result) }}" alt="{{ $result->titel }}" />
			<!--<div>{{ $result->gefVon[0] }}</div>-->
		</div>
	</a>
</div>