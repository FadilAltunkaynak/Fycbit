<div class="row">
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__('Trade Details')}}</h6>
            </div>

            <p><strong>Trade ID:</strong> {{ $trade->uid }}</p>
            <p><strong>{{__('Code')}}:</strong> {{ $trade?->code ?? 'N/A' }} </p>
            <p><strong>{{__('Buyer')}}:</strong> {{ $trade?->buyer_email ?? 'N/A'}} </p>
            <p><strong>{{__('Seller')}}:</strong> {{ $trade?->seller_email ?? 'N/A'}} </p>
            <p><strong>{{__('Maker')}}:</strong>
                {{ $trade?->buy_user_id == $trade->maker_id ? ($trade?->buyer_email ?? 'N/A') : $trade?->seller_email ?? 'N/A' }}
            </p>
            <p><strong>{{__('Taker')}}:</strong>
                {{ $trade?->buy_user_id == $trade->taker_id ? ($trade?->buyer_email ?? 'N/A') : $trade?->seller_email ?? 'N/A' }}
            </p>
            <p><strong>{{__('Created At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($trade->created_at)) }}</p>
            <p><strong>{{__('Updated At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($trade->updated_at)) }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__("Amount Details")}}</h6>
            </div>
            <p><strong>{{__('Price')}}:</strong> {{trim_num($trade->price, $trade?->trade_decimal ?? 8) }}
                {{ $trade?->trade_coin_code ?? '' }}</p>
            <p><strong>{{__('Amount')}}:</strong> {{trim_num($trade->amount, $trade?->trade_decimal ?? 8) }}
                {{ $trade?->base_coin_code ?? '' }}</p>
            <p><strong>{{__('Total Price')}}:</strong>
                {{ trim_num(bcmulx($trade->price, $trade->amount, $trade?->trade_decimal ?? 8), $trade?->trade_decimal ?? 8) }}
                {{ $trade?->trade_coin_code ?? '' }}</p>
            <p><strong>{{__('Buyer Fees')}}:</strong>
                {{ $trade?->buy_user_id == $trade->maker_id ? ($trade?->maker_fees ?? 'N/A') : $trade?->taker_fees ?? 'N/A' }}
            </p>
            <p><strong>{{__('Seller Fees')}}:</strong>
                {{ $trade?->buy_user_id == $trade->taker_id ? ($trade?->maker_fees ?? 'N/A') : $trade?->taker_fees ?? 'N/A' }}
            </p>
        </div>
    </div>
</div>