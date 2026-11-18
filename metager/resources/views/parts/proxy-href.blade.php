{{-- The href of an "open anonymously" link to $target (anything with a link and a proxyLink):
     what a browser follows when no JavaScript takes the click (resultpage/proxy.js does, through
     parts/proxy-safebrowse-attribute, whenever it runs).

     For clients that can use SafeBrowse that is SafeBrowse's page view without JavaScript
     (App\Http\SafebrowseNojs: the target, the results page's sid, and the key on a query login
     only, for the reasons given in parts/proxy-safebrowse-attribute). The same href is where
     resultpage/proxy.js lets the click go when WebSockets turn out not to work, which the no-JS
     view does not need. Clients that cannot authenticate against SafeBrowse
     (ClientCapabilities) keep the old proxy. --}}
@if (\App\Http\ClientCapabilities::supportsSafebrowse(request()))
href="{{ \App\Http\SafebrowseNojs::url(request(), $target->link, auth()->guard("key")->user() !== null && auth()->guard("key")->login_method === 'query' ? auth()->guard("key")->user()->key : null) }}"
@else
href="{{ $target->proxyLink }}"
@endif
