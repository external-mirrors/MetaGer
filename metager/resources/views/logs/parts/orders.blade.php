@if(sizeof(app(App\Models\Logs\LogsAccountProvider::class)->client->orders) > 0)
    <div id="orders">
        <h2>@lang("logs.orders.heading")</h2>
        <table>
            <thead>
                <tr>
                    <td>@lang("logs.orders.thead.from")</td>
                    <td>@lang("logs.orders.thead.to")</td>
                    <td>@lang("logs.orders.thead.price")</td>
                    <td>@lang("logs.orders.thead.status")</td>
                    <td>@lang("logs.orders.thead.invoice")</td>
                </tr>
            </thead>
            <tbody>
                @foreach(app(App\Models\Logs\LogsAccountProvider::class)->client->orders as $order)
                    <tr>
                        <td>{{ $order->from->format("d.m.Y H:i:s") }}</td>
                        <td>{{ $order->to->format("d.m.Y H:i:s") }}</td>
                        <td>{{ $order->getDiscountedPrice() }}€</td>
                        <td>
                            @if(!is_null($order->invoice))
                                @lang("logs.orders.status." . $order->invoice->status)
                            @endif
                        </td>
                        <td>
                            @if(!is_null($order->invoice))
                                <a href="{{ $order->invoice->invitation_link }}" target="_blanK">{{ $order->invoice->number }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif