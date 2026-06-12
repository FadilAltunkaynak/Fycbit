@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'trade','sub_menu'=>'order_list_'.$status??0])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title?? __('Order List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">

        <div class="row">
            <div class="col-12">
                <div class="mr-2 mb-1">
                </div><br>
                <div class="card-body">
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
