@extends('layouts.subPages')

@section('title', $title )

@section('content')
    <div id="current-user" class="user" data-pcso="{{$picasso_hash}}">
    <table>
        <tbody>
            <tr>
                <td>IP-Adresse</td>
                <td>{{$ip}}</td>
            </tr>
            <tr>
                <td>ID</td>
                <td>{{$user["id"]}}</td>
            </tr>
            <tr>
                <td>User-ID</td>
                <td>{{$user["uid"]}}</td>
            </tr>
            <tr>
                <td>Unused Resultpages</td>
                <td>
                    <form action="" method="post">
                        @if(!empty($picasso_hash))
                        <input type="hidden" name="pcso" value="{{$picasso_hash}}">
                        @endif
                        <input type="number" name="unusedResultPages" id="unusedResultPages" value="{{$user["unusedResultPages"]}}">
                    </form>
                </td>
            </tr>
            <tr>
                <td>Whitelist</td>
                <td>
                    <form action="" method="post">
                        @if(!empty($picasso_hash))
                        <input type="hidden" name="pcso" value="{{$picasso_hash}}">
                        @endif
                        <select name="whitelist" id="locked">
                            <option value="1" @if($user["whitelist"]) selected @endif>True</option>
                            <option value="0" @if(!$user["whitelist"]) selected @endif>False</option>
                        </select>
                    </form>
                </td>
            </tr>
            <tr>
                <td>Locked</td>
                <td>
                    <form action="" method="post">
                        @if(!empty($picasso_hash))
                        <input type="hidden" name="pcso" value="{{$picasso_hash}}">
                        @endif
                        <select name="locked" id="locked">
                            <option value="1" @if($user["locked"]) selected @endif>True</option>
                            <option value="0" @if(!$user["locked"]) selected @endif>False</option>
                        </select>
                    </form>
                </td>
            </tr>
            
            <tr>
                <td>Picasso Enabled</td>
                <td><pre>@if(array_key_exists("picasso_enabled", $user)){{$user["picasso_enabled"]}}@else false @endif</pre></td>
            </tr>
            <tr>
                <td>Expiration</td>
                <td><pre>{{$user["expiration"]->format("d.m.Y H:i:s")}}</pre></td>
            </tr>
        </tbody>
    </table>
    </div>
    @foreach($userList as $user_current)
    @if($user_current["uid"] === $user["uid"])
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
                <td>Picasso Enabled</td>
                <td><pre>@if(array_key_exists("picasso_enabled", $user_current)){{$user_current["picasso_enabled"]}}@else false @endif</pre></td>
            </tr>
            <tr>
                <td>Expiration</td>
                <td><pre>{{$user_current["expiration"]->format("d.m.Y H:i:s")}}</pre></td>
            </tr>
        </tbody>
    </table>
    </div>
    @endforeach
   
@endsection
