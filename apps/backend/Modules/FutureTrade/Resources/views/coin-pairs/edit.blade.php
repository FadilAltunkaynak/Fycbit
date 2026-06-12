@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coin-pairs'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row align-items-center">
            <div class="col-md-6">
                <ul>
                    <li class="active-item">{{__('Future Coin Pair')}}</li>
                </ul>
            </div>
            <div class="col-md-6 text-right">
                @if(isset($item))
                    <a class="btn btn-info mr-1" href="{{ route('future.leverage.list', $item->uid) }}">
                        <i class="fa fa-cogs mr-1"></i>{{ __('Leverage Settings') }}
                    </a>
                    <a class="btn btn-secondary mr-1" href="{{ route('future.coin-pairs.bot-settings.edit', [$item->uid]) }}">
                        <svg class="text-info mr-2" height="20" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M32,224H64V416H32A31.96166,31.96166,0,0,1,0,384V256A31.96166,31.96166,0,0,1,32,224Zm512-48V448a64.06328,64.06328,0,0,1-64,64H160a64.06328,64.06328,0,0,1-64-64V176a79.974,79.974,0,0,1,80-80H288V32a32,32,0,0,1,64,0V96H464A79.974,79.974,0,0,1,544,176ZM264,256a40,40,0,1,0-40,40A39.997,39.997,0,0,0,264,256Zm-8,128H192v32h64Zm96,0H288v32h64ZM456,256a40,40,0,1,0-40,40A39.997,39.997,0,0,0,456,256Zm-8,128H384v32h64ZM640,256V384a31.96166,31.96166,0,0,1-32,32H576V224h32A31.96166,31.96166,0,0,1,640,256Z"/></svg>
                        {{ __('Bot Settings') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <form action="{{ route('future.coin-pairs.store') }}" method="post">
                        @csrf
                        @if(isset($item))
                            <input type="hidden" name="coin_pair_id" value="{{ $item->uid }}" />
                        @endif
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Collateral Type') }} <span class="text-danger">*</span></div>
                                        <select name="collateral_type" class="form-control" data-width="100%" data-style="btn-dark">
                                            {!! \Modules\FutureTrade\Emum\CollateralTypeEnum::toSelectOptions(1) !!}
                                        </select>
                                        <pre class="text-danger">{{ $errors->first('collateral_type') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Base Coin') }} <span class="text-danger">*</span></div>
                                        <select name="base_coin_code" class="form-control" data-width="100%" data-style="btn-dark" @disabled(isset($item))>
                                            <option value="">{{ __("Select Base Coin") }}</option>
                                            @foreach ($baseCoins as $coin)
                                                <option value="{{ $coin->coin_type }}" @selected($coin->coin_type == ($item?->base_coin_code ?? old("base_coin_code")))>{{ $coin->coin_type }}</option>
                                            @endforeach
                                        </select>
                                        <pre class="text-danger">{{ $errors->first('base_coin_code') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Trade Coin') }} <span class="text-danger">*</span></div>
                                        <select name="trade_coin_code" class="form-control" data-width="100%" data-style="btn-dark" @disabled(isset($item))>
                                            <option value="">{{ __("Select Trade Coin") }}</option>
                                            @foreach ($tradeCoins as $coin)
                                                <option value="{{ $coin->coin_type }}" @selected($coin->coin_type == ($item?->trade_coin_code ?? old("trade_coin_code")))>{{ $coin->coin_type }}</option>
                                            @endforeach
                                        </select>
                                        <pre class="text-danger">{{ $errors->first('trade_coin_code') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Base Coin Decimal') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="base_decimal" step="any"
                                            value="{{ old('base_decimal', $item?->base_decimal) }}" />
                                        <pre class="text-danger">{{ $errors->first('base_decimal') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Trade Coin Decimal') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="trade_decimal" step="any"
                                            value="{{ old('trade_decimal', $item?->trade_decimal) }}" />
                                        <pre class="text-danger">{{ $errors->first('trade_decimal') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Cap Ratio (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="cap_ratio" step="any"
                                            value="{{ old('cap_ratio', $item?->cap_ratio) }}" />
                                        <pre class="text-danger">{{ $errors->first('cap_ratio') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Floor Ratio (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="floor_ratio" step="any"
                                            value="{{ old('floor_ratio', $item?->floor_ratio) }}" />
                                        <pre class="text-danger">{{ $errors->first('floor_ratio') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maker Fee (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="maker_fees_percent" step="any"
                                            value="{{ old('maker_fees_percent', $item?->maker_fees_percent) }}" />
                                        <pre class="text-danger">{{ $errors->first('maker_fees_percent') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Taker Fee (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="taker_fees_percent" step="any"
                                            value="{{ old('taker_fees_percent', $item?->taker_fees_percent) }}" />
                                        <pre class="text-danger">{{ $errors->first('taker_fees_percent') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Minimum Amount') }}</div>
                                        <input type="number" class="form-control" name="min_amount" step="any"
                                            value="{{ old('min_amount', $item?->min_amount) }}" />
                                        <pre class="text-danger">{{ $errors->first('min_amount') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maximum Amount') }}</div>
                                        <input type="number" class="form-control" name="max_amount" step="any"
                                            value="{{ old('max_amount', $item?->max_amount) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_amount') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Minimum Stop Price (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="min_stop_limit_percent" step="any"
                                            value="{{ old('min_stop_limit_percent', $item?->min_stop_limit_percent) }}" />
                                        <pre class="text-danger">{{ $errors->first('min_stop_limit_percent') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maximum Stop Price (Percentage)') }}</div>
                                        <input type="number" class="form-control" name="max_stop_limit_percent" step="any"
                                            value="{{ old('max_stop_limit_percent', $item?->max_stop_limit_percent) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_stop_limit_percent') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Market Order Price Max Slippage (Percentage)') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="slippage_percent" step="any"
                                            value="{{ old('slippage_percent', $item?->slippage_percent) }}" />
                                        <pre class="text-danger">{{ $errors->first('slippage_percent') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maximum Open Order') }}</div>
                                        <input type="number" class="form-control" name="max_open_orders" step="any"
                                            value="{{ old('max_open_orders', $item?->max_open_orders) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_open_orders') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Max Leverage') }}</div>
                                        <input type="number" class="form-control" name="max_leverage" step="any"
                                            value="{{ old('max_leverage', $item?->max_leverage) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_leverage') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Status') }} <span class="text-danger">*</span></div>
                                        <select name="status" class="form-control" data-width="100%">
                                            {!! \Modules\FutureTrade\Emum\FutureCoinPairStatusEnum::toSelectOptions($item?->status?->value) !!}
                                        </select>
                                        <pre class="text-danger">{{ $errors->first('status') }}</pre>
                                    </div>
                                </div>
                            </div> -->
                            <input type="hidden" name="status" value="{{ $item?->status?->value ?? 0 }}">
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{ __("Save") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    "use strict";

</script>
@endsection
