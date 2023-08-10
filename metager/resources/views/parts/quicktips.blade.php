@if($quicktips !== null && sizeof($quicktips) > 0)
<div id="additions-container" data-authorized="{{ !app(\App\SearchSettings::class)->self_advertisements || app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch() ? 'true' : 'false' }}">
	@include('layouts.keyboardNavBox')
	<div id="quicktips">
		@if( app(\App\SearchSettings::class)->quicktips )
		@include('quicktips', ['quicktips', $quicktips])
		@endif
	</div>
</div>
@endif