@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('demotrade::layouts.sidebar',['menu'=>'coins'])
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
        <form action="{{ route('demoCoinEditProcess') }}" method="post">
            @csrf
            <input type="hidden" value="{{ $coin_type }}" name="coin_type" />
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Initial faucet coins") }}</label>
                            <input name="faucet_amount" class="form-control" value="{{ isset($coin->faucet_amount) ? $coin->faucet_amount : old('faucet_amount') }}" type="text" placeholder="{{ __("0.000") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Faucet time restriction (Hours)") }}</label>
                            <input name="faucet_time" class="form-control" value="{{ isset($coin->faucet_time) ? $coin->faucet_time : old('faucet_time') }}" type="number" placeholder="{{ __("0") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Faucet minimum balance") }}</label>
                            <input name="faucet_min_balance" class="form-control" value="{{ isset($coin->faucet_min_balance) ? $coin->faucet_min_balance : old('faucet_min_balance') }}" type="text" placeholder="{{ __("0") }}" />
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
