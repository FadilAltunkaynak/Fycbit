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
                    <li class="active-item">{{$title??__('Disputed List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-white">
                            {{ __('Order Details')}}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if (isset($order_details))
                                <div class="col-md-6">
                                    <div class="profile-info-table">
                                        <ul>
                                            <li>
                                                <span>{{__('Buyer')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    @if (isset($order_details->buyer))
                                                        <a href="{{route('getUserTradeDetails', [encrypt($order_details->buyer->id)])}}">
                                                            <strong>{{ $order_details->buyer->first_name.' '.$order_details->buyer->last_name }}</strong>
                                                        </a>    
                                                    @else
                                                        <strong>{{ __('N/A') }}</strong>
                                                    @endif
                                                    
                                                </span>
                                            </li>
                                            <li>
                                                <span>{{__('Seller')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    @if (isset($order_details->seller))
                                                        <a href="{{route('getUserTradeDetails', [encrypt($order_details->seller->id)])}}">
                                                            <strong>{{ $order_details->seller->first_name.' '.$order_details->seller->last_name }}</strong>
                                                        </a>    
                                                    @else
                                                        <strong>{{ __('N/A') }}</strong>
                                                    @endif
                                                    
                                            </li>
                                            <li>
                                                <span>{{__('Order ID')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->uid }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Coin Type')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->coin_type }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Coin Amount')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->amount.' '.$order_details->coin_type }}</strong></span>
                                            </li>

                                            <li>
                                                <span>{{__('Coin Rate')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->rate.' '.$order_details->currency }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Coin Price')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->price.' '.$order_details->currency }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Buyer Fees')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->buyer_fees.' '.$order_details->coin_type }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Seller Fees')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->seller_fees.' '.$order_details->coin_type }}</strong></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-info-table">
                                        <ul>
                                            <li>
                                                <span>{{__('Order Status')}}</span>
                                                <span class="dot">:</span>
                                                <span class="btn btn-sm btn-info p-3">
                                                    {{tradeStatusListP2P($order_details->status)}}
                                                </span>
                                            </li>
                                            <li>
                                                <span>{{__('Payment Status')}}</span>
                                                <span class="dot">:</span>
                                                @if ($order_details->payment_status == STATUS_ACTIVE)
                                                    <span class="btn btn-sm btn-success p-3">
                                                        {{ __('Success')}}
                                                    </span>
                                                @else
                                                    <span class="btn btn-sm btn-warning p-3">
                                                        {{ __('Pending')}}
                                                    </span>
                                                @endif
                                            </li>
                                            <li>
                                                <span>{{__('Transaction Id')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    {{ $order_details->transaction_id}}
                                                </span>
                                            </li>
                                            <li>
                                                <span>{{__('Reported by')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    {{isset($order_details->reported_user) ? $order_details->reported_user->first_name.' '.$order_details->reported_user->last_name : __('N/A')}}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <div class="profile-info-table">
                                        <ul>
                                            <li>
                                                <span class="text-center"> {{__('Not Found')}}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($order_details->dispute_details))
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-white">
                                {{ __('Dispute Details')}}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="profile-info-table">
                                        <ul>
                                            <li>
                                                <span>{{__('Dispute Status')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    @if ($order_details->dispute_details->status == STATUS_ACTIVE)
                                                        <span class="btn btn-sm btn-success p-3">
                                                            {{ __('Success')}}
                                                        </span>
                                                    @else
                                                        <span class="btn btn-sm btn-warning p-3">
                                                            {{ __('Pending')}}
                                                        </span>
                                                    @endif
                                                </span>
                                            </li>
                                            <li>
                                                <span>{{__('Reason Subject')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->dispute_details->reason_heading }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Reason Details')}}</span>
                                                <span class="dot">:</span>
                                                <span><strong>{{ $order_details->dispute_details->details }}</strong></span>
                                            </li>
                                            <li>
                                                <span>{{__('Attachment')}}</span>
                                                <span class="dot">:</span>
                                                <span>
                                                    <a href="{{asset('storage').'/'.PAYMENT_SLIP_PATH.'/'.$order_details->dispute_details->image}}" target="_blank">
                                                        <img style="width:40px;height:40px;" src="{{asset('storage').'/'.PAYMENT_SLIP_PATH.'/'.$order_details->dispute_details->image}}" alt="">
                                                    </a>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
@section('script')
@endsection
