@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div id="membership" , class="card">
        <h1>Aufnahmeanträge</h1>
        <table>
            <thead>
                <th>Name</th>
                <th>Beitrag</th>
                <th>Zahlungsmethode</th>
                <th></th>
            </thead>
            <tbody>
                @foreach(DB::table("membership")->get() as $membership)
                    <tr>
                        <td>{{ $membership->company . implode(" ", [$membership->title, $membership->firstname, $membership->lastname]) }}
                        </td>
                        <td>{{ $membership->amount . "€ " . $membership->interval }}</td>
                        <td>{{ $membership->payment_method }}</td>
                        <td>
                            <form method="POST" action="{{ route("membership_admin_accept") }}">
                                <input type="hidden" name="id" value="{{ $membership->id }}">
                                <input type="submit" name="action" value="Annehmen" class="btn btn-default">
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route("membership_admin_deny") }}">
                                <input type="hidden" name="id" value="{{ $membership->id }}">
                                <input type="submit" id="membership-deny" name="action" value="Ablehnen" class="btn btn-danger">
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection