@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Haushalte</h1>
        <p><a href="{{ route('assoc_admin_members') }}">Mitglieder ansehen</a></p>

        <table>
            <thead>
                <th>Name</th>
                <th>Ort</th>
            </thead>
            <tbody>
                @foreach($households as $household)
                    <tr>
                        <td>
                            <a href="{{ route('assoc_admin_household', ['id' => $household->id]) }}">
                                {{ $household->household_name }}
                            </a>
                        </td>
                        <td>{{ $household->city }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin.assoc._pager', ['paginator' => $households])
    </div>
@endsection
