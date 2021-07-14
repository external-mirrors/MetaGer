@extends('layouts.subPages')

@section('title', $title )

@section('content')

	<div>
		<h1 class="page-title">{{ trans('search-engine.head.1') }}</h1>

		<div class="card-heavy">
			<h2>{{ trans('search-engine.head.2') }}</h2>
			<p>{!! trans('search-engine.text.1',["transparenz" => "https://metager.de/transparency"]) !!}</p>
		</div>
		<div class="enginecontainer">
		<div class="card-heavy" >
			<h2>{{ trans('search-engine.head.3') }}</h2>		
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1') }}</span>{{ trans('search-engine.text.2.1.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.2') }}</span>{{ trans('search-engine.text.2.2.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.2.3.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.2.4.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.2.5.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.6') }}</span>{{ trans('search-engine.text.2.6.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.2.7.1') }}</p>


		</div>
		<div class="card-heavy">
		<h2>{{ trans('search-engine.head.4') }}</h2>		
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1') }}</span>{{ trans('search-engine.text.3.1') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.2') }}</span>{{ trans('search-engine.text.3.2') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.3.3') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.3.4') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.3.5') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.6') }}</span>{{ trans('search-engine.text.3.6') }}</p>
			<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.3.7') }}</p>
		
		</div>
		<div class="card-heavy">
		<h2>{{ trans('search-engine.head.5') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1') }}</span>{{ trans('search-engine.text.4.1') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.4.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.4.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.4.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.4.5') }}</p>		
	</div>
		<div class="card-heavy">
		<h2>{{ trans('search-engine.head.6') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1') }}</span>{{ trans('search-engine.text.5.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.5.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.5.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.5.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.6') }}</span>{{ trans('search-engine.text.5.5') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.5.6') }}</p>

	</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.7') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1')}}</span>{{ trans('search-engine.text.6.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.6.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.6.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.6.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.6.5') }}</p>	
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.8') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1')}}</span>{{ trans('search-engine.text.7.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.7.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.7.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.7.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.7.5') }}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.9') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1')}}</span>{{ trans('search-engine.text.8.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.8.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.8.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.8.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.8.5') }}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.10') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1')}}</span>{{ trans('search-engine.text.9.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.9.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.9.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.9.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.9.5') }}</p>
		</div>
		<div class="card-heavy">
		<h2>{{ trans('search-engine.head.11') }}</h2>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.1')}}</span>{{ trans('search-engine.text.10.1')}}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.3') }}</span>{{ trans('search-engine.text.10.2') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.4') }}</span>{{ trans('search-engine.text.10.3') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.5') }}</span>{{ trans('search-engine.text.10.4') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.6') }}</span>{{ trans('search-engine.text.10.5') }}</p>
		<p><span class="search-engine-dt">{{ trans('search-engine.text.2.7') }}</span>{{ trans('search-engine.text.10.6') }}</p>
		</div>
		</div>	
	</div>
@endsection
