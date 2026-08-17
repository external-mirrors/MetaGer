{{-- Verwendung: @include('parts.utility') --}}

{{-- type="module" is not optional: Vite emits ES modules, and a bundle that
     imports a shared chunk is a syntax error when loaded as a classic script,
     so the file downloads and never runs. Modules defer by default. --}}
<script type="module" src="{{ Vite::asset('resources/js/utility.js') }}"></script>
<link type="text/css" rel="stylesheet" href="{{ Vite::asset('resources/less/utility.less') }}" />
