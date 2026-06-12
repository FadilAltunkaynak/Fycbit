@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'trade','sub_menu'=>'order_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title??__('User Trade History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="profile-info-table">
                                    <ul>
                                        <li>
                                            <span>{{__('Total Trade')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $total_trade??0 }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Total Buy Trade')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $total_buy_trade??0 }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Total Sell Trade')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $total_sell_trade??0 }}</strong></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-info-table">
                                    <ul>
                                        
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
@endsection
