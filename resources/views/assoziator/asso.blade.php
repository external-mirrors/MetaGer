@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <h1 class="page-title">{{ trans('asso.head.1') }}</h1>
    <div class="card-heavy">
        <p>{{ trans('asso.1.1') }} <a
                href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/datenschutz') }}"
                target="_blank">{{ trans('asso.1.2') }}</a>{{ trans('asso.1.3') }}.</p>


        <form method="get" class="form-inline"
            action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('assoresults')) }}">
            <input type="text" class="form-control search-input-mini" placeholder="{{ trans('asso.search.placeholder') }}"
                @if (isset($keywords)) value="{{ $keywords }}" @endif name="q" required autofocus /><button type="submit" class="search-btn-mini"><img id="asso-search-icon" class= "mg-icon" src="/img/icon-lupe.svg" alt="{{ trans('icon-lupe.alt') }}"></button>
        </form>
    </div>
    @if (isset($words))
        <div class="card-heavy">
            <div class="table-responsive">
                <table class="table">
                    <caption>Assoziationen für "{{ $keywords }}"</caption>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Assoziation</th>
                            <th>Relevanz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($words as $key => $value)
                            <tr>
                                <td>{{ $i }}</td>
                                <td class="association">
                                    <a class="asso-search-link"
                                        href="{{ action('MetaGerSearch@search', ['eingabe' => $key]) }}"
                                        title="{{ trans('asso.searchasso.title') }}"><i class="fa fa-search"
                                            aria-hidden="true"></i></a>
                                    <a name="q" value="{{ $key }}" class="reasso"
                                        href="{{ route('assoresults', ['q' => $key]) }}"
                                        title="{{ trans('asso.reasso.title') }}">{{ $key }}</a>
                                </td>
                                <td>{{ round(($value / $wordCount) * 100, 2) }}%</td>
                                @php $i++; @endphp
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection