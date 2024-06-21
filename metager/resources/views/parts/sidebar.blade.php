<input id="sidebarToggle" type="checkbox" aria-labelledby="sidebarToggle-label">
<div id="sidebarToggle-label">@lang('sidebar.toggle')</div>
<div class="sidebar">
  <a class="sidebar-logo" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
    <span>
      <img src="/img/metager.svg" alt="MetaGer">
    </span>
  </a>
  <ul class="sidebar-list" role="presentation">
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}"  id="navigationSuche" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/icon-lupe.svg" alt="" aria-hidden="true" id="sidebar-img-lupe">
        <span>{{ trans('sidebar.nav1') }}</span>
      </a>
    </li>
    <hr>
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/datenschutz/") }}" id="navigationPrivacy" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/lock.svg" alt="" aria-hidden="true" id="sidebar-img-lock"> 
        <span>{{ trans('sidebar.nav3') }}</span>
      </a>
    </li>
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/help-icon.svg" alt="" aria-hidden="true" id="sidebar-img-help"> 
        <span>{{ trans('sidebar.nav20') }}</span>
      </a>
    </li>
    <li>
      <details>
        <summary aria-label="@lang('sidebar.nav31')" id="navigationInfo">
          <img src="/img/svg-icons/icon-more-information.svg" alt="" aria-hidden="true" id="sidebar-img-info"> 
          <span>{{ trans('sidebar.nav31') }}</span><span class="caret" aria-hidden="true"></span>
        </summary>
        <ul role="presentation">
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/transparency/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav29') }}</a>
          </li>
          <li>
            <a href="https://gitlab.metager.de/open-source/MetaGer" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav24') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/search-engine/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav30') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/about/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('about.head.1') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/tips/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.titles.tips') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/faktencheck/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.navFactcheck') }}</a>
          </li>
        </ul>
      </details>
    </li>
    <hr>
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/spende/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/donate-icon.svg" alt="" aria-hidden="true" id="sidebar-img-donate"> 
        <span>{{ trans('sidebar.nav2') }}</span>
      </a>
    </li>
    @if (App\Localization::getLanguage() === "de")
    <li>
      <a href="{{ route('membership_form') }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/member-icon.svg" alt="" aria-hidden="true" id="sidebar-img-member"> 
        <span>{{ trans('sidebar.nav23') }}</span>
      </a>
    </li>
    @endif
    <hr>
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/app/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/app-icon.svg" alt="" aria-hidden="true" id="sidebar-img-app"> 
        <span>@lang('sidebar.nav25')</span>
      </a>
    </li>
    <li>
      <a  href="https://maps.metager.de?locale={{ App\Localization::getLanguage() }}" target="_blank" >
      <img src="/img/svg-icons/icon-map.svg" alt="" aria-hidden="true" id="sidebar-img-map"> 
        <span>{{ trans('sidebar.nav27') }}</span> 
      </a>
    </li>
    <hr>
    <li>
      <details>
        <summary aria-label="@lang('sidebar.nav18')" id="navigationKontakt">
          <img src="/img/svg-icons/icon-contact.svg" alt="" aria-hidden="true" id="sidebar-img-contact"> 
          <span>{{ trans('sidebar.nav18') }}</span><span class="caret" aria-hidden="true"></span>
        </summary>
        <ul role="presentation">
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/kontakt/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav5') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/team/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav6') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/impressum/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav8') }}</a>
          </li>
        </ul>
      </details>
    </li>
    <li>
      <details>
        <summary aria-label="@lang('sidebar.nav15')" id="navigationServices">
          <img src="/img/svg-icons/icon-services.svg" alt="" aria-hidden="true" id="sidebar-img-services"> 
          <span>{{ trans('sidebar.nav15') }}</span><span class="caret" aria-hidden="true"></span>
        </summary>
        <ul role="presentation">
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.plugin') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/widget/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav10') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/zitat-suche/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav22') }}</a>
          </li>
          <li>
            <a href="{{ route("asso") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav11') }}</a>
          </li>
          <li>
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/tor/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav14') }}</a>
          </li>
          @if(App\Localization::getLanguage() == "de")
            <li>
              <a href="https://shop.spreadshirt.de/metager/" rel="noopener" target="_blank">{{ trans('sidebar.nav26') }}<img src="/img/svg-icons/icon-outlink.svg" alt="" aria-hidden="true" id="sidebar-img-outlink"></a> 
            </li>
          @endif
          <li>
            <a href="https://www.wecanhelp.de/430159004" target="_blank">{{ trans('sidebar.nav17') }} <img src="/img/svg-icons/icon-outlink.svg" alt="" aria-hidden="true" id="sidebar-img-outlink"></a>
          
          </li>
        </ul>
      </details>
    </li>
    <li>
      <details>
        <summary aria-label="@lang('sidebar.nav28')" id="navigationEinstellung">
          <img src="/img/icon-settings.svg" alt="" aria-hidden="true" id="sidebar-img-settings"> 
          <span>{{ trans('sidebar.nav28') }}</span><span class="caret" aria-hidden="true"></span>
        </summary>
        <ul role="presentation">
          @foreach(app()->make(\App\Searchengines::class)->available_foki as $fokus)
            <li>
              <a href="{{ route("settings", ["focus" => $fokus, "url" => url()->full()]) }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans("index.foki.$fokus") }}</a>
            </li>
          @endforeach
        </ul>
      </details>
    </li>
    <hr>
    <li>
      <a href="{{ route('lang-selector') }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/icon-language.svg" alt="" aria-hidden="true" id="sidebar-img-language"> 
        <span>{{ LaravelLocalization::getSupportedLocales()[LaravelLocalization::getCurrentLocale()]['native'] }}</span> 
      </a>
    </li>
  </ul>
  <a id="skip-to-nav-toggle" href="#sidebarToggle">@lang('sidebar.close')</a>
</div>
