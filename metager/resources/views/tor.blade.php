@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">MetaGer hidden service</h1>
<div class="card">
	<p>@lang('tor.description')</p>
	<a class="btn btn-primary" href="http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/{{LaravelLocalization::getCurrentLocale()}}" role="button">{{trans('tor.torbutton')}}</a>
</div>
{{--
	Filled in by the MetaGer web extension, and hidden without it: only the
	extension knows whether it can reach the hidden service, and whether it
	already may. Its content script (contentScripts/metagerPage.js in the
	metager-webextension repository) shows one of the two states and makes the
	buttons open the extension's own page, which asks for the permission.

	No JavaScript of ours is involved, on purpose: Tor Browser's "Safest" level
	turns page scripts off, and a content script runs regardless.
--}}
<div id="tor-extension" hidden>
	<div class="card">
		<div id="tor-extension-disabled" hidden>
			<p>@lang('tor.extension.disabled')</p>
			<button type="button" class="btn btn-primary" data-extension-onion-setup>@lang('tor.extension.enable')</button>
		</div>
		<div id="tor-extension-enabled" hidden>
			<p>@lang('tor.extension.enabled')</p>
			<button type="button" class="btn btn-default" data-extension-onion-setup>@lang('tor.extension.manage')</button>
		</div>
	</div>
</div>
@endsection