@extends('layouts.subPages')

@section('title', $title )

@section('content')
@foreach($verificators as $verificator)
    <div class="verificator">
        <h1>{{$verificator::class}}</h1>
        <div id="current-user" class="user">
        <table>
            <tbody>
                <tr>
                    <td>IP-Adresse</td>
                    <td>{{$ip}}</td>
                </tr>
                <tr>
                    <td>ID</td>
                    <td>{{$verificator->id}}</td>
                </tr>
                <tr>
                    <td>User-ID</td>
                    <td>{{$verificator->uid}}</td>
                </tr>
                <tr>
                    <td>Unused Resultpages</td>
                    <td>
                        <form action="" method="post" target="__top">
                            <input type="hidden" name="verificator" value="{{$verificator::class}}">
                            <input type="hidden" name="key" value="{{ $key }}">
                            <input type="number" name="unusedResultPages" id="unusedResultPages" value="{{$verificator->getVerificationCount()}}">
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Whitelist</td>
                    <td>
                        <form action="" method="post" target="__top">
                            <input type="hidden" name="verificator" value="{{$verificator::class}}">
                            <input type="hidden" name="key" value="{{ $key }}">
                            <select name="whitelist" id="locked">
                                <option value="1" @if($verificator->isWhitelisted()) selected @endif>True</option>
                                <option value="0" @if(!$verificator->isWhitelisted()) selected @endif>False</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Locked</td>
                    <td>
                        <form action="" method="post" target="__top">
                            <input type="hidden" name="verificator" value="{{$verificator::class}}">
                            <input type="hidden" name="key" value="{{ $key }}">
                            <select name="locked" id="locked">
                                <option value="1" @if($verificator->isLocked()) selected @endif>True</option>
                                <option value="0" @if(!$verificator->isLocked()) selected @endif>False</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Expiration</td>
                    <td><pre>{{$verificator->getExpiration()->format("d.m.Y H:i:s")}}</pre></td>
                </tr>
            </tbody>
        </table>
        
        </div>
        @foreach($verificator->getUserList() as $user_current)
        @if($user_current["uid"] === $verificator->uid)
            @continue
        @endif
        <div class="user">
        <h3>{{$user_current["uid"]}}</h3>
        <table>
            <tbody>
                <tr>
                    <td>ID</td>
                    <td>{{$user_current["id"]}}</td>
                </tr>
                <tr>
                    <td>Unused Resultpages</td>
                    <td>
                        <form action="" method="post">
                            <input type="number" name="unusedResultPages" id="unusedResultPages" readonly value="{{$user_current["unusedResultPages"]}}">
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Whitelist</td>
                    <td>
                        <form action="" method="post">
                            <select name="whitelist" id="locked" disabled>
                                <option value="1" @if($user_current["whitelist"]) selected @endif >True</option>
                                <option value="0" @if(!$user_current["whitelist"]) selected @endif >False</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Locked</td>
                    <td>
                        <form action="" method="post">
                            <select name="locked" id="locked" disabled>
                                <option value="1" @if($user_current["locked"]) selected @endif>True</option>
                                <option value="0" @if(!$user_current["locked"]) selected @endif>False</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Expiration</td>
                    <td><pre>{{$user_current["expiration"]->format("d.m.Y H:i:s")}}</pre></td>
                </tr>
            </tbody>
        </table>
        </div>
        @endforeach
    </div>
    @endforeach
   
@endsection
