@extends('admin.master')
@section('title', __('Bot Settings'))
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coin-pairs'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row align-items-center">
            <div class="col-md-8">
                <ul>
                    <li><a href="{{ route('future.coin-pairs.list') }}">{{ __('Future Coin Pairs') }}</a></li>
                    <li class="active-item">{{ __('Bot Settings for :code', ['code' => $item->code]) }}</li>
                </ul>
            </div>
            <div class="col-md-4 text-right">
                <a class="btn btn-outline-primary" href="{{ route('future.coin-pairs.edit', [$item->uid]) }}">
                    <i class="fa fa-arrow-left mr-1"></i>{{ __('Return to Coin Pair') }}
                </a>
            </div>
        </div>
    </div>

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <form action="{{ route('future.coin-pairs.bot-settings.update', ['uid' => $item->uid]) }}" method="post">
                        @csrf

                        <div class="mb-3">
                            <p class="mb-2 text-muted">
                                {{ __('Last updated at: :time', ['time' => (($botSetting?->updated_at ?? $item->updated_at))->format('d M, Y h:i:s a')]) }}
                            </p>
                            <p class="text-danger font-weight-bold mb-4">
                                {{ __('CAUTION: Bots in futures can cause system money loss, if user trades with bot and if user wins(lots of profit) then bot/system will loose. Run the bots at your own risk') }}
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Amount Minimum (:coin)', ['coin' => $item->base_coin_code]) }}</div>
                                        <input type="number" class="form-control" name="bot_amount_low" step="any"
                                            value="{{ old('bot_amount_low', $botSetting?->amount_low) }}">
                                        <pre class="text-danger">{{ $errors->first('bot_amount_low') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Amount Maximum (:coin)', ['coin' => $item->base_coin_code]) }}</div>
                                        <input type="number" class="form-control" name="bot_amount_high" step="any"
                                            value="{{ old('bot_amount_high', $botSetting?->amount_high) }}">
                                        <pre class="text-danger">{{ $errors->first('bot_amount_high') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Price Minimum (:coin)', ['coin' => $item->trade_coin_code]) }}</div>
                                        <input type="number" class="form-control" name="bot_price_low" step="any"
                                            value="{{ old('bot_price_low', $botSetting?->price_low) }}">
                                        <pre class="text-danger">{{ $errors->first('bot_price_low') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Price Maximum (:coin)', ['coin' => $item->trade_coin_code]) }}</div>
                                        <input type="number" class="form-control" name="bot_price_high" step="any"
                                            value="{{ old('bot_price_high', $botSetting?->price_high) }}">
                                        <pre class="text-danger">{{ $errors->first('bot_price_high') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Order Place Time Interval (In second)') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="bot_order_interval" min="1"
                                            value="{{ old('bot_order_interval', $botSetting?->order_interval ?: 1) }}">
                                        <pre class="text-danger">{{ $errors->first('bot_order_interval') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Status') }}</div>
                                        <select name="bot_status" class="form-control">
                                            <option value="1" @selected((int) old('bot_status', $botSetting?->status) === 1)>{{ __('Active') }}</option>
                                            <option value="0" @selected((int) old('bot_status', $botSetting?->status) === 0)>{{ __('Inactive') }}</option>
                                        </select>
                                        <pre class="text-danger">{{ $errors->first('bot_status') }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="text-danger font-weight-bold mb-3">
                                {{ __('Danger Zone: These actions permanently delete data for this coin pair.') }}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <form action="{{ route('future.coin-pairs.bot-settings.clean-chart', ['uid' => $item->uid]) }}" method="post"
                                  onsubmit="return confirm('{{ __('Are you sure you want to clean chart data for this coin pair?') }}')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">{{ __('Clean Chart Data') }}</button>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form action="{{ route('future.coin-pairs.bot-settings.clean-bot', ['uid' => $item->uid]) }}" method="post"
                                  onsubmit="return confirm('{{ __('Are you sure you want to clean bot data for this coin pair? This will delete buy, sell, and trade data.') }}')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">{{ __('Clean Bot Data') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
