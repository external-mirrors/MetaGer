{{-- The href of an "open anonymously" link to $target (anything with a link and a proxyLink):
     SafeBrowse's page view without JavaScript (App\Http\SafebrowseNojs: the target, the results
     page's sid, and the key on a query login only, for the reasons given in
     parts/proxy-safebrowse-attribute). It is what a browser follows when no JavaScript takes the
     click; resultpage/proxy.js takes it through parts/proxy-safebrowse-attribute whenever it runs,
     and lets it go here when WebSockets turn out not to work, which this view does not need. --}}
href="{{ \App\Http\SafebrowseNojs::url(request(), $target->link, auth()->guard("key")->user() !== null && auth()->guard("key")->login_method === 'query' ? auth()->guard("key")->user()->key : null) }}"
