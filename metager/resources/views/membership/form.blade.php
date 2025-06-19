@extends('layouts.subPages')

@section('title', $title)

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
@if($application === null || !$application->is_update)
<div class="page-description">@lang('membership.application.description')</div>
@else
<div class="page-description">@lang('membership.application.update', ["contact_link" => route("contact")])</div>
@endif
@php
    $application_id = $application !== null && $application->id !== null ? $application->id : null;
@endphp
<form id="membership-form" method="POST" enctype="multipart/form-data" action="{{ route("membership_form", array_merge(request()->except("edit"), ["application_id" => $application_id])) }}">
    <input type="hidden" name="_token" value="{{$csrf_token}}" autocomplete="off">
    @php
        $editable = $application === null || ($application->contact === null && $application->company === null);
        $type = Request::input("type", "person");
        if($application !== null){
            if($application->contact !== null){
                $type = "person";
            }elseif($application->company !== null){
                $type = "company";
            }
        }
        $title = $application !== null && $application->contact !== null ? $application->contact->title : Request::input('title', '');
        $firstname = $application !== null && $application->contact !== null ? $application->contact->first_name : Request::input('firstname', '');
        $lastname = $application !== null && $application->contact !== null ? $application->contact->last_name : Request::input('lastname', '');
        $email = $application !== null && $application->contact !== null ? $application->contact->email : Request::input('email', '');
        $email = $application !== null && $application->company !== null ? $application->company->email : $email;
        $company = $application !== null && $application->company !== null ? $application->company->company : Request::input('company', '');
        $employees = $application !== null && $application->company !== null ? $application->company->employees : Request::input('employees', '');
    @endphp
    <input type="hidden" name="type" value="{{ $type }}">
    <div id="contact-data" @if($type === "company")class="company"@else class="person"@endif>
        <h3 id="contact_data">1. Ihre Kontaktdaten 
            @if($editable)
            @if($type === "company")
            <a href="{{  route("membership_form", array_merge(Request::except(["type"]), ["application_id" => request()->route("application_id")])) }}#contact_data">Als Person beitreten?</a>
            @else
            <a href="{{  route("membership_form", array_merge(Request::all(), ["type" => "company", "application_id" => request()->route("application_id")])) }}#contact_data">Als Firma beitreten?</a>
            @endif
            @elseif(!$application->is_update)
            <a href="{{  route("membership_form", array_merge(Request::all(), [Request::route("application_id")], ["edit" => "contact"])) }}#contact_data">Bearbeiten</a>
            @endif
        </h3>
        @if($type !== "company")
        <div class="input-group title">
            @if(isset($errors) && $errors->has("title"))
                @foreach($errors->get("title") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="title">Anrede</label>
            <select name="title" id="title" required @if(!$editable) disabled autocomplete="off" @endif>
                <option disabled @if($title === "")selected @endif value>-- Auswahl --</option>
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
            <input type="text" name="firstname" id="firstname" size="25" placeholder="Max" @if(!$editable) disabled autocomplete="off" @endif
                value="{{ $firstname }}" required />
        </div>
        <div class="input-group">
            @if(isset($errors) && $errors->has("lastname"))
                @foreach($errors->get("lastname") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="lastname">Ihr Nachname</label>
            <input type="text" name="lastname" id="lastname" size="25" placeholder="Mustermann"  @if(!$editable) disabled autocomplete="off" @endif
                value="{{ $lastname }}" required />
        </div>
        @else
        <div class="input-group company">
            @if(isset($errors) && $errors->has("company"))
                @foreach($errors->get("company") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="company">Ihr Firmenname</label>
            <input type="text" name="company" id="company" size="25" placeholder="Muster GmbH"  @if(!$editable) disabled autocomplete="off" @endif
                value="{{ $company }}" required />
        </div>
        <div class="input-group employees">
            @if(isset($errors) && $errors->has("employees"))
                @foreach($errors->get("employees") as $error)
                    <div class="error">{{ $error }}</div>
                @endforeach
            @endif
            <label for="employees">Anzahl Mitarbeitende</label>
            <select name="employees" id="employees" required @if(!$editable) disabled autocomplete="off" @endif>
                <option disabled @if($employees === "")selected @endif value>-- Auswahl --</option>
                <option value="1-19" @if($employees==="1-19")selected @endif>1 - 19</option>
                <option value="20-199" @if($employees==="20-199")selected @endif>20 - 199</option>
                <option value=">200" @if($employees===">200")selected @endif>> 200</option>
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
            <input type="email" name="email" id="email" placeholder="max@mustermann.de"  @if(!$editable) disabled @endif
                value="{{ $email }}" />
        </div>
        @if($editable)
        <button type="submit" class="btn btn-primary">Weiter</button>
        @endif
    </div>
    @php
    $visible = $application !== null && ($application->contact !== null || $application->company !== null);
    $editable = $visible && $application->amount === null ;
    $amount = $application !== null && $application->amount !== null ? $application->amount : request()->input("amount", null);
    if($amount === "custom") $amount = request()->input("custom-amount");
    if($amount !== null) $amount = floatval($amount);
    @endphp
    <div id="membership-fee" @if(!$visible)class="disabled"@endif>
        <h3>2. Ihr monatlicher Mitgliedsbeitrag
            @if($visible && !$editable)
            <a href="{{  route("membership_form", array_merge(Request::all(), [Request::route("application_id")], ["edit" => "membership-fee"])) }}">Bearbeiten</a>
            @endif
        </h3>
        @if($visible)
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
        <div class="input-group @if(!$editable)disabled @endif " >
            <input type="radio" name="amount" id="amount-10" value="10.00" @if($amount === null ||
                $amount == 10 )checked @endif @if(!$editable)disabled @endif required />
            <label for="amount-10">10€</label>
        </div>
        <div class="input-group @if(!$editable)disabled @endif ">
            <input type="radio" name="amount" id="amount-15" value="15.00" @if($amount == 15
                )checked @endif @if(!$editable)disabled @endif required />
            <label for="amount-15">15€</label>
        </div>
        <div class="input-group @if(!$editable)disabled @endif ">
            <input type="radio" name="amount" id="amount-20" value="20.00" @if($amount == 20
                )checked @endif @if(!$editable)disabled @endif required />
            <label for="amount-20">20€</label>
        </div>
        <div class="input-group custom @if(!$editable)disabled @endif ">
            <input type="radio" name="amount" id="amount-custom" value="custom" @if(!in_array($amount, [null, (float)"10.00", (float)"15.00", (float)"20.00"]))checked @endif @if(!$editable)disabled @endif required />
            <label for="amount-custom">Wunschbetrag</label>
            <input type="text" name="custom-amount" id="amount-custom-value" inputmode="numeric"
                value="{{ $amount }}" placeholder="10,00€" @if(!$editable)disabled @endif />
        </div>
        @if($editable)
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
        <button type="submit" class="btn btn-primary">Weiter</button>
        @endif
        @endif
    </div>
    @php
    $visible = $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null;
    $editable = $visible && $application->interval === null;
    $interval = $application !== null && $application->interval !== null ? $application->interval : request()->input("interval", null);
    @endphp
    <div id="membership-payment" @if(!$visible)class="disabled"@endif>
        <h3>3. Ihr Zahlungsintervall
            @if($visible && !$editable)
            <a href="{{  route("membership_form", array_merge(Request::all(), [Request::route("application_id")], ["edit" => "membership-payment"])) }}">Bearbeiten</a>
            @endif
        </h3>
        @if($visible)
        @if(isset($errors) && $errors->has("interval"))
            @foreach($errors->get("interval") as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif
        <div id="membership-interval">
            <div class="input-group monthly @if(!$editable)disabled @endif">
                <input type="radio" name="interval" id="interval-monthly" value="monthly" @if($interval === null || $interval === "monthly" )checked @endif required>
                <label for="interval-monthly">monatlich</label>
            </div>
            <div class="input-group quarterly @if(!$editable)disabled @endif">
                <input type="radio" name="interval" id="interval-quarterly" value="quarterly"
                    @if($interval==="quarterly" )checked @endif required>
                <label for="interval-quarterly">vierteljährlich <span class="amount"> {{ number_format($application->amount * 3, 2, ",") }}€</span></label>
            </div>
            <div class="input-group six-monthly @if(!$editable)disabled @endif">
                <input type="radio" name="interval" id="interval-six-monthly" value="six-monthly"
                    @if($interval==="six-monthly" )checked @endif required>
                <label for="interval-six-monthly">halbjährlich <span class="amount"> {{ number_format($application->amount * 6, 2, ",") }}€</span></label>
            </div>
            <div class="input-group annual @if(!$editable)disabled @endif">
                <input type="radio" name="interval" id="interval-annual" value="annual"
                    @if($interval==="annual" )checked @endif required>
                <label for="interval-annual">jährlich <span class="amount"> {{ number_format($application->amount * 12, 2, ",") }}€</span></label>
            </div>
        </div>
        @if($editable)
        <button type="submit" class="btn btn-primary">Weiter</button>
        @endif
        @endif
    </div>
    @php
    $visible = $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null;
    $editable = $visible && $application->payment_method === null;
    $payment_method = $application !== null && $application->payment_method !== null ? $application->payment_method : request()->input("payment-method", null);
    $payment_directdebit_accountholder = $application !== null && $application->directdebit !== null ? $application->directdebit->accountholder : request()->input("accountholder", "");
    $payment_directdebit_iban = $application !== null && $application->directdebit !== null ? $application->directdebit->iban : request()->input("iban", "");
    $payment_directdebit_bic = $application !== null && $application->directdebit !== null ? $application->directdebit->bic : request()->input("bic", "");
    @endphp
    <div id="membership-payment-method" @if(!$visible)class="disabled"@endif>
        <h3>4. Ihre Zahlungsmethode
            @if($visible && !$editable)
            <a href="{{  route("membership_form", array_merge(Request::all(), [Request::route("application_id")], ["edit" => "membership-payment-method"])) }}">Bearbeiten</a>
            @endif
            <div class="funding-sources">
                <img src="/img/funding_source/sepa.svg" alt="SEPA">
                <img src="/img/funding_source/card.svg" alt="Creditcard">
                <img src="/img/funding_source/paypal.svg" alt="PayPal">
            </div>
        </h3>
        @if($visible)
        @if(isset($errors) && $errors->has("payment-method"))
            @foreach($errors->get("payment-method") as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif
        <div @if(!$editable)class="disabled"@endif>
            <input type="radio" name="payment-method" id="payment-method-directdebit" value="directdebit"
                @if(in_array($payment_method, [null, "directdebit"]) )checked @endif
                required>
            <label for="payment-method-directdebit">SEPA Lastschrift</label>
            <input type="radio" name="payment-method" id="payment-method-banktransfer" value="banktransfer"
                @if($payment_method==="banktransfer" )checked @endif required>
            <label for="payment-method-banktransfer">Banküberweisung</label>
            <input type="radio" name="payment-method" id="payment-method-paypal" class="js-only" value="paypal"
                @if($payment_method==="paypal" )checked @endif required>
            <label for="payment-method-paypal" class="js-only">PayPal</label>
            <input type="radio" name="payment-method" id="payment-method-creditcard" class="js-only" value="card"
                @if($payment_method==="card" )checked @endif required data-clientid="{{ config("metager.metager.paypal.membership.client_id") }}">
            <label for="payment-method-creditcard" class="js-only">Kredit-/Debitkarte</label>
            <div id="directdebit-data" class="info-container">
                @if(isset($errors) && $errors->has("iban"))
                    @foreach($errors->get("iban") as $error)
                        <div class="error">{{ $error }}</div>
                    @endforeach
                @endif
                <div class="input-group accountholder">
                    <label for="accountholder">Kontoinhaber (falls abweichend)</label>
                    <input type="text" name="accountholder" id="accountholder" placeholder="Max Mustermann"
                        value="{{ $payment_directdebit_accountholder }}">
                </div>
                <div class="input-group iban">
                    <label for="iban">IBAN</label>
                    <input type="text" name="iban" id="iban" placeholder="DE80 1234 5678 9012 3456 78"
                        value="{{ $payment_directdebit_iban }}">
                </div>
                <div class="input-group bic">
                    <label for="bic">BIC (optional)</label>
                    <input type="text" name="bic" id="bic" placeholder=""
                        value="{{ $payment_directdebit_bic }}">
                </div>
            </div>
            <div id="paypal-data" class="info-container">
                <div>Mit Abschicken des Formulars werden Sie zwecks Authorisierung der Mitgliedsbeiträge zu PayPal weitergeleitet.</div>
                <div>@lang('membership.application.payment_block')</div>
            </div>
            <div id="creditcard-data" class="info-container" data-loading-text="{{ __('spende.execute-payment.card.loading') }}" data-is-update="{{ $application !== null ? $application->is_update : false }}">
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
                <div id="payment-block">@lang('membership.application.payment_block')</div>
            </div>
        </div>
        @endif
    </div>
    @if($editable)
    <button type="submit" class="btn btn-primary">Abschicken</button>
    @endif
    @if($application !== null && $application->id !== null)
    @if($application->is_update && $application->isComplete())
    <div class="alert alert-success">@lang('membership.application.update_hint')</div>
    @endif
    <a href="{{ route("membership_abort", ["application_id" => $application->id]) }}" class="btn btn-disabled">
        @if($application === null || !$application->is_update)
        @lang('membership.application.cancel.application')
        @else
        @lang('membership.application.cancel.update')
        @endif
    </a>
    @endif
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