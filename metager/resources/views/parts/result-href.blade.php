{{-- The href of a link into $target (a Result, a Search\Sitelink): the page
     itself, or — with the "open results anonymously" setting — the proxy, as
     the shield would open it (MetaGer::opensAnonymously). Attributes only, for
     inside an <a>: <a class="…" @include('parts.result-href', ['target' => $x])>. --}}
@if ($metager->opensAnonymously($target))
href="{{ $target->proxyLink }}" @include('parts.proxy-safebrowse-attribute', ['target' => $target])
@else
href="{{ $target->link }}"
@endif
