@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'payment_time'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Payment Time')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <form action="{{ route('p2pPaymentTimeCreate') }}" method="post">
            @csrf
            @if(isset($time->uid))
                <input type="hidden" value="{{ $time->uid }}" name="uid" />
            @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Time ( Minutes )") }}</label>
                            <input name="time" class="form-control" value="{{ isset($time->time) ? $time->time : old('time') }}" type="number" placeholder="{{ __("Time") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Status") }}</label>
                            <div class="cp-select-area">
                                <select name="status" class="form-control">
                                    <option @if(isset($time['status']) && $time->status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("ON")}}</option>
                                    <option @if(isset($time['status']) && $time->status == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("OFF")}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                    @if(isset($time->id))
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    @else
                        <button class="button-primary theme-btn">{{__('Create')}}</button>
                    @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
@endsection
