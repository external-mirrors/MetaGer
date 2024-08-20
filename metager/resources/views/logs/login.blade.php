@extends('layouts.subPages')

@section('title', $title)

@section('content')
<h1>MetaGer Logs API</h1>
<p>Bitte melde dich an, um Zugriff auf dein Konto zu erhalten.</p>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error_message)
                <li>{{ $error_message }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route("logs:login:post")}}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="input-group">
        <label for="email">Email Addresse</label>
        @if(session("email"))
            <input type="email" name="email" id="email" value="{{session('email')}}" placeholder="max@mustermann.de"
                required disabled>
        @else
            <input type="email" name="email" id="email" value="{{old('email')}}" placeholder="max@mustermann.de" required>
        @endif
    </div>
    @if(session("email"))
        <p>Falls dieser Account bereits registriert ist, haben wir dir soeben einen Login Code per Email gesendet. Bitte
            trage diesen ein um dich anzumelden.</p>
        <div class="input-group">
            <label for="code">Login Code</label>
            <input type="text" name="code" id="code" placeholder="123456" required>
        </div>
    @endif
    <button class="btn btn-default" type="submit">Abschicken</button>
</form>
@endsection