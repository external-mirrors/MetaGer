<link rel="preload" as="image" href="{{ $metager->getResults()[0]->image->image_proxy }}">
<div class="image-container">
    @foreach ($metager->getResults() as $index => $result)
        @include('layouts.image_result', [
            'index' => $index,
            'result' => $result,
        ])
    @endforeach
</div>

<div class="image-details">
    @for ($i = sizeof($metager->getResults()) - 1; $i >= 0; $i--)
        @php
            $result = $metager->getResults()[$i];
        @endphp
        <div class="details @if ($i === 0) default @endif" id="result-{{ $i }}">
            <img src="{{ $result->image->image_proxy }}" alt="{{ $result->titel }}" fetchpriority="high">
            <div class="details">
                <h3 class="title">{{ $result->titel }}</h3>
                <a class="link" href="{{ $result->link }}" target="{{ $metager->getNewtab() }}"
                    @if ($metager->getNewtab() === '_blank') rel="noopener" @endif>{{ $result->anzeigeLink }}</a>
                <div class="actions">
                    <a href="{{ $result->image->image_proxy }}" target="_blank"
                        class="btn btn-default btn-sm">@lang('result.image.download')</a>
                    @if (sizeof($result->gefVon) === 1)
                        <a class="result-hoster" href="{{ $result->gefVonLink[0] }}"
                            target="{{ $metager->getNewtab() }}"
                            @if ($metager->getNewtab() === '_blank') rel="noopener" @endif
                            tabindex="-1">{{ trans('result.gefVon') . ' ' . $result->gefVon[0] }} </a>
                    @else
                        <span title="{{ implode(', ', $result->gefVon) }}" class="result-hoster" tabindex="0">
                            {{ trans('result.gefVon') . ' ' . sizeof($result->gefVon) . ' ' . trans('result.providers') }}
                            <ul class="card">
                                @foreach ($result->gefVon as $index => $gefVon)
                                    <li><a class="result-hoster" href="{{ $result->gefVonLink[$index] }}"
                                            target="{{ $metager->getNewtab() }}" rel="noopener"
                                            tabindex="-1">{{ trans('result.gefVon') . ' ' . $result->gefVon[$index] }}
                                        </a></li>
                                @endforeach
                            </ul>
                        </span>
                    @endif
                </div>
                <div class="copyright">@lang('result.image.copyright')</div>
            </div>
        </div>
    @endfor
</div>

@include('parts.pager')
@if (!app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
    <div id="external-search">
        <h3>@lang('results.images.external.heading')</h3>
        <div class="texts">
            <div>@lang('results.images.external.description')</div>
        </div>
        <div class="external-links">
            <a href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}"
                class="btn btn-primary">@lang('results.images.external.buy')</a>
            <div class="divider">@lang('results.images.external.or')</div>
            <form id="external-engines-form" class="external-engines" method="POST">
                @php
                    $expiration = now()->addHour(1);
                @endphp
                <input type="hidden" name="expiration" value="{{ $expiration }}">
                <input type="hidden" name="signature"
                    value="{{ hash_hmac('sha256', $expiration, config('app.key')) }}">
                <button type="submit" name="bilder_setting_external" value="google"
                    class="btn btn-default">@lang('results.images.external.google')</button>
                <button type="submit" name="bilder_setting_external" value="bing"
                    class="btn btn-default">@lang('results.images.external.bing')</button>
            </form>
            <div class="spacer"></div>
            <div class="input-group">
                <input type="checkbox" name="save-external-engine" id="save-external-engine"
                    form="external-engines-form" value="1">
                <label for="save-external-engine">@lang('results.images.external.save')</label>
            </div>
        </div>
    </div>
@endif
