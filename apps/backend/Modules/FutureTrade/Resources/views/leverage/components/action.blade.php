<div class="activity-icon">
    <ul style="gap: 10px">
        <li class="m-0">
            <a title="{{ __('Reject') }}" href="{{ route('future.leverage.edit', ['coin_pair_uid'=>$model->coin_pair_uid,'uid'=>$model->uid]) }}" class="text-primary" style="font-size: 20px">
                <i class="fa fa-edit" aria-hidden="true"></i>
            </a>
        </li>
        <li class="m-0">
            <a class="text-danger delete-url" style="font-size: 20px" title="{{ __('Delete') }}" href="#" onclick="deleteLeverageModel(event)" data-link="{{ route('future.leverage.delete', ['coin_pair_uid'=>$model->coin_pair_uid,'uid'=>$model->uid]) }}">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </a>
        </li>
    </ul>
</div>