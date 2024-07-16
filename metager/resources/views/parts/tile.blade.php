<a href="{{$url}}">
    <div class="image">
        <img src="{{$image}}" alt="{{$image_alt}}" @if(isset($options) && array_key_exists("img_class",$options))class="{{$options["img_class"]}}"@endif>
    </div>
    <div class="title">{{$title}}</div>
</a>