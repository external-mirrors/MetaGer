{{-- Show all warnings --}}
@if(sizeof($warnings)+sizeof($htmlwarnings) > 0)
	<div class="alert alert-warning">
		<ul>
			@foreach($htmlwarnings as $warning)
				<li>{!! $warning !!}</li>
			@endforeach
			@foreach($warnings as $warning)
				<li>{{ $warning }}</li>
			@endforeach
		</ul>
	</div>
@endif