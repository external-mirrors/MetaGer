{{--
  Who runs MetaGer — three cards where there used to be three screens.

  Same copy and same buttons as the old #story-ngo, #story-diversity and
  #story-eco sections; what is gone is that each one filled a viewport and had
  its own tinted background, so the association that funds the whole thing sat
  five screens down. #story-privacy is the fourth that used to be here and is
  now told properly in the benefit cards above.

  This section renders in both states. A signed-in visitor gets a search engine
  and then this, with none of the marketing in between: they have already
  bought it.
--}}
<section id="landing-org" class="landing-section landing-section--tinted">
  <div class="landing-container">
    <h2 class="landing-section__heading">@lang('mg-story.four-reasons')</h2>
    <ul class="landing-org">

      <li class="landing-org__card" id="org-ngo">
        <img class="landing-org__icon" src="/img/svg-icons/heart.svg" alt="{{ trans('mg-story.ngo.image.alt') }}">
        <h3>@lang('mg-story.ngo.title')</h3>
        <p>{!! trans('mg-story.ngo.p') !!}</p>
        <ul class="landing-links">
          <li><a href="https://suma-ev.de/" target="_blank">{{ trans('mg-story.btn-SUMA-EV') }}</a></li>
          <li><a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), 'spende') }}">{{ trans('mg-story.btn-donate') }}</a></li>
          <li><a href="{{ route('membership_form') }}" target="_blank">{{ trans('mg-story.btn-member') }}</a></li>
          <li><a href="https://suma-ev.de/mitglieder/" target="_blank">{{ trans('mg-story.btn-member-advantage') }}</a></li>
        </ul>
      </li>

      <li class="landing-org__card" id="org-diversity">
        <img class="landing-org__icon" src="/img/svg-icons/rainbow.svg" alt="{{ trans('mg-story.diversity.image.alt') }}">
        <h3>@lang('mg-story.diversity.title')</h3>
        <p>{!! trans('mg-story.diversity.p') !!}</p>
        <ul class="landing-links">
          <li><a href="https://gitlab.metager.de/open-source/MetaGer" target="_blank">{{ trans('mg-story.btn-mg-code') }}</a></li>
          <li><a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), 'transparency') }}">{{ trans('mg-story.btn-mg-algorithm') }}</a></li>
        </ul>
      </li>

      <li class="landing-org__card" id="org-eco">
        <img class="landing-org__icon" src="/img/svg-icons/leaf.svg" alt="{{ trans('mg-story.eco.image.alt') }}">
        <h3>@lang('mg-story.eco.title')</h3>
        <p>{!! trans('mg-story.eco.p') !!}</p>
        <ul class="landing-links">
          <li><a href="https://www.hetzner.de/unternehmen/umweltschutz/" target="_blank">{{ trans('mg-story.btn-more') }}</a></li>
        </ul>
      </li>

    </ul>
  </div>
</section>
