@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">@lang('faktencheck.heading.1')</h1>
<div class="card">
	<p>@lang('faktencheck.paragraph.1')</p>
	<p>@lang('faktencheck.paragraph.2')</p>

	<ol id="checklist">
		<li>@lang('faktencheck.list.1.0')</li>
		<ul>
			<li>@lang('faktencheck.list.1.1')</li>
			<li>@lang('faktencheck.list.1.2')</li>
			<li>@lang('faktencheck.list.1.3')</li>
			<li>@lang('faktencheck.list.1.4')</li>
		</ul>
		<li>@lang('faktencheck.list.2.0')</li>
		<ul>
			<li>@lang('faktencheck.list.2.1')</li>
			<li>@lang('faktencheck.list.2.2')</li>
			<li>@lang('faktencheck.list.2.3')</li>
			<li>@lang('faktencheck.list.2.4')</li>
			<li>@lang('faktencheck.list.2.5')</li>
		</ul>
		<li>@lang('faktencheck.list.3.0')</li>
		<p>@lang('faktencheck.list.3.1')</p>
		<ul>
			<li>@lang('faktencheck.list.3.2')</li>
			<li>@lang('faktencheck.list.3.3')</li>
			<li>@lang('faktencheck.list.3.4')</li>
			<li>@lang('faktencheck.list.3.5')</li>

		</ul>
		<li>@lang('faktencheck.list.4.0')</li>
		<p>@lang('faktencheck.list.4.1')</p>
		<ul>
			<li>@lang('faktencheck.list.4.2')</li>
			<li>@lang('faktencheck.list.4.3')</li>
		</ul>
		<li>@lang('faktencheck.list.5.0')</li>
		<ul>
			<li>@lang('faktencheck.list.5.1')</li>
			<li>@lang('faktencheck.list.5.2')</li>
			<li>@lang('faktencheck.list.5.3')</li>
		</ul>
		<li>@lang('faktencheck.list.7')</li>
	</ul>

</div>
@endsection