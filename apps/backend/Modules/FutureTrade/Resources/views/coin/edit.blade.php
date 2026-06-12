@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coins'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Coin setting')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <form action="{{ route('coinEditProcess') }}" method="post">
            @csrf
            <input type="hidden" value="{{ $coin_type }}" name="coin_type" />
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Minimum Amount") }}</label>
                            <input name="minimum_price" class="form-control" value="{{ isset($setting->minimum_price) ? $setting->minimum_price : old('minimum_price') }}" type="text" placeholder="{{ __("Minimum Price") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Maximum Amount") }}</label>
                            <input name="maximum_price" class="form-control" value="{{ isset($setting->maximum_price) ? $setting->maximum_price : old('maximum_price') }}" type="text" placeholder="{{ __("Maximum Price") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Buy Fees (%)") }}</label>
                            <input name="buy_fees" class="form-control" value="{{ isset($setting->buy_fees) ? $setting->buy_fees : old('buy_fees') }}" type="text" placeholder="{{ __("Buy Fees") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Sell Fees (%)") }}</label>
                            <input name="sell_fees" class="form-control" value="{{ isset($setting->sell_fees) ? $setting->sell_fees : old('sell_fees') }}" type="text" placeholder="{{ __("Sell Fees") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("P2P Trade") }}</label>
                            <select name="trade_status" class="selectpicker" data-width="100%" data-style="btn-dark">
                                <option @if(isset($setting->trade_status) && $setting->trade_status == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{ __("Disable") }}</option>
                                <option @if(isset($setting->trade_status) && $setting->trade_status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{ __("Enable") }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
@endsection
