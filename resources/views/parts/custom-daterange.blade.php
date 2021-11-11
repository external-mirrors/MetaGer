<div id="bing-from-to">
    <input type="date" min="{{ Carbon::now()->subYear()->format("Y-m-d") }}" max="{{ Carbon::now()->format("Y-m-d") }}" form="searchForm" @if(Request::filled("ff")) value="{{ Request::input("ff", "") }}" @endif name="ff" onchange="if(document.querySelector('input[name=ff]').value != '' && document.querySelector('input[name=ft]').value != ''){this.form.submit();}">
    <div>&nbsp;-&nbsp;</div>
    <input type="date" min="{{ Carbon::now()->subYear()->format("Y-m-d") }}" max="{{ Carbon::now()->format("Y-m-d") }}" form="searchForm" @if(Request::filled("ft")) value="{{ Request::input("ft", "") }}" @endif name="ft" onchange="if(document.querySelector('input[name=ff]').value != '' && document.querySelector('input[name=ft]').value != ''){this.form.submit();}">
</div>