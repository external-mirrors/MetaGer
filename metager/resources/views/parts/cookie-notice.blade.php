{{--
    The "your browser is blocking cookies" notice
    (App\Authentication\CookieSupport::justAuthenticatedWithoutCookie()).

    A shared partial rather than inline markup in each layout so the copy and
    the class names stay in one place — layouts/staticPages.blade.php includes
    it as-is for every subpage, and index.blade.php includes it too but inside
    #search-wrapper's own grid, since the startpage's hero/search layout is
    sized to the viewport (100dvh, a fixed-position nav cluster aligned to it
    by a reserved band) and a block-level alert dropped in front of that
    layout in normal flow pushed the whole thing down by the alert's height —
    the reserved band no longer lined up with the cluster, which stayed
    pinned to the actual viewport top. See startpage.less's
    #search-wrapper-notices for the positioned version of this same markup.
--}}
@if (isset($cookieNotice))
    <div class="alert alert-warning cookie-notice" role="alert">{{ $cookieNotice }}</div>
@endif
