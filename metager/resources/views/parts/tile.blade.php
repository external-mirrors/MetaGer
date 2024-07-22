<a id="{{ $tile->id }}" href="{{$tile->url}}" target="{{ $tile->target }}"
    class="{{ $tile->advertisement ? "advertisement " : "" }}{{ $tile->classes }}" {{ $tile->advertisement ? 'rel=nofollow target=_blank' : ''}}>
    <div class="image">
        <img src="{{$tile->image}}" alt="{{$tile->image_alt}}" class="{{$tile->image_classes}}">
    </div>
    <div class="title">
        <div class="main-title">{{$tile->title}}</div>
        <div class="sub-title">
            @if($tile->advertisement)
                @lang('tiles.sponsored')
            @endif
        </div>
    </div>
</a>