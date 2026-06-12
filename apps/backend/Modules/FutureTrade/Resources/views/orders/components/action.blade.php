<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        {{ __("Actions") }}
    </button>

    <div class="dropdown-menu py-0 my-0 shadow-sm border-0" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item" onclick="getOrderDetails('{{$model->uid}}')" href="#" style="background:#f8f6ff;">
            <i class="fa fa-edit text-purple mr-2"></i> {{ __("View") }}
        </a>

        @if ($model->status == \Modules\FutureTrade\Emum\OrderStatusEnum::PENDING)
            <a class="dropdown-item cancel-url" onclick="cancelModel(event)" href="#" data-url="{{ route('future.order.cancel',['uid' => $model->uid, 'type' => $model->order_type->value]) }}" style="background:#fff5f5;">
                <i class="fa fa-trash text-danger mr-2"></i> {{ __("Cancel") }}
            </a>
        @endif
    </div>
</div>