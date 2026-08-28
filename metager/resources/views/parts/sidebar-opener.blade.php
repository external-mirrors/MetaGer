{{-- A plain menu button again. The signed-in dot it used to carry was an 8px
     circle with no separation from the ≡ strokes, and it said only "something",
     never which account or how much is left. parts/account-pill.blade.php sits
     immediately beside this and answers all three.

     The matching ✕ is not here: it lives in parts/sidebar.blade.php, with the
     checkbox whose state reveals it. See the comment there. --}}
<label aria-label="@lang('sidebar.opener')" class="sidebar-opener navigation-element {{$class ?? ''}}" for="sidebarToggle">≡</label>
