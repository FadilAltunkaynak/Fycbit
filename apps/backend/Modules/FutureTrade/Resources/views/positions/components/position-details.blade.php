<div class="row">
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__('Position Details')}}</h6>
            </div>

            <p><strong>Position ID:</strong> {{ $position->uid }}</p>
            <p><strong>{{__('Code')}}:</strong> {{ $position?->code ?? 'N/A' }} </p>
            <p><strong>{{__('Type')}}:</strong> {!! $position?->order_type->bothLabelWithColor() ?? 'N/A'!!} </p>
            <p><strong>{{__('Margin Mode')}}:</strong> {{ $position?->margin_mode->label() ?? 'N/A'}} </p>
            <p><strong>{{__('Created At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($position->created_at)) }}</p>
            <p><strong>{{__('Updated At')}}:</strong> {{ date("d-m-Y H:i a", strtotime($position->updated_at)) }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 card rounded">
            <div class="card">
                <h6 class="font-weight-bold text-light mb-3">{{__("Amount Details")}}</h6>
            </div>
            <p><strong>{{__('Price')}}:</strong> {{trim_num($position->price, $position?->trade_decimal ?? 8) }}
                {{ $position?->trade_coin_code ?? '' }}</p>
            <p><strong>{{__('Amount')}}:</strong> {{trim_num($position->amount, $position?->trade_decimal ?? 8) }}
                {{ $position?->base_coin_code ?? '' }}</p>
        </div>
    </div>
</div>