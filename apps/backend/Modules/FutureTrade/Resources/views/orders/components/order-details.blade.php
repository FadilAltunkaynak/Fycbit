<div class="row">
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__('Buy Order Details')}}</h6>
            </div>

            <p><strong>Order ID:</strong> {{ $order->uid }}</p>
            <p><strong>{{__('Code')}}:</strong> {{ $order?->coinPair?->code ?? 'N/A' }} </p>
            <p><strong>{{__('Buyer')}}:</strong> {{ $order?->user?->email ?? 'N/A'}} </p>
            <p><strong>{{__('Created At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($order->created_at)) }}</p>
            <p><strong>{{__('Updated At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($order->updated_at)) }}</p>

            <p>
                <strong>{{__('Order Type')}}:</strong>
                <span class="badge badge-primary">{{ $order?->order_type?->label() ?? 'N/A' }}</span>
            </p>

            <p>
                <strong>{{__('Status')}}:</strong>
                <span class="badge badge-success"> {{ $order?->status?->label() ?? 'N/A' }} </span>
            </p>

            <p><strong>{{__('Margin Mode')}}:</strong> {{ $order->margin_mode->label() ?? 'N/A' }} </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__("Amount Details")}}</h6>
            </div>
            <p><strong>{{__('Price')}}:</strong> {{trim_num($order->price, $order?->coinPair?->trade_decimal ?? 8) }}
                {{ $order?->coinPair?->trade_coin_code ?? '' }}</p>
            <p><strong>{{__('Size')}}:</strong> {{trim_num($order->amount, $order?->coinPair?->trade_decimal ?? 8) }}
                {{ $order?->coinPair?->base_coin_code ?? '' }}</p>
            <p><strong>{{__('Total Cost')}}:</strong>
                {{ trim_num(bcmulx($order->price, $order->amount, $order?->coinPair?->trade_decimal ?? 8), $order?->coinPair?->trade_decimal ?? 8) }}
                {{ $order?->coinPair?->trade_coin_code ?? '' }}</p>
            <p><strong>{{__('Processed Amount')}}:</strong>
                {{ trim_num($order->processed_amount, $order?->coinPair?->trade_decimal ?? 8) }}
                {{ $order?->coinPair?->base_coin_code ?? '' }}</p>
            <p><strong>{{__('Pending Amount')}}:</strong>
                {{ trim_num($order->pending_amount, $order?->coinPair?->trade_decimal ?? 8) }}
                {{ $order?->coinPair?->base_coin_code ?? '' }}</p>
            <p><strong>{{__('Stop Price')}}:</strong>
                {{ $order->stop_price > 0 ? trim_num($order->stop_price, $order?->coinPair?->trade_decimal ?? 8) : 'N/A' }}
            </p>
            <p><strong>{{__('Is Reduce Only')}}:</strong> {{ $order->is_reduce ? __('Yes') : __('No') }}</p>
            <p><strong>{{__('Take Profit Price')}}:</strong>
                {{ $order->tp_price > 0 ? trim_num($order->tp_price, $order?->coinPair?->trade_decimal ?? 8) : 'N/A' }}
            </p>
            <p><strong>{{__('Stop Loss Price')}}:</strong>
                {{ $order->sl_price > 0 ? trim_num($order->sl_price, $order?->coinPair?->trade_decimal ?? 8) : 'N/A' }}
            </p>
        </div>
    </div>
</div>