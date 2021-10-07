@if (sizeof($quicktip->details) > 0)
<details open="open">
  <summary class="quicktip-summary">
    <div class="quicktip-headline">
      @if ($quicktip->title != "")
      <h1 class="quicktip-title">
        @if ($quicktip->link != "")
        <a href="{{ $quicktip->link }}" target="_parent">{{ $quicktip->title }}</a>
        @else
        {{ $quicktip->title }}
        @endif
      </h1>
      @endif
      <div class="space-taker"></div>
      @if ($quicktip->gefVonLink != "")
      <a class="quicktip-hoster" href="{{ $quicktip->gefVonLink }}" target="_blank" rel="noopener">
        von
        @if ($quicktip->gefVonTitle != "")
        {{ $quicktip->gefVonTitle }}
        @else
        {{ $quicktip->gefVonTitle }}
        @endif
      </a>
      @endif
      <img class="mg-icon mg-icon-rot180" src="/img/chevron-down.svg" alt="{{ trans('chevron-down.alt') }}">
    </div>
    <p class="quicktip-description">{!! $quicktip->descr !!}</p>
    @if ($quicktip->author != "")
    <span class="author">{{ $quicktip->author }}</span>
    @endif
  </summary>
  @foreach ($quicktip->details as $detail)
  <div class="quicktip-detail">
    <h2>
      @if (isset($detail->link))
      <a href="{{ $detail->link }}" target="_parent">{{ $detail->title }}</a>
      @else
      {{ $detail->title }}
      @endif
    </h2>
    <p>{!! $detail->descr !!}</p>
  </div>
  @endforeach
</details>
@else
<div class="quicktip-summary">
  <div class="quicktip-headline">
    @if ($quicktip->title != "")
    <h1 class="quicktip-title">
      @if ($quicktip->link != "")
      <a href="{{ $quicktip->link }}" target="_parent">{{ $quicktip->title }}</a>
      @else
      {{ $quicktip->title }}
      @endif
    </h1>
    <div class="space-taker"></div>
    @if ($quicktip->gefVonLink != "")
    <a class="quicktip-hoster" href="{{ $quicktip->gefVonLink }}" target="_blank" rel="noopener">
      von
      @if ($quicktip->gefVonTitle != "")
      {{ $quicktip->gefVonTitle }}
      @else
      {{ $quicktip->gefVonTitle }}
      @endif
    </a>
    @endif
    @endif
  </div>
  <p>{!! $quicktip->descr !!}</p>
  @if ($quicktip->author != "")
  <span class="author">{{ $quicktip->author }}</span>
  @endif
</div>
@endif