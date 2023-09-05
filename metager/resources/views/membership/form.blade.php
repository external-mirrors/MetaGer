@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
<div class="page-description">Vielen Dank, dass Sie eine <a href="https://suma-ev.de/mitglieder/" target="_blank">Mitgliedschaft</a> in unserem gemeinnützigen Trägerverein erwägen. Um Ihren Antrag bearbeiten zu können benötigen wir lediglich ein paar Informationen, die Sie hier ausfüllen können.</div>
<form id="membership-form" method="POST">
    <input type="hidden" name="_token" value="{{$csrf_token}}">
    <div id="contact-data">
        <h3>1. Ihre Kontaktdaten</h3>
        <div class="input-group">
        @if(isset($errors) && $errors->has("name"))
        @foreach($errors->get("name") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <label for="name">Ihr Name</label>
        <input type="text" name="name" id="name" size="25" placeholder="Max Mustermann / Muster GmbH" value="{{ Request::input('name', '') }}" autofocus required />
        </div>
        <div class="input-group">
        @if(isset($errors) &&$errors->has("email"))
        @foreach($errors->get("email") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <label for="email">Ihre Email Addresse</label>
        <input type="email" name="email" id="email" placeholder="max@mustermann.de" value="{{ Request::input('email', '') }}" />
        </div>
    </div>
    <div id="membership-fee">
        <h3>2. Ihr Mitgliedsbeitrag</h3>
        <div>Wählen Sie nachfolgend bitte Ihren gewünschten monatlichen Mitgliedsbeitrag aus.</div>
        @if(isset($errors) && $errors->has("amount"))
        @foreach($errors->get("amount") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        @if(isset($errors) && $errors->has("custom-amount"))
        @foreach($errors->get("custom-amount") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div class="input-group">
            <input type="radio" name="amount" id="amount-5" value="5.00" @if(!Request::has('amount') || Request::input('amount') === "5.00")checked @endif required />
            <label for="amount-5">5€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-10" value="10.00" @if(Request::input('amount', '') === "10.00")checked @endif required />
            <label for="amount-10">10€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-15" value="15.00" @if(Request::input('amount', '') === "15.00")checked @endif required />
            <label for="amount-15">15€</label>
        </div>
        <div class="input-group custom">
            <input type="radio" name="amount" id="amount-custom" value="custom" @if(Request::input('amount', '') === "custom")checked @endif required />
            <label for="amount-custom">Wunschbetrag</label>
            <input type="number" name="custom-amount" id="amount-custom-value" step="0.01" min="2.5" value="{{ Request::input('custom-amount', '5,00') }}" placeholder="5,00€" />
        </div>
    </div>
    <div id="membership-payment">
        <h3>3. Ihr Zahlungsintervall</h3>
        @if(isset($errors) && $errors->has("interval"))
        @foreach($errors->get("interval") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div id="membership-interval">
            <div class="input-group annual">
                <input type="radio" name="interval" id="interval-annual" value="annual" @if(!Request::has('interval') || Request::input('interval') === "annual")checked @endif required>
                <label for="interval-annual">jährlich</label>
            </div>
            <div class="input-group six-monthly">
                <input type="radio" name="interval" id="interval-six-monthly" value="six-monthly" @if(Request::input('interval', '') === "six-monthly")checked @endif required>
                <label for="interval-six-monthly">halbjährlich</label>
            </div>
            <div class="input-group quarterly">
                <input type="radio" name="interval" id="interval-quarterly" value="quarterly" @if(Request::input('interval', '') === "quarterly")checked @endif required>
                <label for="interval-quarterly">vierteljährlich</label>
            </div>
            <div class="input-group monthly">
                <input type="radio" name="interval" id="interval-monthly" value="monthly" @if(Request::input('interval', '') === "monthly")checked @endif required>
                <label for="interval-monthly">monatlich</label>
            </div>
        </div>
        <h3>4. Ihre Zahlungsmethode</h3>
        @if(isset($errors) && $errors->has("payment-method"))
        @foreach($errors->get("payment-method") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div id="membership-payment-method">
            <input type="radio" name="payment-method" id="payment-method-directdebit" value="directdebit" @if(!Request::has('payment-method') || Request::input('payment-method') === "directdebit")checked @endif required>
            <label for="payment-method-directdebit">SEPA Lastschrift</label>
            <input type="radio" name="payment-method" id="payment-method-banktransfer" value="banktransfer" @if(Request::input('payment-method', '') === "banktransfer")checked @endif required>
            <label for="payment-method-banktransfer">Banküberweisung</label>
            <div id="directdebit-data">
            @if(isset($errors) && $errors->has("iban"))
            @foreach($errors->get("iban") as $error)
            <div class="error">{{ $error }}</div>
            @endforeach
            @endif
            <div class="input-group">
                    <label for="accountholder">Kontoinhaber (falls abweichend)</label>
                    <input type="text" name="accountholder" id="accountholder" placeholder="Max Mustermann" value="{{ Request::input('accountholder', '') }}">
                </div>
                <div class="input-group">
                    <label for="iban">IBAN</label>
                    <input type="text" name="iban" id="iban" placeholder="DE80 1234 5678 9012 3456 78" value="{{ Request::input('iban', '') }}">
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Abschicken</button>
</form>
<section id="membership-advantages">
    <div>
        <h3>MetaGer Schlüssel inklusive</h3>
        <div>Sie erhalten einen Schlüssel für werbefreie Suchen im Gegenwert Ihres Mitgliedsbeitrags. Dieser wird automatisch jeden Monat aufgefüllt.</div>
    </div>
    <div>
        <h3>Mastodon</h3>
        <div>Der SUMA-EV ist im alternativen und verteilten sozialen Netzwerk Mastodon mit einem eigenen Account vertreten. Hierzu betreiben wir eine <a href="https://suma-ev.social">Instanz</a> auf unseren eigenen Servern. Gleichzeitig erhalten Sie als Mitglied die exklusive Möglichkeit dem Fediverse ebenfalls über diese Instanz beizutreten. Sie bekommen dann einen Nutzeraccount, der auf @suma-ev.social endet.</div>
    </div>
    <div>
        <h3>Ihr Beitrag wird für gemeinnützige Zwecke verwendet</h3>
        <div>Der SUMA-EV ist vom Finanzamt Hannover Nord als gemeinnützig anerkannt, eingetragen in das Vereinsregister beim Amtsgericht Hannover unter VR200033. Ihre Beiträge können somit steuerlich geltend gemacht werden.</div>
    </div>
</section>
@endsection