<label class="image" for="result-checkbox-{{ $index }}" data-width="{{ $result->image->thumbnail_width }}"
    data-height="{{ $result->image->thumbnail_height }}">
    <div title="{{ $result->titel }}" class="image">
        <img src="{{ $result->image->thumbnail_proxy }}" alt="{{ $result->titel }}" loading="lazy" fetchpriority="low"
            width="{{ $result->image->thumbnail_width }}" height="{{ $result->image->thumbnail_height }}" />
        <!--<div>{{ $result->gefVon[0] }}</div>-->
    </div>
    <div class="title" title="{{ $result->titel }}">{{ $result->titel }}</div>
</label>
