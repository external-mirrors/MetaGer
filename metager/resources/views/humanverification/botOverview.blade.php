@extends('layouts.subPages')

@section('title', $title )

@section('content')
    <style>
        table form {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        td:nth-child(1) {
            padding-right: 8px;
        }
    </style>
    <table>
        <tbody>
            <tr>
                <td>IP-Adresse</td>
                <td><pre>{{$ip}}</pre></td>
            </tr>
            <tr>
                <td>ID</td>
                <td><pre>{{$user["id"]}}</pre></td>
            </tr>
            <tr>
                <td>User-ID</td>
                <td><pre>{{$user["uid"]}}</pre></td>
            </tr>
            <tr>
                <td>Unused Resultpages</td>
                <td>
                    <form action="" method="post">
                        <input onchange="this.form.submit()" type="number" name="unusedResultPages" id="unusedResultPages" value="{{$user["unusedResultPages"]}}">
                    </form>
                </td>
            </tr>
            <tr>
                <td>Whitelist</td>
                <td>
                    <form action="" method="post">
                        <select name="whitelist" id="locked" onchange="this.form.submit()">
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
                        <select name="locked" id="locked" onchange="this.form.submit()">
                            <option value="1" @if($user["locked"]) selected @endif>True</option>
                            <option value="0" @if(!$user["locked"]) selected @endif>False</option>
                        </select>
                    </form>
                </td>
            </tr>
            <tr>
                <td>Locked Key</td>
                <td><pre>{{$user["lockedKey"]}}</pre></td>
            </tr>
            <tr>
                <td>Expiration</td>
                <td><pre>{{$user["expiration"]}}</pre></td>
            </tr>
        </tbody>
    </table>
    {{ dd($userList) }}
@endsection
