<input id="sidebarToggle" type="checkbox" aria-labelledby="sidebarToggle-label">
<div id="sidebarToggle-label">@lang('sidebar.toggle')</div>
{{-- The ✕ belongs to the open sidebar, not to the menu button that opened it.

     It used to ship inside parts/sidebar-opener.blade.php, next to the ≡, and
     the swap between them was `#sidebarToggle:checked ~ .sidebar-opener` — which
     worked only while both labels were siblings of the checkbox. Once the ≡
     moved into .navigation-cluster the combinator stopped at the cluster, so the
     ✕ was never revealed, and the cluster hides itself when the sidebar covers
     that corner: an open menu with nothing in the page that could close it.

     Here it is a sibling of the checkbox again, on every page that has a
     sidebar, at every width — including below 920px, where the result page's
     cluster is display:none and could not have carried it. --}}
<label aria-label="@lang('sidebar.opener_close')" class="sidebar-opener close navigation-element" for="sidebarToggle">×</label>
<div class="sidebar">
  <a class="sidebar-logo" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
    <span>
      <img src="/img/metager.svg" alt="MetaGer">
    </span>
  </a>
  {{-- The account is not a navigation entry.

       It used to be one: an <li> styled exactly like "Suchen" and "Spenden",
       with the balance and a bare "Abmelden" link hanging underneath it, aligned
       to the panel edge rather than to the label they belonged to. Signed out it
       carried the longest label in the menu — "Werbefreie Suche einrichten" — in
       the row with the least room for it, and clipped.

       So it is a block now, between the logo and the list, ruled off from both.
       It is the same block on the startpage and the result page, because the
       sidebar is shared, and the result page is where people actually spend
       their time. --}}
  @php($sidebarKeyUser = \Auth::guard("key")->user())
  <div class="sidebar-account">
    @if($sidebarKeyUser !== null)
      @php($sidebarFingerprint = $sidebarKeyUser->getKeyFingerprint())
      @php($sidebarCharge = $sidebarKeyUser->getCharge())
      @php($sidebarState = $sidebarKeyUser->getKeyState())
      @if($sidebarFingerprint === null && $sidebarCharge === null)
        {{-- The webextension. We hold no key and no balance, and saying so is the
             whole point of the arrangement — see KeyUser::getKeyFingerprint(). --}}
        <div class="sidebar-account__identity">
          {!! \App\Authentication\KeyIdenticon::render(null) !!}
          <div class="sidebar-account__names">
            <span class="sidebar-account__code sidebar-account__code--wordy">@lang('account.pill.anonymous')</span>
            <span class="sidebar-account__balance">@lang('account.sidebar.anonymous_hint')</span>
          </div>
        </div>
        {{-- Hidden until the extension reveals it, because only the extension can
             make it do anything: its content script (contentScripts/metagerPage.js)
             unhides this and opens its own settings from the click. Rendered on
             the server rather than by our own JS so that it is one element with
             one owner — the same arrangement as #plugin-btn, which the extension
             removes, and which is how our scripts detect it at all.

             A link would need a URL, and there is no URL for "the extension's
             settings" that a page is allowed to navigate to. --}}
        <div class="sidebar-account__actions">
          <button type="button" class="btn account-btn account-btn--primary" id="account-extension-settings" hidden>@lang('account.sidebar.extension_settings')</button>
        </div>
      @else
        <div class="sidebar-account__identity">
          {!! \App\Authentication\KeyIdenticon::render($sidebarFingerprint) !!}
          <div class="sidebar-account__names">
            @if($sidebarFingerprint !== null)
              <span class="sidebar-account__code">{{ strtoupper($sidebarFingerprint) }}</span>
            @else
              <span class="sidebar-account__code sidebar-account__code--wordy">@lang('account.pill.signed_in')</span>
            @endif
            @if($sidebarCharge !== null)
              <span class="sidebar-account__balance @if($sidebarState === \App\Authentication\KeyState::LOW) is-low @elseif($sidebarState === \App\Authentication\KeyState::EMPTY) is-empty @endif">
                @if($sidebarState === \App\Authentication\KeyState::EMPTY)
                  @lang('account.sidebar.balance_empty')
                @else
                  {{ __('account.sidebar.balance', ['charge' => (int) floor($sidebarCharge)]) }}
                @endif
              </span>
            @endif
          </div>
        </div>
        <div class="sidebar-account__actions">
          <a class="btn account-btn account-btn--primary" href="{{ App\Landing\KeymanagerLinks::dashboard() }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
            @if($sidebarState === \App\Authentication\KeyState::FULL)
              @lang('account.sidebar.manage')
            @else
              @lang('account.sidebar.topup')
            @endif
          </a>
          {{-- Keeps its id: resources/js/accountBreadcrumb.js clears the
               returning-user flag on it, and the webextension's own
               contentScripts/keys.js needs a stable hook to catch a logout and
               drop the master key from extension storage.

               The return URL goes through KeymanagerLinks::remove() rather
               than App\Localization::currentFullUrl() directly, because a
               visitor who just entered their key is standing on `?key=<uuid>`
               and handing that straight back logs them in again. --}}
          <a class="btn account-btn account-btn--quiet" id="sidebar-key-remove" href="{{ App\Landing\KeymanagerLinks::remove() }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>@lang('account.sidebar.logout')</a>
        </div>
      @endif
    @else
      <div class="sidebar-account__lede">@lang('account.sidebar.logged_out')</div>
      {{-- Both go through App\Landing\KeymanagerLinks, which re-emits the
           MetaGer app's callback markers. The app opens the landing page in a
           Custom Tab, and the landing page is now this page — which has a menu
           the keymanager's own page did not. A visitor who signs in from here
           rather than from the button in the hero must not lose the handback,
           or the key they create never reaches the app.

           "Create" used to point at /keys. That was the landing page; /keys now
           redirects here, so the old target sent people back where they came
           from. --}}
      <div class="sidebar-account__actions">
        <a class="btn account-btn account-btn--primary" href="{{ App\Landing\KeymanagerLinks::login() }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>@lang('account.sidebar.login')</a>
        <a class="btn account-btn account-btn--quiet" href="{{ App\Landing\KeymanagerLinks::create() }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>@lang('account.sidebar.create')</a>
      </div>
    @endif
  </div>
  <ul class="sidebar-list" role="presentation">
    <li>
      <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}"  id="navigationSuche" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/icon-lupe.svg" alt="" aria-hidden="true" id="sidebar-img-lupe">
        <span>{{ trans('sidebar.nav1') }}</span>
      </a>
    </li>
    <hr>
    {{-- Die eine Frage, die ein abgemeldeter Besucher hat und die die
         Startseite nicht beantwortet. Erste Ebene, kein Untermenü: in der
         Navigation des Keymanagers stand „Preis“ ebenfalls ganz oben, und die
         Seite ist mit /keys/cost von dort hierher gezogen. --}}
    <li>
      <a href="{{ route('price') }}" id="navigationPrice" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/price-icon.svg" alt="" aria-hidden="true" id="sidebar-img-price">
        <span>{{ trans('sidebar.navPrice') }}</span>
      </a>
    </li>
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
    @if (App\Support\MembershipOffer::isAdvertised())
    <li>
      <a href="{{ route('membership_form') }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/member-icon.svg" alt="" aria-hidden="true" id="sidebar-img-member"> 
        <span>{{ trans('sidebar.nav23') }}</span>
      </a>
    </li>
    {{-- Der Weg für Firmen steht neben dem für Menschen und nicht in einem
         Untermenü: wer hier nach einer Mitgliedschaft sucht und für eine
         Organisation fragt, soll nicht erst im Beitrittsformular erfahren, dass
         es diesen Zweig gibt. Unter derselben Sprachbedingung, weil dahinter
         dasselbe deutsche Formular liegt. --}}
    <li>
      <a href="{{ route('business') }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/svg-icons/member-icon.svg" alt="" aria-hidden="true" id="sidebar-img-business"> 
        <span>{{ trans('business.hints.sidebar') }}</span>
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
          {{-- Rechtliches steht beisammen: die AGB für die Token-Aufladung
               lagen als /keys/agb im Keymanager und waren aus MetaGers
               Navigation gar nicht erreichbar. --}}
          <li>
            <a href="{{ route('agb') }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.navAgb') }}</a>
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
            <a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/tor/") }}" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>{{ trans('sidebar.nav14') }}</a>
          </li>
          @if(App\Localization::getLanguage() == "de")
            <li>
              <a href="https://shop.spreadshirt.de/suma-ev/" rel="noopener" target="_blank">{{ trans('sidebar.nav26') }}<img src="/img/svg-icons/icon-outlink.svg" alt="" aria-hidden="true" id="sidebar-img-outlink"></a> 
            </li>
          @endif
          <li>
            <a href="https://www.wecanhelp.de/430631004" target="_blank">{{ trans('sidebar.nav17') }} <img src="/img/svg-icons/icon-outlink.svg" alt="" aria-hidden="true" id="sidebar-img-outlink"></a>
          
          </li>
        </ul>
      </details>
    </li>
    <li>
      <a href="{{ route('settings', ['focus' => 'web', 'url' => App\Localization::currentFullUrl()]) }}" id="navigationEinstellung" @if(Request::header("Sec-Fetch-Dest") === "iframe")target="_top"@endif>
      <img src="/img/icon-settings.svg" alt="" aria-hidden="true" id="sidebar-img-settings">
        <span>{{ trans('sidebar.nav28') }}</span>
      </a>
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
