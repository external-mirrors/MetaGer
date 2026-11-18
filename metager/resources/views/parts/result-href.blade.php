{{-- The href of a link into $target (a Result, a Search\Sitelink): the page
     itself, or — with the "open results anonymously" setting — SafeBrowse, as
     the shield would open it (MetaGer::opensAnonymously, parts/proxy-href). Attributes only, for
     inside an <a>: <a class="…" @include('parts.result-href', ['target' => $x])>. --}}
@if ($metager->opensAnonymously($target))
@include('parts.proxy-href', ['target' => $target]) @include('parts.proxy-safebrowse-attribute', ['target' => $target])
@else
href="{{ $target->link }}"
@endif
