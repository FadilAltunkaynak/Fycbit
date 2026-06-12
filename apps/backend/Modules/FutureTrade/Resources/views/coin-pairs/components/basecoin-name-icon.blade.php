<img 
    src="{{ empty($model?->baseCoin?->coin_icon) ? '' : show_image_path($model?->baseCoin?->coin_icon, 'coin/') }}"
    alt="{{ $model?->baseCoin?->name ?? '' }}"
    width="30px" height="30px"
/>
{{$model->base_coin_code}}