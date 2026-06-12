<img 
    src="{{ empty($model?->tradeCoin?->coin_icon) ? '' : show_image_path($model?->tradeCoin?->coin_icon, 'coin/') }}"
    alt="{{ $model?->tradeCoin?->name ?? '' }}"
    width="30px" height="30px"
/>
{{$model->trade_coin_code}}