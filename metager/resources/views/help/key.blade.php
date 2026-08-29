@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title)

@section('content')
{{--
	Fragen zum MetaGer-Schlüssel.

	Lag als /keys/help/faq im Keymanager. Diese Seite ist ab jetzt die eine
	Stelle, an der der Schlüssel erklärt wird — /hilfe/funktionen#h-keyexplain
	hat denselben Stoff erklärt und verweist jetzt hierher.

	<details> statt eines Akkordeons aus JavaScript: die Seite muss ohne
	Client-JS funktionieren, und der Browser kann das von sich aus.
--}}
@php
	$vars = [
		"tokenlink" => LaravelLocalization::getLocalizedURL(null, "/hilfe/anonyme-token"),
		"keylink" => App\Landing\KeymanagerLinks::enter(),
		"voucherlink" => App\Landing\KeymanagerLinks::voucher(),
		"contactlink" => LaravelLocalization::getLocalizedURL(null, "/kontakt"),
	];
@endphp
<div id="key-faq">
	<h1 class="page-title">@lang('help/key.heading')</h1>

	<div id="faqs">
		@foreach(trans('help/key.faqs') as $index => $faq)
			<details class="faq">
				<summary>{{ $faq['summary'] }}</summary>
				<div class="faq-body">
					<p>{!! __("help/key.faqs.$index.description", $vars) !!}</p>
					@isset($faq['steps'])
						<ol class="faq-steps">
							@foreach($faq['steps'] as $step => $content)
								<li>
									<div class="faq-step-heading">{{ $content['heading'] }}</div>
									<div>{!! __("help/key.faqs.$index.steps.$step.description", $vars) !!}</div>
								</li>
							@endforeach
						</ol>
					@endisset
				</div>
			</details>
		@endforeach
	</div>

	<p class="faq-more">{!! __('help/key.more-questions', $vars) !!}</p>
</div>
@endsection
