{{--
    One hidden `key` input, only when this visitor's key is riding in the URL
    without a matching cookie (App\Authentication\CookieSupport::keyMissingCookie()).
    Without it, a POST from a page whose own address carries `key` would submit
    without one — CookieCarryingUrlGenerator only ever sees what's on *this*
    request, and a form body is a different request than the page it's on.

    Do not @include this in a form that already has its own `<input name="key">`
    for something else — settings/allSettings.blade.php's forms use `key` for a
    cookie *name* being deleted, not the auth key, and two same-named fields in
    one POST body is a silent collision, not a merge.
--}}
@if (\App\Authentication\CookieSupport::keyMissingCookie(request()))
    <input type="hidden" name="key" value="{{ request()->query('key') }}">
@endif
