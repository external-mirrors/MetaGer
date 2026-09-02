@extends('layouts.subPages', ['page' => 'c'])

@section('title', $title)

@section('content')
{{--
	Einen Gutschein einlösen — es ging nicht.

	Lag als /keys/c im Keymanager (views/campaign/error.ejs). Acht Gründe, eine
	Meldung, und darunter ein Weg zurück — aber nur, wenn es einen gibt.

	Ob es einen gibt, entscheidet der Controller und nicht diese Seite. Der
	Keymanager fragte hier selbst „ist der Grund rate_limited?“, und damit
	standen zwei Stellen im Code, die dasselbe wissen mussten. Ein Gutschein,
	der schon eingelöst ist, wird durch einen zweiten Versuch nicht wieder
	frei; ein Knopf, der das anbietet, ist eine Einladung in dieselbe Wand.
--}}
<div id="voucher-page" class="voucher-page--error">
	<h1 class="page-title">@lang('campaigns.redeem.error.heading')</h1>

	<p class="voucher-error" role="alert">@lang("campaigns.redeem.error.$error")</p>

	@if($retryUrl !== null)
		<a class="voucher-retry" href="{{ $retryUrl }}">@lang('campaigns.redeem.error.retry')</a>
	@endif
</div>
@endsection
