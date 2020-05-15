<div id="bing-from-to">
    <input type="date" form="searchForm" @if(Request::filled("ff")) value="{{ Request::input("ff", "") }}" @endif name="ff"> 
    <div>&nbsp;-&nbsp;</div> 
    <input type="date" form="searchForm" @if(Request::filled("ft")) value="{{ Request::input("ft", "") }}" @endif name="ft">
</div>