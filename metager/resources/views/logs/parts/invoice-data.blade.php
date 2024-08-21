<div id="invoice-data">
    <h3>@lang('logs.overview.invoice-data.heading')</h3>
    <div class="input-group">
        <label for="email">@lang('logs.overview.invoice-data.email')</label>
        @if($edit_invoice)
            <input type="email" name="email" id="email" placeholder="max@mustermann.de" value="{{ $invoice['email'] }}"
                disabled>
        @else
            <span>{{ $invoice["email"] }}</span>
        @endif
    </div>
    @if($edit_invoice || !empty($invoice["company"]))
        <div class="input-group">
            <label for="company">@lang('logs.overview.invoice-data.company')</label>
            @if($edit_invoice)
                <input type="text" name="company" id="company" value="{{ $invoice['company'] }}" placeholder="Musterfirma"
                    form="update-invoice-data">
            @else
                <span>{{ $invoice["company"] }}</span>
            @endif
        </div>
    @endif
    @if($edit_invoice)
        <div class="input-group">
            <label for="first_name">@lang('logs.overview.invoice-data.first_name')</label>
            <input type="text" name="first_name" id="first_name" value="{{ $invoice['first_name'] }}" placeholder="Max"
                form="update-invoice-data">
        </div>
        <div class="input-group">
            <label for="last_name">@lang('logs.overview.invoice-data.last_name')</label>
            <input type="text" name="last_name" id="last_name" value="{{ $invoice['last_name'] }}" placeholder="Mustermann"
                form="update-invoice-data">
        </div>
    @elseif(!empty($invoice["first_name"]) && !empty($invoice["last_name"]))
        <div class="input-group">
            <label for="full_name">@lang('logs.overview.invoice-data.full_name')</label>
            <span>{{ $invoice["first_name"] }} {{ $invoice["last_name"] }}</span>
        </div>
    @endif
    @if($edit_invoice || !empty($invoice["street"]))
        <div class="input-group">
            <label for="street">@lang('logs.overview.invoice-data.street')</label>
            @if($edit_invoice)
                <input type="text" name="street" id="street" value="{{ $invoice['street'] }}" placeholder="Musterstraße 3"
                    form="update-invoice-data">
            @else
                <span>{{ $invoice["street"] }}</span>
            @endif
        </div>
    @endif
    @if($edit_invoice || !empty($invoice["postal_code"]))
        <div class="input-group">
            <label for="postal_code">@lang('logs.overview.invoice-data.postal_code')</label>
            @if($edit_invoice)
                <input type="text" name="postal_code" id="postal_code" value="{{ $invoice['postal_code'] }}" placeholder="12345"
                    form="update-invoice-data">
            @else
                <span>{{ $invoice["postal_code"] }}</span>
            @endif
        </div>
    @endif
    @if($edit_invoice || !empty($invoice["city"]))
        <div class="input-group">
            <label for="city">@lang('logs.overview.invoice-data.city')</label>
            @if($edit_invoice)
                <input type="text" name="city" id="city" value="{{ $invoice['city'] }}" placeholder="Musterstadt"
                    form="update-invoice-data">
            @else
                <span>{{ $invoice["city"] }}</span>
            @endif
        </div>
    @endif
    @if($edit_invoice)
        <form id="update-invoice-data" method="post" action="{{ route('logs:update_invoice_data') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <button type="submit" class="btn btn-default"
                href="{{ route('logs:overview', ['edit_invoice' => '1']) }}">@lang("logs.overview.invoice-data.save")</button>
        </form>
    @else
        <a href="{{ route('logs:overview', ["edit_invoice" => "1"]) }}">@lang("logs.overview.invoice-data.update")</a>
    @endif
</div>