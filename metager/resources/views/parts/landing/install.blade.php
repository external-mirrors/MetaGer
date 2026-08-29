{{--
  The close: the extension and the app. Unchanged copy from #story-plugin, in a
  band rather than a full screen, and in both states — a signed-in visitor is
  exactly who this offer is for.
--}}
<section id="landing-install" class="landing-section">
  <div class="landing-container">
    <div class="landing-install">
      <div class="landing-install__text">
        <h2>@lang('mg-story.plugin.title')</h2>
        <p>@lang('mg-story.plugin.p', ['anonymousTokenLink' => App\Landing\KeymanagerLinks::anonymousToken()])</p>
      </div>
      <figure class="landing-install__figure">
        <img src="/img/story-plugin.svg" alt="{{ trans('mg-story.plugin.image.alt') }}">
      </figure>
      <div class="landing-install__actions">
        <a class="btn landing-btn landing-btn--solid"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/plugin') }}">{{ trans('mg-story.plugin.btn-add') }}</a>
        <a class="btn landing-btn landing-btn--ghost"
          href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/app') }}">{{ trans('mg-story.plugin.btn-app') }}</a>
      </div>
    </div>
  </div>
</section>
