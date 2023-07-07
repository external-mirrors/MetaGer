@if ($type === 'startpage' || $type === 'subpage' || $type === 'resultpage')
<footer class="{{ $id }} noprint">
  <div id="language-footer">
    <a href="{{ route('lang-selector') }}">{{ LaravelLocalization::getCurrentLocaleNative() }}</a>
  </div>
  <div id="footer-links">
    <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "kontakt") }}" @if(!empty($metager) && $metager->isFramed())target="_top"@endif>{{ trans('sidebar.nav5') }}</a>
    <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "impressum") }}" @if(!empty($metager) && $metager->isFramed())target="_top"@endif>{{ trans('sidebar.nav8') }}</a>
    <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "datenschutz") }}" @if(!empty($metager) && $metager->isFramed())target="_top"@endif>{{ trans('sidebar.nav3') }}</a>
  </div>
</footer>
@endif
