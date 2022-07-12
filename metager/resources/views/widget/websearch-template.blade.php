<form class="metager-searchform" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/meta/meta.ger3") }}" method="get" accept-charset="UTF-8" >
	<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}"><img class="metager-logo" title="{{ trans('websearch.head.4') }}" alt="MetaGer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGcAAAASCAMAAABxcDC5AAAArlBMVEVHcEz0chb0chb1chbzchXzcRb0cRf0chf0chfzchbzchX0chb0chb0chb/XwD1chbycBf0chb0chb0chb/WwD/cx71chb1chb0chb0chbzchX1cxXzchX0chb1chb3chf0chb1chb1cRX3chb1chX0chX1cxb0cxb4bxb0chbycRT0chb0chb2cRb0cRb0chb0chX2chb2cRP0cRX0chb0cxb1cRb0cRf1chb0chZuEtl5AAAAOXRSTlMA5f3cVZlE7ojdO/Xv7QOqE966+QEGZvLf6j9Ig6SWIcB/Xx5315TPC8IlcNFQybONMxrjn1usLcU6Z1+WAAACFUlEQVQ4y62V23aqMBCGx4oURZUtCBtQAUGkguKp1Xn/F2tOKFBcpav9L0gymeRLJpMA0FJ23Gx/9zuvhbbwe72cG80RlvQnnE6T1RjiwjPgD9XMCdGetRsfKVUFLl/ebMd7Ipm0XUVBS1FSAK/wSx3qN8C3+oTeak7756Yn5qHNFLpY11tCF9q/txcAc17LAJyH33RGOdMaxtSLft0lTS1j9TXhSFy2yorAwgAActyYrizLbqDiFbayjLYsDyhnKlO50StGDZyTjj2JOkg57kl7iaOAzOoSjvD4N+SlhD75Wqom7Ad8L52Pg0th3+GtgSPR7fOA2aoBsEaHN79wEjwDaLi5BwLTEkeTTlD4/b9zzAmTSfPcLAbuMQFDteAJB9QRgPc430VlP7UMFJwDvaUW3eocV0UELcuAMV3MN5y4R3TZoHWqcAY9IexU4+YIzpH1ftg4gVYc/p50poPy/Uk0BVEfsiR6wpH4I9SNtXacxns64Zm+fXnOKY/6jhOypKxw7DIH7ILjVznBIw/acMb6qPaenJEl+qXEyQBO2Ln7rVCh2Xn4CQc2eNEqnA/MQ6DrJboZmkqKGMAYYU/4Xdc0ZEdUd7MfcNwXREv8Vti4o4q4B9hSAFr0OwrZ7RJ+xHJO2HpQF+OWZY7fLSrFzcxYZTBZ94X4+q6XfU55k4wZszjkz0Iu/G7cME79YtwBYNwX5/cJHKxXkx8i86oAAAAASUVORK5CYII="></a>
	<input class="metager-searchinput" name="eingabe" placeholder="{{ trans('websearch.head.5') }}" required>
	<button class="metager-searchbutton" type="submit">{{ trans('websearch.head.8') }}</button>
	<input type="hidden" name="wdgt-version" value="2">
    @if(!empty($site))
    <input type="hidden" name="site" value="{{ $site }}">
    @endif
	@if(!empty($css))
    <style type="text/css" scoped>
		{!! $css !!}
	</style>
	@endif
</form>
