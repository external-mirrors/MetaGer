@extends('layouts.subPages')

@section('title', $title)

@section('content')
<h1>MetaGer Logs API (Admin)</h1>
<table>
    <thead>
        <td>Email</td>
        <td>Discount</td>
        <td>Created At</td>
        <td>Updated At</td>
        <td>Last Activity</td>
        <td>Actions</td>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->email }}</td>
                <td>{{ $user->discount }}</td>
                <td>{{ $user->created_at }}</td>
                <td>{{ $user->updated_at }}</td>
                <td>{{ $user->last_activity }}</td>
                <td>
                    <a href="{{ route('logs:admin', ["email" => $user->email, "action" => "update"]) }}">Bearbeiten</a>
                    <a href="{{ route('logs:admin', ["email" => $user->email, "action" => "delete"]) }}">Löschen</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<form action="{{ route('logs:admin') }}" method="post">
    <h2>Add a user</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error_message)
                    <li>{{ $error_message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <label for="email">Email</label>
    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="max@mustermann.de">
    <label for="discount">Discount</label>
    <input type="number" name="discount" id="discount" value="{{ old('discount') }}" placeholder="0-100%">
    <button class="btn btn-default" type="submit">Abschicken</button>
</form>
@endsection