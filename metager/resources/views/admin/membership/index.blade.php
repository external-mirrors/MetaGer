@extends('layouts.subPages')

@section('title', $title)

@section('content')
    @if(request()->filled("success"))
        <div class="alert alert-success">{{ request("success") }}</div>
    @endif
    @if(request()->filled("error"))
        <div class="alert alert-danger">{{ request("error") }}</div>
    @endif
    <div id="membership" , class="card">
        <h1>Mitgliedsanträge Admin</h1>
        @if(sizeof($membership_applications) === 0 && sizeof($reductions) === 0)
            <div class="alert alert-success">Aktuell nichts zu tun</div>
        @endif
        @if(sizeof($membership_applications) > 0)
            <h1>Aufnahmeanträge</h1>
            <table>
                <thead>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Zahlungsmethode</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($membership_applications as $membership_application)
                        <tr>
                            <td>
                                <a href="https://suma-ev.de/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fcontact%2Fview&reset=1&cid={{ $membership_application["contact_id"] }}&selectedChild=member"
                                    target="_blank">
                                    {{ $membership_application["contact_id.addressee_display"] }}
                                </a>
                            </td>
                            <td>{{ number_format($membership_application["Beitrag.Monatlicher_Mitgliedsbeitrag"], 2, ",", ".") . "€ " }}
                            </td>
                            <td>
                                <div>{{ $membership_application["Beitrag.Zahlungsweise:label"] }}</div>
                                @if($membership_application["Beitrag.Zahlungsweise:label"] === "Lastschrift")
                                    @if($membership_application["Beitrag.Kontoinhaber"] !== null)
                                        <div>Kontoinhaber: {{ $membership_application["Beitrag.Kontoinhaber"] }}</div>
                                    @endif
                                    <div>IBAN: {{ iban_to_human_format($membership_application["Beitrag.IBAN"]) }}</div>
                                @elseif($membership_application["Beitrag.Zahlungsweise:label"] === "PayPal")
                                    <div>PayPal Vault: {{ $membership_application["Beitrag.PayPal_Vault"] }}</div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_accept") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application["id"] }}">
                                    <input type="submit" name="action" value="Annehmen" class="btn btn-default">
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_deny") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application["id"] }}">
                                    <input type="submit" id="membership-deny" name="action" value="Ablehnen" class="btn btn-danger">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if(sizeof($reductions) > 0)
            <h1>Nachweise für Beitragsminderung</h1>
            <table>
                <thead>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Nachweis</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($reductions as $reduction)
                        <tr>
                            <td>{{ $reduction->company . implode(" ", [$reduction->title, $reduction->firstname, $reduction->lastname]) }}
                            </td>
                            <td>{{ $reduction->amount . "€ " . $reduction->interval }}</td>
                            <td>
                                <a href="{{ route("membership_admin_reduction", ["reduction_id" => $reduction->id]) }}"
                                    target="_blank">Nachweis
                                    für Beitragsminderung</a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_reduction_accept") }}">
                                    <input type="hidden" name="id" value="{{ $reduction->id }}">
                                    <input type="date" name="reduction_until" id="reduction-until"
                                        min="{{ now()->format("Y-m-d") }}" max="{{ now()->addYears(20)->format("Y-m-d") }}"
                                        value="{{ now()->addYear()->format("Y-m-d") }}">
                                    <input type="submit" id="membership-reduction-deny" name="action" value="Annehmen"
                                        class="btn btn-success">
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_reduction_deny") }}">
                                    <input type="hidden" name="id" value="{{ $reduction->id }}">
                                    <input type="submit" id="membership-reduction-deny" name="action" value="Ablehnen"
                                        class="btn btn-danger">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection