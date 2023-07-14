@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
	<h1 class="page-title">{{ trans('search-engine.head.1') }}</h1>

	<div class="card">
		<h2>{{ trans('search-engine.head.2') }}</h2>
		<p>{!! trans('search-engine.text.1.1',["transparenz" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "transparency")]) !!}</p>
		<h2>{{ trans('search-engine.head.3') }}</h2>
		<p>{{ trans('search-engine.text.1.2') }}</p>
		<p>{!! trans('search-engine.text.1.3', ["contact" => route('contact')]) !!}</p>

	</div>
	@foreach($suma_infos as $fokus_name => $suma_list)
	<h1>{{ __('index.foki.' . $fokus_name) }}</h2>
		<div class="enginecontainer">

			@foreach($suma_list as $suma_name => $suma_infos)
			<div class="card">
				<h2><a href="{{$suma_infos->homepage}}" rel="noopener" target="_blank">{{ $suma_infos->display_name }}<img src="/img/icon-outlink.svg" alt="" aria-hidden="true" id="sidebar-img-outlink"></a></h2>
				@if($suma_infos->index_name !== null)
				<p><span class="search-engine-dt">{{ trans('search-engine.text.2.2') }}</span>{{ $suma_infos->index_name }}</p>
				@endif
				@if($suma_infos->founded !== null)
				<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ $suma_infos->founded }}</p>
				@endif
				@if($suma_infos->headquarter !== null)
				<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ $suma_infos->headquarter }}</p>
				@endif
				@if($suma_infos->operator !== null)
				<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ $suma_infos->operator }}</p>
				@endif
				@if($suma_infos->index_size !== null)
				<p><span class="search-engine-dt">{{ trans('search-engine.text.2.6') }}</span>{{ $suma_infos->index_size }}</p>
				@endif
			</div>
			@endforeach
		</div>
		@endforeach
</div>
@endsection