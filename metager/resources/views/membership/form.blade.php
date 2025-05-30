@extends('layouts.subPages')

@section('title', $title)

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
<div class="page-description">Vielen Dank, dass Sie eine <a href="https://suma-ev.de/mitglieder/"
        target="_blank">Mitgliedschaft</a> in unserem gemeinnützigen Trägerverein erwägen. Um Ihren Antrag bearbeiten zu
    können benötigen wir lediglich ein paar Informationen, die Sie hier ausfüllen können.</div>
<form id="membership-form" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{$csrf_token}}" autocomplete="off">
    <input type="hidden" name="type" value="{{ request("type", "") === "company" ? "company" : "person"  }}">
    <div id="contact-data" @if(Request::input("type", "") === "company")class="company"@else class="person"@endif>
        @php
            $title = $application !== null ? $application->contact->title : Request::input('title', '');
            $firstname = $application !== null ? $application->contact->first_name : Request::input('firstname', '');
            $lastname = $application !== null ? $application->contact->last_name : Request::input('lastname', '');
            $email = $application !== null ? $application->contact->email : Request::input('email', '');
        @endphp
        <h3 id="contact_data">1. Ihre Kontaktdaten 
            @if($application === null)
            @if(Request::input("type", "") === "company")
            <a href="{{  route("membership_form", Request::except(["type"])) }}#contact_data">Als Person beitreten?</a>
            @else
            <a href="{{  route("membership_form", array_merge(Request::all(), ["type" => "company"])) }}#contact_data">Als Firma beitreten?</a>
            @endif
            @endif
        </h3>
        @if(Request::input("type", "") !== "company")
        <div class="input-group title">
            @if(isset($errors) && $errors->has("title"))
                @foreach($errors->get("title") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="title">Anrede</label>
            <select name="title" id="title" required @if($application !== null) disabled @endif>
                <option disabled selected value>-- Auswahl --</option>
                <option value="Herr" @if($title === "Herr")selected @endif>Herr</option>
                <option value="Frau" @if($title === "Frau")selected @endif>Frau</option>
                <option value="Neutral" @if($title === "Neutral")selected @endif>Neutral</option>
            </select>
        </div>
        <div class="input-group">
            @if(isset($errors) && $errors->has("firstname"))
                @foreach($errors->get("firstname") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="firstname">Ihr Vorname</label>
            <input type="text" name="firstname" id="firstname" size="25" placeholder="Max" @if($application !== null) disabled @endif
                value="{{ $firstname }}" required />
        </div>
        <div class="input-group">
            @if(isset($errors) && $errors->has("lastname"))
                @foreach($errors->get("lastname") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="lastname">Ihr Nachname</label>
            <input type="text" name="lastname" id="lastname" size="25" placeholder="Mustermann"  @if($application !== null) disabled @endif
                value="{{ $lastname }}" required />
        </div>
        @else
        <div class="input-group">
            @if(isset($errors) && $errors->has("company"))
                @foreach($errors->get("company") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="company">Ihr Firmenname</label>
            <input type="text" name="company" id="company" size="25" placeholder="Muster GmbH"  @if($application !== null) disabled @endif
                value="{{ Request::input('company', '') }}" required />
        </div>
        <div class="input-group employees">
            @if(isset($errors) && $errors->has("employees"))
                @foreach($errors->get("employees") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="employees">Anzahl Mitarbeitende</label>
            <select name="employees" id="employees" required>
                <option disabled selected value>-- Auswahl --</option>
                <option value="1-19" @if(Request::input('employees', '' )==="1-19")selected @endif>1 - 19</option>
                <option value="20-199" @if(Request::input('employees', '' )==="20-199")selected @endif>20 - 199</option>
                <option value=">200" @if(Request::input('employees', '' )===">200")selected @endif>> 200</option>
            </select>
        </div>
        @endif
        <div class="input-group email">
            @if(isset($errors) && $errors->has("email"))
                @foreach($errors->get("email") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="email">Ihre Email Addresse</label>
            <input type="email" name="email" id="email" placeholder="max@mustermann.de"  @if($application !== null) disabled @endif
                value="{{ $email }}" />
        </div>
    </div>
    <div id="membership-fee">
        <h3>2. Ihr monatlicher Mitgliedsbeitrag</h3>
        @if($application !== null)
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
            <input type="radio" name="amount" id="amount-10" value="10.00" @if(!Request::has('amount') ||
                Request::input('amount')==="10.00" )checked @endif required />
            <label for="amount-10">10€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-15" value="15.00" @if(Request::input('amount', '' )==="15.00"
                )checked @endif required />
            <label for="amount-15">15€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-20" value="20.00" @if(Request::input('amount', '' )==="20.00"
                )checked @endif required />
            <label for="amount-20">20€</label>
        </div>
        <div class="input-group custom">
            <input type="radio" name="amount" id="amount-custom" value="custom" @if(Request::input('amount', ''
                )==="custom" )checked @endif required />
            <label for="amount-custom">Wunschbetrag</label>
            <input type="text" name="custom-amount" id="amount-custom-value" inputmode="numeric"
                value="{{ Request::input('custom-amount', '10,00') }}" placeholder="10,00€" />
        </div>
        <div id="reduction-container" class="hidden">
            <div>Der Mindestbeitrag beträgt monatlich <span>5€</span>. Wenn Sie <a href="https://suma-ev.de/beitragsordnung/" target="_blank">Anspruch auf einen reduzierten Beitrag</a> haben, laden Sie bitte nachfolgend einen geeigneten Nachweis zusammen mit Ihrem Antrag hoch.</div>
            @if(isset($errors) && $errors->has("reduction"))
                @foreach($errors->get("reduction") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <div class="input-group">
                <label for="reduction">Nachweis (PNG/JPG/PDF)</label>
                <input type="file" name="reduction" id="reduction">
            </div>
        </div>
        @endif
    </div>
    <div id="membership-payment">
        <h3>3. Ihr Zahlungsintervall</h3>
        @if($application !== null)
        @if(isset($errors) && $errors->has("interval"))
            @foreach($errors->get("interval") as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif
        <div id="membership-interval">
            <div class="input-group monthly">
                <input type="radio" name="interval" id="interval-monthly" value="monthly" @if(!Request::has('interval')
                    || Request::input('interval')==="monthly" )checked @endif required>
                <label for="interval-monthly">monatlich <span class="amount"></span></label>
            </div>
            <div class="input-group quarterly">
                <input type="radio" name="interval" id="interval-quarterly" value="quarterly"
                    @if(Request::input('interval', '' )==="quarterly" )checked @endif required>
                <label for="interval-quarterly">vierteljährlich <span class="amount"></span></label>
            </div>
            <div class="input-group six-monthly">
                <input type="radio" name="interval" id="interval-six-monthly" value="six-monthly"
                    @if(Request::input('interval', '' )==="six-monthly" )checked @endif required>
                <label for="interval-six-monthly">halbjährlich <span class="amount"></span></label>
            </div>
            <div class="input-group annual">
                <input type="radio" name="interval" id="interval-annual" value="annual"
                    @if(Request::input('interval', '' )==="annual" )checked @endif required>
                <label for="interval-annual">jährlich <span class="amount"></span></label>
            </div>
        </div>
        @endif
        <h3>4. Ihre Zahlungsmethode</h3>
        @if($application !== null)
        @if(isset($errors) && $errors->has("payment-method"))
            @foreach($errors->get("payment-method") as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif
        <div id="membership-payment-method">
            <input type="radio" name="payment-method" id="payment-method-directdebit" value="directdebit"
                @if(!Request::has('payment-method') || Request::input('payment-method')==="directdebit" )checked @endif
                required>
            <label for="payment-method-directdebit">SEPA Lastschrift</label>
            <input type="radio" name="payment-method" id="payment-method-banktransfer" value="banktransfer"
                @if(Request::input('payment-method', '' )==="banktransfer" )checked @endif required>
            <label for="payment-method-banktransfer">Banküberweisung</label>
            <input type="radio" name="payment-method" id="payment-method-paypal" class="js-only" value="paypal"
                @if(Request::input('payment-method', '' )==="paypal" )checked @endif required>
            <label for="payment-method-paypal" class="js-only">PayPal</label>
            <input type="radio" name="payment-method" id="payment-method-creditcard" class="js-only" value="card"
                @if(Request::input('payment-method', '' )==="creditcard" )checked @endif required data-clientid="{{ config("metager.metager.paypal.membership.client_id") }}">
            <label for="payment-method-creditcard" class="js-only">Kredit-/Debitkarte</label>
            <div id="directdebit-data" class="info-container">
                @if(isset($errors) && $errors->has("iban"))
                    @foreach($errors->get("iban") as $error)
                        <div class="error">{{ $error }}</div>
                    @endforeach
                @endif
                <div class="input-group">
                    <label for="accountholder">Kontoinhaber (falls abweichend)</label>
                    <input type="text" name="accountholder" id="accountholder" placeholder="Max Mustermann"
                        value="{{ Request::input('accountholder', '') }}">
                </div>
                <div class="input-group">
                    <label for="iban">IBAN</label>
                    <input type="text" name="iban" id="iban" placeholder="DE80 1234 5678 9012 3456 78"
                        value="{{ Request::input('iban', '') }}">
                </div>
            </div>
            <div id="paypal-data" class="info-container">Mit Abschicken des Formulars werden Sie zwecks Authorisierung der Mitgliedsbeiträge zu PayPal weitergeleitet.</div>
            <div id="creditcard-data" class="info-container" data-loading-text="{{ __('spende.execute-payment.card.loading') }}">
                <div id="creditcard-name-container">
                    <label for="creditcard-name">@lang("spende.execute-payment.card.name")</label>
                    <div id="creditcard-name"></div>
                </div>
                <div id="errors">
                    <div id="card-acceptance-error" class="error hidden">@lang('spende.execute-payment.card.error.acceptance')</div>
                    <div id="syntax-error" class="error hidden">@lang('spende.execute-payment.card.error.syntax')</div>
                </div>
                <div id="creditcard-details">
                    <div id="creditcard-number-container">
                        <label for="creditcard-number">@lang("spende.execute-payment.card.number")</label>
                        <div id="creditcard-number"></div>
                    </div>
                    <div id="creditcard-valid-until-container">
                        <label for="creditcard-valid-until">@lang("spende.execute-payment.card.expiration")</label>
                        <div id="creditcard-valid-until"></div>
                    </div>
                    <div id="creditcard-valid-until-container">
                        <label for="creditcard-cvv">@lang("spende.execute-payment.card.cvv")</label>
                        <div id="creditcard-cvv"></div>
                    </div>
                </div>
                <div id="billing-address">
                    <h4>@lang('spende.execute-payment.card.billing.address')</h4>
                    <div>@lang('spende.execute-payment.card.billing.hint')</div>
                    <div class="inputs">
                        <div class="input-group">
                            <label for="card-billing-address-line-1">@lang('spende.execute-payment.card.billing.address-line-1')</label>
                            <input type="text" id="card-billing-address-line-1" name="card-billing-address-line-1" autocomplete="off" placeholder="Musterstraße 3" />
                        </div>
                        <div class="input-group">
                            <label for="card-billing-address-line-2">@lang('spende.execute-payment.card.billing.address-line-2')</label>
                            <input type="text" id="card-billing-address-line-2" name="card-billing-address-line-2" autocomplete="off" placeholder="Appartment 3"/>
                        </div>
                        <div class="input-group">
                            <label for="card-billing-address-admin-area-line-1">@lang('spende.execute-payment.card.billing.address-admin-area-line-1')</label>
                            <input type="text" id="card-billing-address-admin-area-line-1" name="card-billing-address-admin-area-line-1" autocomplete="off" placeholder="Musterstadt"/>
                        </div>
                        <div class="input-group">
                            <label for="card-billing-address-admin-area-line-2">@lang('spende.execute-payment.card.billing.address-admin-area-line-2')</label>
                            <input type="text" id="card-billing-address-admin-area-line-2" name="card-billing-address-admin-area-line-2" autocomplete="off" placeholder="Niedersachsen"/>
                        </div>
                        <div class="input-group">
                            <label for="card-billing-address-country-code">@lang('spende.execute-payment.card.billing.address-country-code')</label>
                            <input type="text" id="card-billing-address-country-code" name="card-billing-address-country-code" autocomplete="off" placeholder="DE"/>
                        </div>
                        <div class="input-group">
                        <label for="card-billing-address-postal-code">@lang('spende.execute-payment.card.billing.address-postal-code')</label>
                            <input type="text" id="card-billing-address-postal-code" name="card-billing-address-postal-code" autocomplete="off" placeholder="30159"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <button type="submit" class="btn btn-primary">Abschicken</button>
</form>
<section id="membership-advantages">
    <div>
        <h3>MetaGer Schlüssel inklusive</h3>
        <div>Sie erhalten einen Schlüssel für werbefreie Suchen im Gegenwert Ihres Mitgliedsbeitrags. Dieser wird
            automatisch jeden Monat aufgefüllt. Dank <a
                href="{{ LaravelLocalization::getLocalizedURL(null, "/keys/help/anonymous-token") }}" target="_blank">anonymer Token</a> können wir die
            anonyme Suche auch im Zusammenhang mit einer Mitgliedschaft beweisbar versprechen.</div>
    </div>
    <div>
        <h3>Mastodon</h3>
        <div>Der SUMA-EV ist im alternativen und verteilten sozialen Netzwerk Mastodon mit einem eigenen Account
            vertreten. Hierzu betreiben wir eine <a href="https://suma-ev.social">Instanz</a> auf unseren eigenen
            Servern. Gleichzeitig erhalten Sie als Mitglied die exklusive Möglichkeit dem Fediverse ebenfalls über diese
            Instanz beizutreten. Sie bekommen dann einen Nutzeraccount, der auf @suma-ev.social endet.</div>
    </div>
    <div>
        <h3>Ihr Beitrag wird für gemeinnützige Zwecke verwendet</h3>
        <div>Der SUMA-EV ist vom Finanzamt Hannover Nord als gemeinnützig anerkannt, eingetragen in das Vereinsregister
            beim Amtsgericht Hannover unter VR200033. Ihre Beiträge können somit steuerlich geltend gemacht werden.
        </div>
    </div>
</section>
@endsection