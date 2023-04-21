@if(sizeof(app(\App\SearchSettings::class)->parameterFilter["customfreshness"]->{"disabled-values"}) === 0)						
<div id="bing-from-to">
    <input type="date" min="{{ Carbon::now()->subYear()->format("Y-m-d") }}" max="{{ Carbon::now()->format("Y-m-d") }}" form="searchForm" @if(Request::filled("ff")) value="{{ Request::input("ff", "") }}" @endif name="ff">
    <div>&nbsp;-&nbsp;</div>
    <input type="date" min="{{ Carbon::now()->subYear()->format("Y-m-d") }}" max="{{ Carbon::now()->format("Y-m-d") }}" form="searchForm" @if(Request::filled("ft")) value="{{ Request::input("ft", "") }}" @endif name="ft">
</div>
@endif