@extends('layouts.subPages')

@section('title', $title)

@section('content')
<div id="abo-create">
    <h1>MetaGer Logs API - @lang("logs.create_abo.heading")</h1>
    <h3>@lang("logs.create_abo.interval")</h3>
    <div id="interval">
        <a @if(request('interval', app(App\Models\Logs\LogsAccountProvider::class)->abo->interval) === 'monthly') class="active" @endif
            href="{{ route('logs:abo', ['interval' => 'monthly']) }}">@lang('logs.overview.abo.interval.setting_values.monthly')</a>
        <a @if(request('interval',app(App\Models\Logs\LogsAccountProvider::class)->abo->interval) === 'quarterly') class="active" @endif
            href="{{ route('logs:abo', ['interval' => 'quarterly']) }}">@lang('logs.overview.abo.interval.setting_values.quarterly')</a>
        <a @if(request('interval', app(App\Models\Logs\LogsAccountProvider::class)->abo->interval) === 'six-monthly') class="active" @endif
            href="{{ route('logs:abo', ['interval' => 'six-monthly']) }}">@lang('logs.overview.abo.interval.setting_values.six-monthly')</a>
        <a @if(request('interval', app(App\Models\Logs\LogsAccountProvider::class)->abo->interval) === 'annual') class="active" @endif
            href="{{ route('logs:abo', ['interval' => 'annual']) }}">@lang('logs.overview.abo.interval.setting_values.annual')</a>
    </div>
    @if(app(App\Models\Logs\LogsAccountProvider::class)->abo->interval !== "never")
    <form id="cancel-form" action="{{ route('logs:abo') }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <input type="hidden" name="interval" value="never">
        <button type="submit">@lang("logs.create_abo.cancel")</button>
    </form>
    @endif
    @if(request()->filled("interval") && in_array(request("interval"), ["monthly", "quarterly", "six-monthly", "annual"]))
        <h3>@lang("logs.create_abo.conditions")</h3>
        <div class="input-group">
            <label for="interval">@lang("logs.create_abo.interval"):</label>
            <span>{{ __("logs.overview.abo.interval.setting_values." . request("interval")) }}</span>
        </div>
        <div class="input-group">
            <label for="amount">@lang("logs.create_abo.amount"):</label>
            <span>
                @switch(request("interval"))
                @case("monthly")
                {{ config("metager.logs.monthly_cost") }}
                @break
                @case("quarterly")
                {{ config("metager.logs.monthly_cost") * 3 }}
                @break
                @case("six-monthly")
                {{ config("metager.logs.monthly_cost") * 6 }}
                @break
                @case("annual")
                {{ config("metager.logs.monthly_cost") * 12 }}
                @break
                @endswitch
                €
            </span>
        </div>
        <p>@lang("logs.create_abo.conditions_hint")</p>
        <p>@lang("logs.create_abo.conditions_nda")</p>
        <a href="{{ route('logs:nda') }}" target="_blank" class="nda-link">@lang("logs.create_abo.nda")</a>
        <form action="{{ route('logs:abo') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <input type="hidden" name="interval" value="{{ request("interval") }}">
            <button class="btn btn-default" type="submit">@lang("logs.create_abo.accept")</button>
        </form>
    @endif
</div>
@endsection