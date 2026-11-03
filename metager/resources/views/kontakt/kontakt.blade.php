@extends('layouts.subPages')

@section('title', $title)

@section('content')
{{--
	/kontakt — routes people before it gives them an address.

	The mail we can help least with is "please remove this search result", so
	that comes first and goes to the removal guide; then the other frequent
	questions that already have a page; only then email and post.
	No script on this page.
--}}
<div id="kontakt">
	<h1 class="page-title">{!! trans('kontakt.headline.1') !!}</h1>
	<p class="contact-intro">@lang('kontakt.intro')</p>

	<section class="contact-removal" aria-labelledby="contact-removal-heading">
		<h2 id="contact-removal-heading">@lang('kontakt.removal.heading')</h2>
		<p>@lang('kontakt.removal.text')</p>
		<a class="contact-removal-link" href="{{ route('removal') }}">@lang('kontakt.removal.link')</a>
	</section>

	<section class="contact-topics" aria-labelledby="contact-topics-heading">
		<h2 id="contact-topics-heading">@lang('kontakt.topics.heading')</h2>
		<ul>
			@foreach($topics as $topic => $url)
				<li>
					<a href="{{ $url }}">@lang("kontakt.topics.$topic.title")</a>
					<span>@lang("kontakt.topics.$topic.text")</span>
				</li>
			@endforeach
		</ul>
	</section>

	<h2 class="contact-reach">@lang('kontakt.reach')</h2>
	<div class="contact-ways">
		<section class="contact-way" id="email" aria-labelledby="contact-email-heading">
			<h3 id="contact-email-heading">@lang('kontakt.headline.2')</h3>
			<p>@lang('kontakt.email.text', ['mail' => $to_mail])</p>
			<h4>@lang('kontakt.headline.pgp')</h4>
			<p>@lang('kontakt.email.pgp.description')</p>
			<p>@lang('kontakt.email.pgp.pubkey', ['keyserver' => 'https://keys.openpgp.org/search?q=' . urlencode($to_mail)])</p>
			<p class="contact-fingerprint">@lang('kontakt.email.pgp.fingerprint')</p>
		</section>
		<section class="contact-way" id="post" aria-labelledby="contact-post-heading">
			<h3 id="contact-post-heading">{!! trans('kontakt.letter.1') !!}</h3>
			<p>{!! trans('kontakt.letter.2') !!}</p>
			<address>{!! trans('kontakt.letter.3') !!}</address>
		</section>
	</div>
</div>
@endsection
