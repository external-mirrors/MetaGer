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
        @if(sizeof($membership_applications) === 0 && sizeof($reduction_requests) === 0 && sizeof($membership_update_requests) === 0)
            <div class="alert alert-success">Aktuell nichts zu tun</div>
        @endif
        @if(sizeof($membership_applications) > 0)
            <h1>Aufnahmeanträge</h1>
            <table>
                <thead>
                    <th>Datum</th>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Zahlungsmethode</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($membership_applications as $membership_application)
                        <tr>
                            <td title="{{ $membership_application->created_at->format("d.m.Y H:i:s") }}">
                                {{ $membership_application->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if($membership_application->contact !== null)
                                    {{ $membership_application->contact->title . " " . $membership_application->contact->first_name . " " . $membership_application->contact->last_name }}
                                @elseif($membership_application->company !== null)
                                    {{ $membership_application->company->company }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $amount = match ($membership_application->interval) {
                                        "monthly" => $membership_application->amount,
                                        "quarterly" => $membership_application->amount * 3,
                                        "six-monthly" => $membership_application->amount * 6,
                                        "annual" => $membership_application->amount * 12,
                                        null => 0
                                    };
                                @endphp
                                @if($membership_application->amount !== null)
                                    {{ number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$membership_application->interval}") }}
                                @endif
                            </td>
                            <td>
                                <div>@lang("membership.data.payment_methods.{$membership_application->payment_method}")</div>
                                @if($membership_application->payment_method === "directdebit")
                                    @if($membership_application->directdebit->accountholder !== null)
                                        <div>Kontoinhaber: {{ $membership_application->directdebit->accountholder }}</div>
                                    @endif
                                    <div>IBAN: {{ iban_to_human_format($membership_application->directdebit->iban) }}</div>
                                    @if($membership_application->directdebit->bic !== null)
                                        <div>BIC: {{ $membership_application->directdebit->bic }}</div>
                                    @endif
                                @elseif(in_array($membership_application->payment_method, ["paypal", "card"]))
                                    <div>PayPal Vault: {{ $membership_application->paypal->vault_id }}</div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_accept") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application->id }}">
                                    <input type="submit" name="action" value="Annehmen" class="btn btn-default application-accept">
                                    <dialog>
                                        <div>
                                            <label for="message">Zusätzlicher Text für die Willkommensmail (optional)</label>
                                            <textarea name="message" id="message" rows="10"
                                                placeholder="Wird als eigener erster Absatz direkt nach dem Namen am Anfang eingefügt."></textarea>
                                            <input type="submit" name="action" value="Annehmen"
                                                class="btn btn-success membership-reduction-deny">
                                            <button class="close-modal btn btn-default" autofocus>Schließen</button>
                                        </div>
                                    </dialog>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_deny") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application->id }}">
                                    <input type="submit" name="action" value="Ablehnen" class="btn btn-danger application-deny">
                                    <dialog>
                                        <div>
                                            <label for="message">Zusätzlicher Text für die Ablehnungsmail (optional)</label>
                                            <textarea name="message" id="message" rows="10"
                                                placeholder="Wird als Begründung für die Ablehnung in den Text eingefügt."></textarea>
                                            <input type="submit" name="action" value="Ablehnen"
                                                class="btn btn-danger membership-reduction-deny">
                                            <button class="close-modal btn btn-default" autofocus>Schließen</button>
                                        </div>
                                    </dialog>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if(sizeof($membership_update_requests) > 0)
            <h1>Änderungsanträge</h1>
            <table>
                <thead>
                    <th>Datum</th>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Zahlungsmethode</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($membership_update_requests as $membership_application)
                        <tr>
                            <td title="{{ $membership_application->created_at->format("d.m.Y H:i:s") }}">
                                {{ $membership_application->created_at->diffForHumans() }}
                            </td>
                            <td>
                                <a href="https://suma-ev.de/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fcontact%2Fview%2Fmembership&action=view&reset=1&cid={{ $membership_application->crm_contact }}&id={{ $membership_application->crm_membership }}&context=membership&selectedChild=member"
                                    target="_blank">
                                    @if($membership_application->contact !== null)
                                        {{ $membership_application->contact->title . " " . $membership_application->contact->first_name . " " . $membership_application->contact->last_name }}
                                    @elseif($membership_application->company !== null)
                                        {{ $membership_application->company->company }}
                                    @endif
                                </a>
                            </td>
                            <td>
                                @php
                                    $amount = match ($membership_application->interval) {
                                        "monthly" => $membership_application->amount,
                                        "quarterly" => $membership_application->amount * 3,
                                        "six-monthly" => $membership_application->amount * 6,
                                        "annual" => $membership_application->amount * 12,
                                        null => 0
                                    };
                                @endphp
                                @if($membership_application->amount !== null)
                                    {{ number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$membership_application->interval}") }}
                                @endif
                            </td>
                            <td>
                                <div>@lang("membership.data.payment_methods.{$membership_application->payment_method}")</div>
                                @if($membership_application->payment_method === "directdebit")
                                    @if($membership_application->directdebit->accountholder !== null)
                                        <div>Kontoinhaber: {{ $membership_application->directdebit->accountholder }}</div>
                                    @endif
                                    <div>IBAN: {{ iban_to_human_format($membership_application->directdebit->iban) }}</div>
                                    @if($membership_application->directdebit->bic !== null)
                                        <div>BIC: {{ $membership_application->directdebit->bic }}</div>
                                    @endif
                                @elseif(in_array($membership_application->payment_method, ["paypal", "card"]))
                                    <div>PayPal Vault: {{ $membership_application->paypal->vault_id }}</div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_accept") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application->id }}">
                                    <input type="hidden" name="update-request" value="true">
                                    <input type="submit" name="action" value="Annehmen" class="btn btn-default">
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_deny") }}">
                                    <input type="hidden" name="id" value="{{ $membership_application->id }}">
                                    <input type="hidden" name="update-request" value="true">
                                    <input type="submit" name="action" value="Ablehnen" class="btn btn-danger membership-deny">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if(sizeof($reduction_requests) > 0)
            <h1>Nachweise für Beitragsminderung</h1>
            <table>
                <thead>
                    <th>Datum</th>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Nachweis</th>
                    <th></th>
                </thead>
                <tbody>
                    @foreach($reduction_requests as $reduction_application)
                        <tr>
                            <td title="{{ $reduction_application->reduction->created_at->format("d.m.Y H:i:s") }}">
                                {{ $reduction_application->reduction->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if($reduction_application->contact !== null)
                                    {{ $reduction_application->contact->title . " " . $reduction_application->contact->first_name . " " . $reduction_application->contact->last_name }}
                                @elseif($reduction_application->company !== null)
                                    {{ $reduction_application->company->company }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $amount = match ($reduction_application->interval) {
                                        "monthly" => $reduction_application->amount,
                                        "quarterly" => $reduction_application->amount * 3,
                                        "six-monthly" => $reduction_application->amount * 6,
                                        "annual" => $reduction_application->amount * 12,
                                        null => 0
                                    };
                                @endphp
                                @if($reduction_application->amount !== null)
                                    {{ number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$reduction_application->interval}") }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route("membership_admin_reduction", ["reduction_id" => $reduction_application->reduction->id]) }}"
                                    target="_blank">Nachweis
                                    für Beitragsminderung</a>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_reduction_accept") }}">
                                    <input type="hidden" name="id" value="{{ $reduction_application->reduction->id }}">
                                    <input type="date" name="reduction_until" id="reduction-until"
                                        min="{{ now()->format("Y-m-d") }}" max="{{ now()->addYears(20)->format("Y-m-d") }}"
                                        value="{{ now()->addYear()->format("Y-m-d") }}">
                                    <input type="submit" name="action" value="Annehmen"
                                        class="btn btn-success membership-reduction-accept">
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route("membership_admin_reduction_deny") }}">
                                    <input type="hidden" name="id" value="{{ $reduction_application->reduction->id }}">
                                    <input type="submit" id="membership-reduction-deny-modal" name="action" value="Ablehnen"
                                        class="btn btn-danger reduction-deny">
                                    <dialog>
                                        <div>
                                            <label for="message">Begründung für den Nutzer</label>
                                            <textarea name="message" id="message" rows="10" required></textarea>
                                            <input type="submit" name="action" value="Ablehnen"
                                                class="btn btn-danger membership-reduction-deny">
                                            <button class="close-modal btn btn-default" autofocus>Schließen</button>
                                        </div>
                                    </dialog>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(sizeof($unfinished_applications) > 0)
            <h1>Unfertige Aufnahmeanträge</h1>
            <table>
                <thead>
                    <th>Datum</th>
                    <th>Name</th>
                    <th>Beitrag</th>
                    <th>Zahlungsmethode</th>
                </thead>
                <tbody>
                    @foreach($unfinished_applications as $membership_application)
                        <tr>
                            <td title="{{ $membership_application->created_at->format("d.m.Y H:i:s") }}">
                                {{ $membership_application->created_at->diffForHumans() }}
                            </td>
                            <td>
                                @if($membership_application->contact !== null)
                                    {{ $membership_application->contact->title . " " . $membership_application->contact->first_name . " " . $membership_application->contact->last_name }}
                                @elseif($membership_application->company !== null)
                                    {{ $membership_application->company->company }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $amount = match ($membership_application->interval) {
                                        "monthly" => $membership_application->amount,
                                        "quarterly" => $membership_application->amount * 3,
                                        "six-monthly" => $membership_application->amount * 6,
                                        "annual" => $membership_application->amount * 12,
                                        null => 0
                                    };
                                @endphp
                                @if($membership_application->amount !== null)
                                    {{ number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$membership_application->interval}") }}
                                @endif
                            </td>
                            <td>
                                @if($membership_application->payment_method !== null)
                                    <div>@lang("membership.data.payment_methods.{$membership_application->payment_method}")</div>
                                    @if($membership_application->payment_method === "directdebit")
                                        @if($membership_application->directdebit->accountholder !== null)
                                            <div>Kontoinhaber: {{ $membership_application->directdebit->accountholder }}</div>
                                        @endif
                                        <div>IBAN: {{ iban_to_human_format($membership_application->directdebit->iban) }}</div>
                                        @if($membership_application->directdebit->bic !== null)
                                            <div>BIC: {{ $membership_application->directdebit->bic }}</div>
                                        @endif
                                    @elseif(in_array($membership_application->payment_method, ["paypal", "card"]))
                                        <div>PayPal Vault: {{ $membership_application->paypal->vault_id }}</div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection