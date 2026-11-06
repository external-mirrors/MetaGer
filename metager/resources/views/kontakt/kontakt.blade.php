@extends('layouts.subPages')

@section('title', $title)

@section('content')
{{--
	/kontakt — first what does not go through us here, then email, phone and
	post, then the pages that answer common questions.

	Removal requests are only taken through suma-crm's form, not by any way
	listed on this page, so that row comes before the address people would
	write to. The guide stays linked: MetaGer shows other engines' results, and
	the form is for SUMA-EV's own indexes.
	One row per way to reach us, so a fold opening in one never stretches
	another.
	No script on this page.
--}}
<div id="kontakt">
	<h1 class="page-title">{!! trans('kontakt.headline.1') !!}</h1>
	<p class="contact-lead">@lang('kontakt.lead')</p>

	{{-- An address card: each way to reach us is one row, its h2 a short
	     label beside the content (pages/contact.less), stacked on a phone. --}}
	<section class="contact-section contact-removal" id="removal" aria-labelledby="contact-removal-heading">
		<h2 id="contact-removal-heading">@lang('kontakt.removal.heading')</h2>
		<div class="contact-section-body">
			<p class="contact-removal-only">@lang('kontakt.removal.only')</p>
			<a class="contact-button" href="{{ $removal_form }}">@lang('kontakt.removal.form')</a>
			<p class="contact-removal-guide">@lang('kontakt.removal.guide', ['link' => route('removal')])</p>
		</div>
	</section>

	<section class="contact-section contact-email" id="email" aria-labelledby="contact-email-heading">
		<h2 id="contact-email-heading">@lang('kontakt.headline.2')</h2>
		<div class="contact-section-body">
			<div class="contact-email-primary">
				<p class="contact-address">{{ $to_mail }}</p>
				<a class="contact-button" href="mailto:{{ $to_mail }}">@lang('kontakt.email.write')</a>
			</div>
			<details class="contact-pgp">
				<summary>@lang('kontakt.email.pgp.summary')</summary>
				{{-- How suma-crm decides to answer encrypted: App\Pgp\KeyDiscovery
				     there. ContactPageTest pins this text to those rules. --}}
				<p>@lang('kontakt.email.pgp.signed')</p>
				<p>@lang('kontakt.email.pgp.pubkey', ['keyserver' => 'https://keys.openpgp.org/search?q=' . urlencode($to_mail)])</p>
				<p class="contact-fingerprint">@lang('kontakt.email.pgp.fingerprint')</p>
				<h3>@lang('kontakt.email.pgp.replies.heading')</h3>
				<p>@lang('kontakt.email.pgp.replies.how')</p>
				<ul>
					@foreach(trans('kontakt.email.pgp.replies.trusted_when') as $rule)
						<li>{!! $rule !!}</li>
					@endforeach
				</ul>
				<p>@lang('kontakt.email.pgp.replies.otherwise')</p>
				<p>@lang('kontakt.email.pgp.replies.tofu')</p>
				<p>@lang('kontakt.email.pgp.replies.never_plain')</p>
			</details>
		</div>
	</section>

	<section class="contact-section contact-phone" id="phone" aria-labelledby="contact-phone-heading">
		<h2 id="contact-phone-heading">@lang('kontakt.phone.heading')</h2>
		<div class="contact-section-body">
			<p class="contact-phone-number"><a href="{{ $phone_uri }}">{{ $phone }}</a></p>
			{{-- The times are <time> elements built by ContactController. --}}
			<p class="contact-phone-hours">@lang('kontakt.phone.hours', $office_hours)</p>
			<p class="contact-phone-outside">@lang('kontakt.phone.outside')</p>
		</div>
	</section>

	<section class="contact-section contact-post" id="post" aria-labelledby="contact-post-heading">
		<h2 id="contact-post-heading">{!! trans('kontakt.letter.1') !!}</h2>
		<div class="contact-section-body">
			<address>{!! trans('kontakt.letter.3') !!}</address>
		</div>
	</section>

	<section class="contact-section contact-topics" aria-labelledby="contact-topics-heading">
		<h2 id="contact-topics-heading">@lang('kontakt.topics.heading')</h2>
		<ul>
			@foreach($topics as $topic => $url)
				<li>
					<a href="{{ $url }}"><span>@lang("kontakt.topics.$topic.title")<span>@lang("kontakt.topics.$topic.text")</span></span></a>
				</li>
			@endforeach
		</ul>
	</section>
</div>
@endsection
