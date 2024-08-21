@extends('layouts.subPages')

@section('title', $title)

@section('content')
<div id="login">
    <h1>MetaGer Logs API</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error_message)
                    <li>{{ $error_message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <p>@lang('logs.login.hint')</p>
    <form action="{{ route("logs:login:post")}}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @if(session("email"))
            <input type="hidden" name="email" value="{{session('email')}}">
        @endif
        <div class="input-group">
            <label for="email">@lang('logs.login.email')</label>
            @if(session("email"))
                <input type="email" name="email" id="email" value="{{session('email')}}" placeholder="max@mustermann.de"
                    required disabled>
            @else
                <input type="email" name="email" id="email" value="{{old('email')}}" placeholder="max@mustermann.de"
                    required>
            @endif
        </div>
        @if(session("email"))
            <div class="input-group">
                <label for="code">@lang('logs.login.code')</label>
                <input type="text" name="code" id="code" placeholder="123456" required>
            </div>
            <p>@lang('logs.login.email_sent')</p>
        @endif
        <button class="btn btn-default" type="submit">@lang('logs.login.submit')</button>
        @if(session("email"))
            <a class="reset" href="{{ route('logs:login', ['reset' => '1']) }}">@lang('logs.login.restart')</a>
        @endif
    </form>
</div>
@endsection