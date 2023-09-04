@extends('layouts.subPages', ['page' => 'privacy'])

@section('title', trans('titles.datenschutz') )

@section('navbarFocus.datenschutz', 'class="active"')

@section('content')
<div>
    <h1 class="page-title">@lang('privacy.title')</h1>
    <div class="section">
        @lang('privacy.introduction')
    </div class="section">
    <div class="section">
        <h1>@lang('privacy.responsible_party.title')</h1>
        <div>@lang('privacy.responsible_party.description', ['link_impress' => route('impress'), 'link_contact' => route('contact')])</div>
    </div>
    <div class="section">
        <h1>@lang('privacy.principles.title')</h1>
        <div>@lang('privacy.principles.description')</div>
    </div>
    <div class="section">
        <h1>@lang('privacy.contexts.title')</h1>
        <ol>
            <li class="kontext-list">
                <article class="kontext">
                    <h1>@lang('privacy.contexts.metager.title')</h1>
                    <div>@lang('privacy.contexts.metager.description')</div>
                    <ol class="datum-list">
                        <li><a href="#ip-address">@lang('privacy.data.ip'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#search-request">@lang('privacy.data.query'):</a>  @lang('privacy.contexts.metager.query')</li>
                        <li><a href="#preferences">@lang('privacy.data.preferences'):</a> @lang('privacy.contexts.metager.preferences')</li>
                    </ol>
                    <h3>@lang('privacy.contexts.metager.additionally')</h3>
                    <ol class="datum-list">
                        <li>
                            <div><a href="#ip-address">@lang('privacy.data.ip')</a>, <a href="#user-agent">@lang('privacy.data.useragent')</a>:</div>
                            <div>@lang('privacy.contexts.metager.botprotection')</div>
                        </li>
                        <li>
                            <div><a href="https://privacy.microsoft.com/privacystatement">Microsoft Clarity & Yahoo</a></div>
                            <div>@lang('privacy.contexts.metager.clarity')</div></li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1 id="contact">@lang('privacy.contexts.contact.title')</h1>
                    <div>@lang('privacy.contexts.contact.description')</div>
                    <ol class="datum-list">
                        <li><a href="#ip-address">@lang('privacy.data.ip'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#contact-data">@lang('privacy.data.contact'):</a> @lang('privacy.contexts.contact.contact')</li>
                        <li><a href="#message">@lang('privacy.data.message'):</a> @lang('privacy.contexts.contact.contact')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1 id="donation">@lang('privacy.contexts.donate.title')</h1>
                    <div>@lang('privacy.contexts.donate.description')</div>
                    <ol class="datum-list">
                        <li><a href="#ip-address">@lang('privacy.data.ip'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#contact-data">@lang('privacy.data.contact'):</a> @lang('privacy.contexts.donate.contact')</li>
                        <li><a href="#payment-data">@lang('privacy.data.payment'):</a> @lang('privacy.contexts.donate.payment')</li>
                        <li><a href="#message">@lang('privacy.data.message') (@lang('privacy.data.optional')):</a> @lang('privacy.contexts.donate.message')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1 id="donation">@lang('privacy.contexts.key.title')</h1>
                    <ol class="datum-list">
                        <li><a href="#ip-address">@lang('privacy.data.ip'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent'):</a> @lang('privacy.data.unused')</li>
                        <li><a href="#contact-data">@lang('privacy.data.contact') (@lang('privacy.data.optional')):</a> @lang('privacy.contexts.key.contact')</li>
                        <li><a href="#payment-data">@lang('privacy.data.payment'):</a> @lang('privacy.contexts.key.payment')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.suma.title')</h1>
                    <div>@lang('privacy.contexts.suma.description')</div>
                    <ul>
                        <li><a href="#ip-address">@lang('privacy.data.ip')</a></li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent')</a></li>
                        <li>@lang('privacy.data.referrer')</li>
                    </ul>
                    <div>@lang('privacy.contexts.suma.function')</div>
                    <div>@lang('privacy.contexts.suma.other')</div>
                    <div>@lang('privacy.contexts.suma.startpage')</div>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.newsletter.title')</h1>
                    <div>@lang('privacy.contexts.newsletter.description')</div>
                    <ol class="datum-list">
                        <li><a href="#contact-data">@lang('privacy.data.contact'):</a> @lang('privacy.contexts.newsletter.contact')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.maps.title')</h1>
                    <div>@lang('privacy.contexts.maps.description')</div>
                    <ol>
                        <li><a href="#ip-address">@lang('privacy.data.ip')</a>: @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent')</a>: @lang('privacy.data.unused')</li>
                        <li><a href="#search-request">@lang('privacy.data.query')</a>: @lang('privacy.data.unused')</li>
                        <li>@lang('privacy.data.gps'): @lang('privacy.data.unused')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.proxy.title')</h1>
                    <div>@lang('privacy.contexts.proxy.description')</div>
                    <ol>
                        <li><a href="#ip-address">@lang('privacy.data.ip')</a>: @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent')</a>: @lang('privacy.data.unused')</li>
                    </ol>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.quote.title')</h1>
                    <div>@lang('privacy.contexts.quote.description')</div>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.asso.title')</h1>
                    <div>@lang('privacy.contexts.asso.description')</div>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.mapsapp.title')</h1>
                    <div>@lang('privacy.contexts.mapsapp.description')</div>
                </article>
                <article class="kontext">
                    <h1>@lang('privacy.contexts.plugin.title')</h1>
                    <div>@lang('privacy.contexts.plugin.description')</div>
                    <ol>
                        <li><a href="#ip-address">@lang('privacy.data.ip')</a>: @lang('privacy.data.unused')</li>
                        <li><a href="#user-agent">@lang('privacy.data.useragent')</a>: @lang('privacy.data.unused')</li>
                    </ol>
                </article>
            </li>
        </ol>
    </div>
    <div class="section">
        <h1>@lang('privacy.hosting.title')</h1>
        <div>@lang('privacy.hosting.description')</div>
    </div>
    <div class="section">
        <h1>@lang('privacy.description.title')</h1>
        <ol class="datum-list-general">
            <li>
                <article id="ip-address" class="datum">
                    <h2>@lang('privacy.description.ip.title')</h2>
                    <div>@lang('privacy.description.ip.description')</div>
                    <h3>@lang('privacy.description.ip.example_full')</h3>
                    <samp>154.67.88.47</samp><br />
                    <samp>82.159.53.49</samp>
                    <h3>@lang('privacy.description.ip.example_partial')</h3>
                    <samp>154.67.0.0</samp><br />
                    <samp>82.159.0.0</samp>
                </article>
            </li>
            <li>
                <article class="datum">
                    <h2 id="user-agent">@lang('privacy.description.useragent.title')</h2>
                    <div>@lang('privacy.description.useragent.description')</div>
                    <h3>@lang('privacy.description.useragent.example')</h3>
                    <samp>Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) Gecko/20100101 Firefox/59.0</samp>
                </article>
            </li>
            <li>
                <article id="payment-data" class="datum">
                    <h2>@lang('privacy.description.payment.title')</h2>
                    <div>@lang('privacy.description.payment.description')</div>
                    <h3>@lang('privacy.description.payment.examples')</h3>
                    <samp>@lang('privacy.description.payment.name')</samp><br />
                    <samp>IBAN: DE1234567890123</samp><br />
                    <samp>@lang('privacy.description.payment.card'): XXXXXXXXXXX1234</samp><br />
                </article>
            </li>
            <li>
                <article class="datum">
                    <h2 id="search-request">@lang('privacy.description.query.title')</h2>
                    <div>@lang('privacy.description.query.description')</div>
                    <h3>@lang('privacy.description.query.examples')</h3>
                    <samp>@lang('privacy.description.query.example_1')</samp><br />
                    <samp>@lang('privacy.description.query.example_2')</samp><br />
                </article>
            </li>
            <li>
                <article class="datum">
                    <h2 id="preferences">@lang('privacy.description.preferences.title')</h2>
                    <div>@lang('privacy.description.preferences.description')</div>
                    <h3>@lang('privacy.description.query.examples')</h3>
                    <samp>interface=de sprachfilter=all fokus=web</samp><br />
                    <samp>interface=de sprachfilter=de fokus=nachrichten</samp><br />
                    <samp>interface=en sprachfilter=en fokus=web</samp>
                </article>
            </li>
            <li>
                <article class="datum">
                    <h2 id="contact-data">@lang('privacy.description.contact.title')</h2>
                    <div>@lang('privacy.description.contact.description')</div>
                    <h3>@lang('privacy.description.query.examples')</h3>
                    <samp>@lang('privacy.description.payment.name')</samp><br />
                </article>
            </li>
            <li>
                <article class="datum">
                    <h2 id="message">@lang('privacy.description.message.title')</h2>
                    <div>@lang('privacy.description.message.description')</div>
                    <h3>@lang('privacy.description.query.examples')</h3>
                    <samp>Feedback MetaGer</samp><br />
                    <samp>MetaGer Browser-PlugIn</samp><br />
                </article>
            </li>
        </ol>
    </div>
    <div class="section">
        <h1>@lang('privacy.base.title')</h1>
        <div>@lang('privacy.base.description')</div>
    </div>
    <div class="section">
        <h1 id="ihrerechte">@lang('privacy.rights.title')</h1>
        <div>@lang('privacy.rights.description')</div>
        <ol>
            <li><b>@lang('privacy.rights.information.title'):</b></li>
            <article class="kontext">
            @lang('privacy.rights.information.description')
            </article>
            <li><b>@lang('privacy.rights.correction.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.correction.description')
            </article>
            <li><b>@lang('privacy.rights.deletion.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.deletion.description')
            </article>
            <li><b>@lang('privacy.rights.processing.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.processing.description')
            </article>
            <li><b>@lang('privacy.rights.complaint.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.complaint.description')
            </article>
            <li><b>@lang('privacy.rights.opposition.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.opposition.description')
            </article>
            <li><b>@lang('privacy.rights.portability.title'):</b></li>
            <article class="kontext">
                @lang('privacy.rights.portability.description')
            </article>
            <li><b>@lang('privacy.rights.obligation_notify.title')</b></li>
            <article class="kontext">
                @lang('privacy.rights.obligation_notify.description')
            </article>
        </ol>
        <div>@lang('privacy.rights.perception', ['contact_link' => route('contact')])</div>
        <br />SUMA-EV
        <br />Röselerstraße 3
        <br />30159 Hannover
    </div>
    <div class="section">
        <h1>@lang('privacy.changes.title')</h1>
        <div>@lang('privacy.changes.description')</div>
        <div>@lang('privacy.changes.date', ['date' => '2023-09-04'])</div>
    </div>
@endsection