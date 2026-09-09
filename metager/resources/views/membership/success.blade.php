@extends('layouts.subPages')

@section('title', $title)

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
{{--
    Der Antrag ist abgeschickt.

    Zwei Dinge stehen hier, und beide stehen sonst nirgends:

    **Der Schlüssel.** Er wurde im ersten Schritt des Formulars still erstellt
    und per Cookie gesetzt; gesehen hat ihn bis hierher niemand. Die
    Willkommensmail nennt ihn erst, wenn der Antrag bearbeitet ist, und bis dahin
    können Tage vergehen — wessen Cookie in der Zwischenzeit verlorengeht, hat
    ohne diese Seite nichts in der Hand. Deshalb derselbe Block wie auf
    /schluessel-erstellen (parts/key-backup.blade.php) und nicht bloß eine Zeile
    zum Abschreiben.

    **Wie es weitergeht.** Der Antrag wird von Hand geprüft, und darauf folgt
    eine Mail; das ist der Satz, den jemand mitnehmen muss, der die Seite
    schließt. Der Knopf darunter führt weiter — für die meisten in die Suche, aus
    dem Custom Tab der App heraus über den verifizierten App Link zurück in die
    App ({@see App\Landing\AppCallback}). Ein Knopf und keine Weiterleitung: diese
    Seite ist die einzige, die den Schlüssel zeigt.
--}}
<h1 class="page-title">@lang('membership.title')</h1>
<div class="success">@lang("membership.success")</div>

<div id="data">
    <div>@lang("membership.data.description")</div>
    @if($application->contact !== null)
        <div class="input-group">
            <label for="name">@lang("membership.data.name")</label>
            <input type="text" name="name" id="name"
                value="{{ $application->contact->title . " " . $application->contact->first_name . " " . $application->contact->last_name }}"
                readonly>
        </div>
        <div class="input-group">
            <label for="email">@lang("membership.data.email")</label>
            <input type="text" name="email" id="email"
                value="{{ $application->contact->email }}"
                readonly>
        </div>
    @elseif($application->company !== null)
        <div class="input-group">
            <label for="company">@lang("membership.data.company")</label>
            <input type="text" name="company" id="company"
                value="{{ $application->company->company }}"
                readonly>
        </div>
        <div class="input-group">
            <label for="email">@lang("membership.data.email")</label>
            <input type="text" name="email" id="email"
                value="{{ $application->company->email }}"
                readonly>
        </div>
    @endif
    <div class="input-group">
        <label for="name">@lang("membership.data.amount")</label>
        @php
        $amount = match($application->interval){
            "monthly" => $application->amount,
            "quarterly" => $application->amount * 3,
            "six-monthly" => $application->amount * 6,
            "annual" => $application->amount * 12
        };
        @endphp
        <input type="text" name="amount" id="amount" value="{{ number_format($amount, 2, ",") }}€ {{ __("membership.data.payment.interval.{$application->interval}") }}" readonly>
    </div>
    <div class="input-group">
        <label for="payment_method">@lang("membership.data.payment_method")</label>
        <input type="text" name="payment_method" id="payment_method"
            value="@lang("membership.data.payment_methods.{$application->payment_method}")"
            readonly>
    </div>
</div>

@if($key !== null)
<div class="membership-key">
    <h2 class="membership-key__heading">@lang('membership.key.heading')</h2>
    <p class="membership-key__description">
        @lang('membership.key.description')
        @switch($application->payment_method)
        @case("banktransfer")
        @case("directdebit")
        <span>@lang('membership.key.later')</span>
        @break
        @case("paypal")
        @case("card")
        <span>@lang('membership.key.now')</span>
        @break
        @endswitch
    </p>

    @include("parts.key-backup", ["key" => $key, "settingsUrl" => $settingsUrl, "qrUri" => $qrUri, "keyLabel" => __("membership.key.label")])
</div>
@endif

{{--
    Der Satz, der in jedem Fall stehen bleiben muss — auch für den, der
    gleich in die App zurückspringt und die Seite nie wiedersieht.
--}}
<div class="membership-next">
    <p class="membership-next__review">@lang('membership.next.review')</p>

    @if($handback !== null)
        {{--
            away() wäre eine Weiterleitung; hier ist es ein Link, weil die
            Seite darüber vorher gelesen werden soll. Das Ziel ist ein von
            Android verifizierter App Link und keine Adresse dieser Seite —
            deshalb kein route().
        --}}
        <a class="membership-next__action" href="{{ $handback }}">@lang('membership.next.app')</a>
    @else
        <a class="membership-next__action" href="{{ LaravelLocalization::getLocalizedURL(null, '/') }}">@lang('membership.next.search')</a>
    @endif
</div>
@endsection
