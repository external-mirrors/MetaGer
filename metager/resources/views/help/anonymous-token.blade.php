@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title)

@section('content')
{{--
	Anonyme Token.

	Lag als /keys/help/anonymous-token im Keymanager. Die Weiterleitung von dort
	ist dauerhaft und darf nie abgeschaltet werden: der alte Pfad steht in
	bereits versandten Mitglieds-Willkommensmails
	(mail/membership/welcome.blade.php) und damit in fremden Postfächern.
--}}
<div id="anonymous-token">
	<h1 class="page-title">@lang('help/anonymous-token.heading')</h1>

	<section>
		<h2>@lang('help/anonymous-token.description.heading')</h2>
		<p>{!! trans('help/anonymous-token.description.text') !!}</p>
	</section>

	<section>
		<h2>@lang('help/anonymous-token.problem.heading')</h2>
		<p>{!! trans('help/anonymous-token.problem.text') !!}</p>
	</section>

	@foreach(["general-function", "meaning", "technical-function"] as $part)
		<section>
			<h2>@lang("help/anonymous-token.$part.heading")</h2>
			@foreach(trans("help/anonymous-token.$part.texts") as $text)
				<p>{!! $text !!}</p>
			@endforeach
		</section>
	@endforeach
</div>
@endsection
