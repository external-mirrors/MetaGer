{{--
  The five benefit cards, carried over from the keymanager's landing page.

  The story section "Garantierte Privatsphäre" used to say the same thing one
  screen further down, in four vague lines; three of these cards say it
  concretely, so that section is gone and its two buttons — about, and the
  privacy policy — hang off "no compromises", which is the card they belong to.

  The icons are the keymanager's, moved to public/img/landing. They are flat
  #808080 line art and need a filter that flips per theme; @landing-icon-filter
  in variables.less is that. The lock is the exception: it is brand orange on
  purpose and landing.less turns the filter off for it.
--}}
<section id="landing-benefits" class="landing-section">
  <div class="landing-container">
    <ul class="landing-benefits">

      <li class="landing-benefit" id="benefit-browsing">
        <div class="landing-benefit__badge">
          <img src="/img/landing/incognito.svg" alt="">
        </div>
        <div class="landing-benefit__text">
          <h2>@lang('index.landing.benefits.browsing.heading')</h2>
          <p>@lang('index.landing.benefits.browsing.description')</p>
          <ul class="landing-benefit__nots">
            <li>@lang('index.landing.benefits.browsing.fingerprinting')</li>
            <li>@lang('index.landing.benefits.browsing.tracking')</li>
          </ul>
        </div>
      </li>

      <li class="landing-benefit" id="benefit-ads">
        <div class="landing-benefit__badge">
          <img src="/img/landing/no-ads.svg" alt="">
        </div>
        <div class="landing-benefit__text">
          <h2>@lang('index.landing.benefits.ads.heading')</h2>
          <p>@lang('index.landing.benefits.ads.description')</p>
          <ul class="landing-benefit__nots">
            <li>@lang('index.landing.benefits.ads.ads')</li>
            <li>@lang('index.landing.benefits.ads.tracking')</li>
          </ul>
        </div>
      </li>

      <li class="landing-benefit" id="benefit-logging">
        <div class="landing-benefit__badge">
          <img src="/img/landing/no-logging.svg" alt="">
        </div>
        <div class="landing-benefit__text">
          <h2>@lang('index.landing.benefits.logging.heading')</h2>
          <p>@lang('index.landing.benefits.logging.description')</p>
          <ul class="landing-benefit__nots">
            <li>@lang('index.landing.benefits.logging.logging')</li>
          </ul>
        </div>
      </li>

      <li class="landing-benefit" id="benefit-compromise">
        <div class="landing-benefit__badge landing-benefit__badge--brand">
          <img src="/img/landing/metager-schloss-orange.svg" alt="">
        </div>
        <div class="landing-benefit__text">
          <h2>@lang('index.landing.benefits.compromise.heading')</h2>
          <p>{!! __('index.landing.benefits.compromise.description', [
            'linkPaymentMethods' => App\Landing\KeymanagerLinks::paymentMethods(),
            'linkApp' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/app'),
            'linkToken' => App\Landing\KeymanagerLinks::anonymousToken(),
          ]) !!}</p>
          <ul class="landing-benefit__nots">
            <li>@lang('index.landing.benefits.compromise.compromise')</li>
          </ul>
          <ul class="landing-links">
            <li><a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), 'about') }}">{{ trans('about.head.1') }}</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), 'datenschutz') }}">{{ trans('mg-story.btn-data-protection') }}</a></li>
          </ul>
        </div>
      </li>

      <li class="landing-benefit" id="benefit-efficiency">
        <div class="landing-benefit__badge">
          <img src="/img/landing/efficiency.svg" alt="">
        </div>
        <div class="landing-benefit__text">
          <h2>@lang('index.landing.benefits.efficiency.heading')</h2>
          <p>@lang('index.landing.benefits.efficiency.description')</p>
        </div>
      </li>

    </ul>
  </div>
</section>
