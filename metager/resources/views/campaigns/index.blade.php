@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Gutscheinaktionen — App\Http\Controllers\CampaignController.

	Eine Liste (mit Statistik pro Kampagne) plus ein Anlegeformular darunter,
	wie views/campaign/manage.ejs im Keymanager es zeigte — keine eigene
	Detailseite pro Kampagne, es gibt nichts, das eine bräuchte. $campaigns
	kommt validiert aus CampaignIssuer; wem eine Kampagne gehört, hat
	ausschließlich der Keyserver geprüft (siehe CampaignController's
	Klassenkommentar) — anders als bei Bestellungen gibt es hier keinen
	Schlüssel im Antwortkörper, den man selbst noch vergleichen könnte.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('campaigns.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		<p class="account-section__lede">@lang('campaigns.description')</p>

		@if($unreachable)
			<p class="checkout-consent__error" role="alert">@lang('campaigns.unreachable')</p>
		@endif

		@if(count($campaigns) > 0)
			<div class="campaigns-list">
				@foreach($campaigns as $campaign)
					<div class="campaigns-item">
						<div class="campaigns-item__head">
							<span class="campaigns-item__name">{{ $campaign['name'] }}</span>
							@if($campaign['active'])
								<span class="campaigns-status campaigns-status--active">@lang('campaigns.status.active')</span>
							@elseif($campaign['disabled'])
								<span class="campaigns-status campaigns-status--disabled">@lang('campaigns.status.disabled')</span>
							@else
								<span class="campaigns-status campaigns-status--expired">@lang('campaigns.status.expired')</span>
							@endif
						</div>

						<div class="campaigns-item__facts">
							<span>@lang('campaigns.facts.tokens_per_key', ['tokens' => $campaign['tokens_per_key']])</span>
							@if($campaign['stats'] !== null)
								<span>@lang('campaigns.facts.redeemed', ['redeemed' => $campaign['stats']['vouchers_redeemed'], 'total' => $campaign['stats']['vouchers_total']])</span>
								<span>@lang('campaigns.facts.budget', ['left' => $campaign['stats']['backing_charge'], 'total' => $campaign['total_volume']])</span>
							@endif
							@if($campaign['backing_expires_at'] !== null)
								<span>@lang('campaigns.facts.expires', ['date' => \Illuminate\Support\Carbon::parse($campaign['backing_expires_at'])->isoFormat('L')])</span>
							@endif
						</div>

						<div class="campaigns-item__link">
							<label for="campaigns-public-link-{{ $campaign['id'] }}">@lang('campaigns.public_link')</label>
							<div class="campaigns-item__link-row">
								<input type="text" id="campaigns-public-link-{{ $campaign['id'] }}" class="campaigns-item__link-input"
									readonly value="{{ $campaign['public_link'] }}">
								<button type="button" class="account-btn account-btn--quiet" data-copies="campaigns-public-link-{{ $campaign['id'] }}"
									data-done="@lang('key-create.copy.done')" hidden>@lang('campaigns.copy_link')</button>
							</div>
						</div>

						@if(!$campaign['active'] && !$campaign['disabled'])
							<p class="campaigns-item__hint">@lang('campaigns.delete_note')</p>
						@endif

						<div class="campaigns-item__actions">
							<a class="account-btn account-btn--quiet" href="{{ route('account.campaigns.cards', ['id' => $campaign['id']]) }}" target="_blank" rel="noopener">@lang('campaigns.print_cards')</a>
							@if(!$campaign['disabled'])
								<form method="post" action="{{ route('account.campaigns.disable', ['id' => $campaign['id']]) }}">
									<button type="submit" class="account-btn account-btn--quiet">@lang('campaigns.disable')</button>
								</form>
							@endif
							@if(!$campaign['active'])
								<form method="post" action="{{ route('account.campaigns.delete', ['id' => $campaign['id']]) }}">
									<button type="submit" class="account-btn account-btn--danger">@lang('campaigns.delete')</button>
								</form>
							@endif
						</div>
					</div>
				@endforeach
			</div>
		@endif

		<h2 class="account-section__heading">@lang('campaigns.create.heading')</h2>
		<p class="campaigns-create__intro">@lang('campaigns.create.info')</p>

		@if($errorCode !== null)
			<p class="checkout-consent__error" role="alert">@lang('campaigns.create.error.' . $errorCode)</p>
		@endif

		<form method="post" action="{{ route('account.campaigns.store') }}" class="campaigns-create">
			<div class="campaigns-create__field">
				<label for="campaigns-name">@lang('campaigns.create.name')</label>
				<input type="text" name="name" id="campaigns-name" required maxlength="200" value="{{ $fields['name'] }}">
			</div>

			<div class="campaigns-create__row">
				<div class="campaigns-create__field">
					<label for="campaigns-tokens-per-key">@lang('campaigns.create.tokens_per_key')</label>
					<input type="number" name="tokens_per_key" id="campaigns-tokens-per-key" required min="1" step="1" value="{{ $fields['tokens_per_key'] }}">
				</div>
				<div class="campaigns-create__field">
					<label for="campaigns-total-volume">@lang('campaigns.create.total_volume')</label>
					<input type="number" name="total_volume" id="campaigns-total-volume" required min="1" step="1" max="{{ $maxCampaignVolume }}" value="{{ $fields['total_volume'] }}">
					<div class="campaigns-create__hint">@lang('campaigns.create.total_volume_hint', ['charge' => $maxCampaignVolume])</div>
				</div>
			</div>

			<div class="campaigns-create__field">
				<label for="campaigns-voucher-count">@lang('campaigns.create.voucher_count')</label>
				<input type="number" name="voucher_count" id="campaigns-voucher-count" min="1" step="1" value="{{ $fields['voucher_count'] }}">
				<div class="campaigns-create__hint">@lang('campaigns.create.voucher_count_hint')</div>
			</div>

			<button type="submit" class="account-btn account-btn--primary">@lang('campaigns.create.submit')</button>
		</form>
	</section>
</div>
@endsection
