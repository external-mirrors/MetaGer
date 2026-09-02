{{--
    One fokus' worth of settings (engines / filter / blacklist), rendered as
    the content of the active tab pane in settings/index.blade.php.
    Expects: $fokus (string key), $data (array, see SettingsController::index()), $url
--}}
<div class="subblock">
    <h3 class="block-title" id="{{ $fokus }}-engines">@lang('settings.header.2')</h3>
    <p class="help">@lang('settings.text.2')</p>

    <div class="engine-grid">
        @foreach ($data['sumas'] as $name => $suma)
            @if ($suma->configuration->disabled === false)
                <form action="{{ route('disableEngine') }}" method="post" class="pill-form">
                    @include('parts.carry-key')
                    <input type="hidden" name="suma" value="{{ $name }}">
                    <input type="hidden" name="focus" value="{{ $fokus }}">
                    <input type="hidden" name="url" value="{{ $url }}">
                    <button type="submit" class="pill pill-on"
                        title="@lang('settings.disable-engine')"
                        aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.1')">{{ $suma->getDisplayName(true) }}
                    </button>
                </form>
            @endif
        @endforeach
        @if (in_array(\App\Models\DisabledReason::USER_CONFIGURATION, $data['disabledReasons']) || in_array(\App\Models\DisabledReason::SUMAS_DEFAULT_CONFIGURATION, $data['disabledReasons']))
            @foreach ($data['sumas'] as $name => $suma)
                @if (
                    $suma->configuration->disabled &&
                        (in_array(\App\Models\DisabledReason::USER_CONFIGURATION, $suma->configuration->disabledReasons) || in_array(\App\Models\DisabledReason::SUMAS_DEFAULT_CONFIGURATION, $suma->configuration->disabledReasons)) &&
                        sizeof($suma->configuration->disabledReasons) === 1)
                    <form action="{{ route('enableEngine') }}" method="post" class="pill-form">
                        @include('parts.carry-key')
                        <input type="hidden" name="suma" value="{{ $name }}">
                        <input type="hidden" name="focus" value="{{ $fokus }}">
                        <input type="hidden" name="url" value="{{ $url }}">
                        <button type="submit" class="pill pill-off"
                            title="@lang('settings.enable-engine')"
                            aria-label="{{ $suma->configuration->infos->displayName }} @lang('settings.aria.label.2')">{{ $suma->getDisplayName(true) }}
                        </button>
                    </form>
                @endif
            @endforeach
        @endif
    </div>

    @unless ($data['hasEnabledEngine'])
        <p class="notice">@lang('settings.no-engines')</p>
    @endunless

    @if (in_array(\App\Models\DisabledReason::INCOMPATIBLE_FILTER, $data['disabledReasons']))
        <p class="group-note">@lang('settings.disabledByFilter')</p>
        <div class="engine-grid">
            @foreach ($data['sumas'] as $name => $suma)
                @if ($suma->configuration->disabled && in_array(\App\Models\DisabledReason::INCOMPATIBLE_FILTER, $suma->configuration->disabledReasons))
                    <span class="pill pill-locked" title="@lang('settings.filtered-engine')">{{ $suma->getDisplayName(true) }}</span>
                @endif
            @endforeach
        </div>
    @endif

    @if (in_array(\App\Models\DisabledReason::PAYMENT_REQUIRED, $data['disabledReasons']))
        <p class="group-note">@lang('settings.disabledBecausePaymentRequired', ['link' => app(\App\Models\Authorization\Authorization::class)->getAdfreeLink()])</p>
        <div class="engine-grid">
            @foreach ($data['sumas'] as $name => $suma)
                @if ($suma->configuration->disabled && in_array(\App\Models\DisabledReason::PAYMENT_REQUIRED, $suma->configuration->disabledReasons))
                    <span class="pill pill-locked" title="@lang('settings.payment-engine')">{{ $suma->getDisplayName(true) }}</span>
                @endif
            @endforeach
        </div>
    @endif

    @if ($data['searchCost'] > 0)
        <p class="cost">@lang('settings.cost.total', ['cost' => $data['searchCost']])
            @if ($data['rawSearchCost'] < 1)
                <span class="min-note text-warning">@lang('settings.cost.minimum', ['min' => 1])</span>
            @endif
        </p>
    @else
        <p class="cost">@lang('settings.cost-free')</p>
    @endif
    @if(array_key_exists("yahoo", $data['sumas']) && $data['sumas']["yahoo"]->configuration->disabled === false)
        <p class="help">@lang('settings.hint.yahoo')</p>
    @endif
</div>

<div class="subblock">
    <h3 class="block-title" id="{{ $fokus }}-filter">@lang('settings.header.3')</h3>
    <p class="help">@lang('settings.text.3')</p>
    <form action="{{ route('enableFilter') }}" method="post" class="form filter-form">
        @include('parts.carry-key')
        <input type="hidden" name="focus" value="{{ $fokus }}">
        <input type="hidden" name="url" value="{{ $url }}">
        <div class="filter-grid">
            @foreach ($data['filter'] as $name => $filterInfo)
                @if (empty($filterInfo->hidden) || $filterInfo->hidden === false)
                    <div class="field">
                        <label class="field-label" for="{{ $fokus }}-{{ $filterInfo->{"get-parameter"} }}">@lang($filterInfo->name)</label>
                        <select name="{{ $filterInfo->{"get-parameter"} }}"
                            id="{{ $fokus }}-{{ $filterInfo->{"get-parameter"} }}">
                            @foreach ($filterInfo->values as $key => $value)
                                @if (!empty($key))
                                    <option
                                        value="{{ $key !== 'nofilter' ? $key : '' }}"
                                        @if (
                                            (!empty($filterInfo->value) && $filterInfo->value === $key) ||
                                                (empty($filterInfo->value) && $filterInfo->{"default-value"} === $key)) selected @endif
                                        @if (array_key_exists($key, $filterInfo->{"disabled-values"}) && sizeof($filterInfo->{"disabled-values"}[$key]) > 0) disabled @endif>@lang($value)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="form-actions"><button type="submit" class="btn btn-sm no-js">@lang('settings.save')</button></div>
    </form>
</div>

<div class="subblock">
    <h3 class="block-title" id="{{ $fokus }}-bl">@lang('settings.header.4')</h3>
    <p class="help">@lang('settings.text.4')</p>
    <form action="{{ route('newBlacklist', ['fokus' => $fokus, 'url' => $url]) }}" method="post">
        @include('parts.carry-key')
        <input type="hidden" name="url" value="{{ $url }}">
        <input type="hidden" name="focus" value="{{ $fokus }}">
        <label class="field-label" for="{{ $fokus }}-blacklist">@lang('settings.address') ({{ sizeof($data['blacklist']) }}) </label>
        <textarea name="blacklist" id="{{ $fokus }}-blacklist" cols="30" rows="{{ max(min(sizeof($data['blacklist']) + 1, 20), 4) }}"
            maxlength="2048" placeholder="example.com&#10;example2.com&#10;*.example3.com" spellcheck="false">{{ implode("\r\n", $data['blacklist']) }}</textarea>
        <div class="form-actions"><button type="submit" class="btn btn-sm">@lang('settings.save')</button></div>
    </form>
</div>
