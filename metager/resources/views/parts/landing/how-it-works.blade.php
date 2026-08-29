{{--
  The three steps, directly under the hero.

  They used to be the bottom of a page at /keys that most visitors never
  reached, and they are the only place the site answers the two questions a
  visitor has at the login button: why do I need a key, and what does it cost.
  So they come before the benefits, not after.

  #how-it-works is also the anchor /keys redirects to, which is why the id is
  the keymanager's rather than a new one — an external link to the old landing
  page lands on the section it was pointing at.

  Written out rather than looped: there are three, they are not
  interchangeable, and each takes different links.
--}}
<section id="how-it-works" class="landing-section landing-section--tinted">
  <div class="landing-container">
    <h2 class="landing-section__heading">@lang('index.landing.howitworks.heading')</h2>
    <ol class="landing-steps">
      <li>
        <h3>@lang('index.landing.howitworks.steps.0.heading')</h3>
        <p>@lang('index.landing.howitworks.steps.0.description')</p>
      </li>
      <li>
        <h3>@lang('index.landing.howitworks.steps.1.heading')</h3>
        <p>{!! __('index.landing.howitworks.steps.1.description', ['linkCost' => route('price')]) !!}</p>
      </li>
      <li>
        <h3>@lang('index.landing.howitworks.steps.2.heading')</h3>
        <p>@lang('index.landing.howitworks.steps.2.description')</p>
      </li>
    </ol>

    {{-- Members of SUMA-EV search without paying again. The keymanager had this
         string in de and en and never rendered it — the include that would have
         printed it was dropped at some point and nobody noticed, because the
         only way to notice was to read the JSON. --}}
    <p class="landing-steps__membership">
      {!! __('index.landing.howitworks.steps.1.membership', ['linkMembership' => route('membership_form')]) !!}
    </p>

    <div class="landing-steps__actions">
      <a href="{{ App\Landing\KeymanagerLinks::create() }}"
        class="btn landing-btn landing-btn--solid startpage-create-link">
        @lang('index.landing.howitworks.start')
      </a>
      <a href="{{ App\Landing\KeymanagerLinks::login(route('startpage')) }}" class="landing-steps__login">
        @lang('index.landing.howitworks.login')
      </a>
    </div>
  </div>
</section>
