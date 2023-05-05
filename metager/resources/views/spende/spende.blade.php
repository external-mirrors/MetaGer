@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <div class="section">

        <p>@lang('spende.headline.2', [
            'aboutlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/about'),
            'beitrittlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/beitritt')
            ])</p>
    </div>
    <div id="content-container">
        @if (app('request')->input('method') == "paypal")
        <form class="form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <div class="section">
                <h3>{!! trans('spende.headline.5') !!}</h3>
                <div id="payment-methods">
                    <a class="payment-label" href="?method=debit">{!! trans('spende.head.lastschrift') !!}</a>
                    <a class="payment-label" href="?method=bank-transfer">{!! trans('spende.ueberweisung') !!}</a>
                    <a class="payment-label payment-label-selected" href="?method=paypal">{!! trans('spende.paypal.0') !!}</a>
                </div>
                <p><br>{!! trans('spende.paypal.1') !!}</p>
                <div class="center-wrapper">
                    @if (\App\Localization::getLanguage() == "de")
                    <input type="hidden" name="lc" value="{{ Request::getPreferredLanguage([]) }}">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="5JPHYQT88JSRQ" />
                    <input type="image" src="{{ \App\Http\Controllers\Pictureproxy::generateUrl('https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif') }}" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                    <img alt="" border="0" src="{{ \App\Http\Controllers\Pictureproxy::generateUrl('https://www.paypal.com/de_DE/i/scr/pixel.gif') }}" width="1" height="1" />
                    @else
                    <input type="hidden" name="lc" value="{{ Request::getPreferredLanguage([]) }}">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="LXWAVD6P3ZSWG" />
                    <input type="image" src="{{ \App\Http\Controllers\Pictureproxy::generateUrl('https://www.paypalobjects.com/en_US/DK/i/btn/btn_donateCC_LG.gif') }}" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                    <img alt="" border="0" src="{{ \App\Http\Controllers\Pictureproxy::generateUrl('https://www.paypal.com/en_DE/i/scr/pixel.gif') }}" width="1" height="1" />
                    @endif
                </div>
            </div>
        </form>
        @elseif ((app('request')->input('method') == "bank-transfer"))
        <div class="section form">
            <h3>{!! trans('spende.headline.5') !!}</h3>
            <div id="payment-methods">
                <a class="payment-label" href="?method=debit">{!! trans('spende.head.lastschrift') !!}</a>
                <a class="payment-label payment-label-selected" href="?method=bank-transfer">{!! trans('spende.ueberweisung') !!}</a>
                <a class="payment-label" href="?method=paypal">{!! trans('spende.paypal.0') !!}</a>
            </div>
            <p>
                <br>{!! trans('spende.bankinfo.1') !!} <br>
                <br>{!! trans('spende.bankinfo.2.0') !!}
                <br>{!! trans('spende.bankinfo.2.1') !!}
                <br>{!! trans('spende.bankinfo.2.2') !!}
                <br>{!! trans('spende.bankinfo.2.3') !!}
                <br>{!! trans('spende.bankinfo.2.4') !!}<br>
                <br>{!! trans('spende.bankinfo.3') !!}
            </p>
        </div>
        @else
        <form method="post" class="form" onsubmit="document.getElementById('donate-button').disabled=true;">
            <input type="hidden" name="pcsrf" value="{{ \Crypt::encrypt(\time()) }}">
            <div class="section">
                <h3>{!! trans('spende.headline.5') !!}</h3>
                <div id="payment-methods">
                    <a class="payment-label payment-label-selected" href="?method=debit">
                        <nobr>{!! trans('spende.head.lastschrift') !!}</nobr>
                    </a>
                    <a class="payment-label" href="?method=bank-transfer">
                        <nobr>{!! trans('spende.ueberweisung') !!}</nobr>
                    </a>
                    <a class="payment-label" href="?method=paypal">
                        <nobr>{!! trans('spende.paypal.0') !!}</nobr>
                    </a>
                </div>
                <p id="lastschrift-info">@lang('spende.lastschrift.info')</p>
                <p>@lang('spende.lastschrift.info2')</p>
                <h3>{!! trans('spende.headline.3') !!}</h3>
                <div class="amount-row">
                    <input type="radio" value="5" class="amount-radio" name="amount" id="amount-5euro" required="required" @if(empty($data) || $data["betrag"]==="5" )checked="checked" @endif> <label for="amount-5euro" class="amount-label">5€</label>
                    <input type="radio" value="10" class="amount-radio" name="amount" id="amount-10euro" required="required" @if(!empty($data) && $data["betrag"]==="10" )checked="checked" @endif><label for="amount-10euro" class="amount-label">10€</label>
                    <input type="radio" value="15" class="amount-radio" name="amount" id="amount-15euro" required="required" @if(!empty($data) && $data["betrag"]==="15" )checked="checked" @endif><label for="amount-15euro" class="amount-label">15€</label>
                    <input type="radio" value="20" class="amount-radio" name="amount" id="amount-20euro" required="required" @if(!empty($data) && $data["betrag"]==="20" )checked="checked" @endif><label for="amount-20euro" class="amount-label">20€</label>
                    <input type="radio" value="25" class="amount-radio" name="amount" id="amount-25euro" required="required" @if(!empty($data) && $data["betrag"]==="15" )checked="checked" @endif><label for="amount-25euro" class="amount-label">25€</label>
                    <input type="radio" value="50" class="amount-radio" name="amount" id="amount-50euro" required="required" @if(!empty($data) && $data["betrag"]==="50" )checked="checked" @endif><label for="amount-50euro" class="amount-label">50€</label>
                    <input type="radio" value="100" class="amount-radio" name="amount" id="amount-100euro" required="required" @if(!empty($data) && $data["betrag"]==="100" )checked="checked" @endif><label for="amount-100euro" class="amount-label">100€</label>
                    <input type="radio" value="200" class="amount-radio" name="amount" id="amount-200euro" required="required" @if(!empty($data) && $data["betrag"]==="200" )checked="checked" @endif><label for="amount-200euro" class="amount-label">200€</label>
                    <input type="radio" value="300" class="amount-radio" name="amount" id="amount-300euro" required="required" @if(!empty($data) && $data["betrag"]==="300" )checked="checked" @endif><label for="amount-300euro" class="amount-label">300€</label>
                </div>
                <div class="custom-amount-container">
                    <input type="radio" name="amount" id="amount-custom" value="custom" required="required" @if(!empty($data) && $data["betrag"]==="custom" )checked="checked" @endif><label for="amount-custom" class="amount-custom">{!! trans('spende.wunschbetrag.label') !!}</label> <input id="custom-amount" type="number" name="custom-amount" min="0" step=".01" placeholder="@lang('spende.wunschbetrag.placeholder')" value="">
                </div>
                <h3>{!! trans('spende.headline.4') !!}</h3>
                <div id="frequency">
                    <input type="radio" class="frequency-radio" name="frequency" id="once" value="once" required="required" @if(empty($data) || $data["frequency"]==="once" )checked="checked" @endif><label class="frequency-label" for="once">
                        <nobr>{!! trans('spende.frequency.once') !!}</nobr>
                    </label>
                    <input type="radio" class="frequency-radio" name="frequency" id="monthly" value="monthly" required="required" @if(!empty($data) && $data["frequency"]==="monthly" )checked="checked" @endif><label class="frequency-label" for="monthly">
                        <nobr>{!! trans('spende.frequency.monthly') !!}</nobr>
                    </label>
                    <input type="radio" class="frequency-radio" name="frequency" id="quarterly" value="quarterly" required="required" @if(!empty($data) && $data["frequency"]==="quarterly" )checked="checked" @endif><label class="frequency-label" for="quarterly">
                        <nobr>{!! trans('spende.frequency.quarterly') !!}</nobr>
                    </label>
                    <input type="radio" class="frequency-radio" name="frequency" id="six-monthly" value="six-monthly" required="required" @if(!empty($data) && $data["frequency"]==="six-monthly" )checked="checked" @endif><label class="frequency-label" for="six-monthly">
                        <nobr>{!! trans('spende.frequency.six-monthly') !!}</nobr>
                    </label>
                    <input type="radio" class="frequency-radio" name="frequency" id="annual" value="annual" required="required" @if(!empty($data) && $data["frequency"]==="annual" )checked="checked" @endif><label class="frequency-label" for="annual">
                        <nobr>{!! trans('spende.frequency.annual') !!}</nobr>
                    </label>
                </div>
                <h3>{!! trans('spende.headline.6') !!}</h3>
                <p>{!! trans('spende.lastschrift.2') !!}</p>
                <input type="hidden" name="dt" value="{{ md5(date('Y') . date('m') . date('d')) }}">
                <div id="input-picker" class="form-group donation-form-group">
                    <label>*Kontoinhaber</label>
                    <br>
                    <input type="radio" required="required" id="private" name="person" value="private" @if(empty($data) || $data["person"]==="private" )checked="checked" @endif><label for="private">{{trans('spende.lastschrift.private')}}</label>
                    <div id="input-private" class="show-on-input-checked form-inline">
                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="{!! trans('spende.lastschrift.3f.placeholder') !!}" @if(isset($data['firstname'])) value="{{$data['firstname']}}" @endif />
                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="{!! trans('spende.lastschrift.3l.placeholder') !!}" @if(isset($data['lastname'])) value="{{$data['lastname']}}" @endif />
                    </div>
                    <br>
                    <input type="radio" id="company" name="person" value="company" @if(!empty($data) && $data["person"]==="company" )checked="checked" @endif><label for="company">{{trans('spende.lastschrift.company')}}</label>
                    <div id="input-company" class="show-on-input-checked form-inline">
                        <input type="text" class="form-control" id="companyname" name="companyname" placeholder="{!! trans('spende.lastschrift.3c.placeholder') !!}" @if(isset($data['company'])) value="{{$data['company']}}" @endif />
                    </div>
                </div>
                <div class="form-group donation-form-group">
                    <label for="email">{!! trans('spende.lastschrift.4') !!}</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" @if(isset($data['email'])) value="{{$data['email']}}" @endif>
                </div>
                <div class="form-group donation-form-group">
                    <label for="iban">*{!! trans('spende.lastschrift.6') !!}</label>
                    <input type="text" required="required" class="form-control" id="iban" name="iban" placeholder="IBAN" @if(isset($data['iban'])) value="{{$data['iban']}}" @endif>
                </div>
                <div class="form-group donation-form-group">
                    <label for="bic">{!! trans('spende.lastschrift.7') !!}</label>
                    <input type="text" class="form-control" id="bic" name="bic" placeholder="BIC" @if(isset($data['bic'])) value="{{$data['bic']}}" @endif>
                </div>
                <div class="form-group donation-form-group">
                    <label for="msg">{!! trans('spende.lastschrift.8.message.label')!!}</label>
                    <p>{!! trans('spende.bankinfo.3')!!}</p>
                    <textarea class="form-control" id="msg" name="Nachricht" placeholder="{!! trans('spende.lastschrift.8.message.placeholder') !!}">@if(isset($data['nachricht'])){{$data['nachricht']}}@endif</textarea>
                </div>
                <input class="btn btn-default" id="donate-button" type="submit" value="{!! trans('spende.submit') !!}">
            </div>
        </form>
        @endif
        <div class="section">
            <p>{!! trans('spende.lastschrift.10') !!}</p>
        </div>
        @if (App\Localization::getLanguage() === "de")
        <div class="section member">
            <h3>{!! trans('spende.member.1') !!}</h3>
            <p>{!! trans('spende.member.2') !!}</p>
            <ul>
                <li>{!! trans('spende.member.3') !!}</li>
                <li>{!! trans('spende.member.4') !!}</li>
                <li>{!! trans('spende.member.5') !!}</li>
                <li>{!! trans('spende.member.6') !!}</li>
            </ul>
            <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/beitritt/") }}">{!! trans('spende.member.7') !!}</a>
        </div>
        @endif
    </div>
</div>
@endsection