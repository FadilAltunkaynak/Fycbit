<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Bitgo Wallet ID')}}</div>
                @if(env('APP_MODE') == 'demo')
                    <input type="text" class="form-control" value="{{'disablefordemo'}}">
                @else
                    <input class="form-control" type="text" name="bitgo_wallet_id"
                           placeholder="" value="{{$coin_setting ? $coin_setting->bitgo_wallet_id : ''}}">
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Bitgo Wallet Password')}}</div>
                @if(env('APP_MODE') == 'demo')
                    <input type="password" class="form-control" value="{{'disablefordemo'}}">
                @else
                    <input class="form-control" type="password" name="bitgo_wallet"
                           placeholder="" value="{{ isset($coin_setting->bitgo_wallet) ? decrypt($coin_setting->bitgo_wallet) : ''}}">
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="controls">
                <div class="form-label">{{__('Bitgo Wallet Chain')}}</div>
                <input type="text" class="form-control" name="chain"
                       @if(isset($coin_setting))value="{{$coin_setting->chain}}" @else value="1" @endif>
            </div>
        </div>
    </div>
</div>
