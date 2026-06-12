@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'currency'])
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
        <form action="{{ route('currencyEditProcess') }}" method="post">
            @csrf
            <input type="hidden" value="{{ $code }}" name="code" />
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Minimum Price") }}</label>
                            <input name="minimum_price" class="form-control" value="{{ isset($setting->minimum_price) ? $setting->minimum_price : old('minimum_price') }}" type="number" placeholder="{{ __("Minimum Price") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Maximum Price") }}</label>
                            <input name="maximum_price" class="form-control" value="{{ isset($setting->maximum_price) ? $setting->maximum_price : old('maximum_price') }}" type="number" placeholder="{{ __("Maximum Price") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("P2P Trade") }}</label>
                            <select name="trade_status" class="selectpicker" data-width="100%" data-style="btn-dark">
                                <option @if(isset($setting->trade_status) && $setting->trade_status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{ __("Enable") }}</option>
                                <option @if(isset($setting->trade_status) && $setting->trade_status == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{ __("Disable") }}</option>
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
