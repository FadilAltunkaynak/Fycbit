@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'dashboarded'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Dashboard')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">


            <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Total Buy Advertisement')}}</p>
                                    <h3>{{ $buy ?? 0 }}</h3>
                                    <a href="{{ route('adsListPage') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/ads.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Total Sell Advertisement')}}</p>
                                    <h3>{{ $sell ?? 0 }}</h3>
                                    <a href="{{ route('adsListPage').'?tab=sell' }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/ads.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Total Trade')}}</p>
                                    <h3>{{ $trade ?? 0 }}</h3>
                                    <a href="{{ route('getOrderList',['status' => TRADE_STATUS_TRANSFER_DONE]) }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Active Buy Order')}}</p>
                                    <h3>{{ $active_buy ?? 0 }}</h3>
                                    <a href="{{ route('getOrderList',['status' => TRADE_STATUS_TRANSFER_DONE]) }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Active Sell Order')}}</p>
                                    <h3>{{ $active_sell ?? 0 }}</h3>
                                    <a href="{{ route('getOrderList',['status' => TRADE_STATUS_TRANSFER_DONE]) }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 col-12 mb-4">
                    <div class="card status-card status-card-bg-average">
                        <div class="card-body py-0">
                            <div class="status-card-inner">
                                <div class="content">
                                    <p>{{__('Active Disput Order')}}</p>
                                    <h3>{{ $active_order ?? 0 }}</h3>
                                    <a href="{{ route('getOrderList',['status' => TRADE_STATUS_TRANSFER_DONE]) }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                </div>
                                <div class="icon">
                                    <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="user-management user-chart card">
                <div class="row">
                    <div class="col-12">

                        <div class="card-body">
                        <div class="ml-1 card-top">
                            <h4>{{__('Escrowed Order')}}</h4>
                        </div>
                            <div class="table-responsive">
                                <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="all">{{__('Order ID')}}</th>
                                            <th scope="col">{{__('Buyer')}}</th>
                                            <th scope="col">{{__('Seller')}}</th>
                                            <th scope="col">{{__('Order Type')}}</th>
                                            <th scope="col">{{__('Coin Type')}}</th>
                                            <th scope="col">{{__('Coin Rate')}}</th>
                                            <th scope="col">{{__('Amount')}}</th>
                                            <th scope="col">{{__('Price')}}</th>
                                            <th scope="col">{{__('Status')}}</th>
                                            <th scope="col">{{__('Reported')}}</th>
                                            <th scope="col">{{__('Created At')}}</th>
                                            <th scope="col" class="all">{{__('Actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    </div>
    <!-- /User Management -->


@endsection
@section('script')

<script>
     (function($) {
            "use strict";

            $('#table-coin').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: '{{ route('getOrderList',[ "status" => TRADE_STATUS_ESCROW ]) }}',
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                   {"data": "order_id"},
                   {"data": "buyer_name"},
                   {"data": "seller_name"},
                   {"data": "order_type"},
                   {"data": "coin_type"},
                   {"data": "rate"},
                   {"data": "amount"},
                   {"data": "price"},
                   {"data": "status"},
                   {"data": "reported"},
                   {"data": "created_at"},
                   {"data": "action"},
                ]
            });
    })(jQuery);
</script>


@endsection

