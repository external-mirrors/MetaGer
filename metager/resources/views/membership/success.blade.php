@extends('layouts.subPages')

@section('title', $title)

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
    <h1 class="page-title">@lang('membership.title')</h1>
    <div class="success">Herzlichen Dank für die Übermittlung Ihres Aufnahmeantrags. Wir werden diesen möglichst schnell
        bearbeiten. Anschließend erhalten Sie eine Mail mit weiteren Informationen von uns an die angegebene Addresse.</div>
    @if(request()->filled("new_key"))
        <div>Für die Nutzung von MetaGer wurde ein neuer Schlüssel erstellt und in Ihrem Browser eingerichtet. Je nach
            Zahlungsmethode wird er aber erst nach Bearbeitung Ihres Antrags von uns aufgeladen.</div>
        <div>Notieren Sie den Schlüssel bitte für Ihre Unterlagen. Sie brauchen Ihn, um sich bei Bedarf bei MetaGer anzumelden:
        </div>
        <div class="copyLink">
            <input id="loadSettings" class="loadSettings" type="text" value="{{ request("new_key") }}">
            <button class="js-only btn btn-default">@lang('settings.copy')</button>
        </div>
    @else
        <div>Für die Nutzung von MetaGer wurde Ihr bestehender Schlüssel eingerichtet. Je nach
            Zahlungsmethode wird er aber erst nach Bearbeitung Ihres Antrags von uns aufgeladen.</div>
    @endif
    <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/') }}">@lang('membership.back')</a>
@endsection