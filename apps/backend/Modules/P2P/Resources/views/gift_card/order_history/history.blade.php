@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'gift_card', 'sub_menu'=>'orders'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Order History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav wallet-transaction user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id=all_order" data-toggle="pill" href="#all_order_tab"
                            role="tab" aria-controls="all_order_tab" aria-selected="true">
                            {{__('All Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="escrow" data-toggle="pill" href="#escrow_tab"
                            role="tab" aria-controls="escrow_tab" aria-selected="true">
                            {{__('Escrow Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="payment_done_order" data-toggle="pill"
                            href="#payment_done_order_tab" role="tab" aria-controls="payment_done_order_tab"
                            aria-selected="true">
                            {{__('Payment Done Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="payment_transffer_order" data-toggle="pill"
                            href="#payment_transffer_order_tab" role="tab" aria-controls="payment_transffer_order_tab"
                            aria-selected="true">
                            {{__('Payment Done Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="canceled_order" data-toggle="pill"
                            href="#canceled_order_tab" role="tab" aria-controls="canceled_order_tab"
                            aria-selected="true">
                            {{__('Canceled Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="refund_order" data-toggle="pill"
                            href="#refund_order_tab" role="tab" aria-controls="refund_order_tab"
                            aria-selected="true">
                            {{__('Refund Orders')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="released_order" data-toggle="pill"
                            href="#released_order_tab" role="tab" aria-controls="released_order_tab"
                            aria-selected="true">
                            {{__('Released Orders')}}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="all_order_tab" role="tabpanel"
                            aria-labelledby=all_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="all_table" class="table table-borderless custom-table display text-left"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="escrow_tab" role="tabpanel"
                            aria-labelledby="escrow">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="escrow_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="payment_done_order_tab" role="tabpanel"
                            aria-labelledby="payment_done_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="payment_done_order_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="payment_transffer_order_tab" role="tabpanel"
                            aria-labelledby="payment_transffer_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="payment_transffer_order_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="canceled_order_tab" role="tabpanel"
                            aria-labelledby="canceled_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="canceled_order_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="refund_order_tab" role="tabpanel"
                            aria-labelledby="refund_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="refund_order_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="released_order_tab" role="tabpanel"
                            aria-labelledby="released_order">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="released_order_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('Buyer')}}</th>
                                        <th class="all">{{__('Seller')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                        <th class="all">{{__('Card Details')}}</th>
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



    <!-- Modal -->
    <div id="cardDetalsModel" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('Card Details')}}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group text-white">
                                <table>
                                    <tr>
                                        <td>{{__('Category')}} : </td>
                                        <td id="card_category">hello</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Card Titel')}} : </td>
                                        <td id="card_title">Title</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Card Description')}} : </td>
                                        <td id="card_description">Description</td>
                                    </tr>
                                    <tr>
                                        <td>{{__('Card Value')}} : </td>
                                        <td id="card_value">50 BTC</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End -->
@endsection

@section('script')
    <script>
        function getGiftCardDetails(id){
            $.get(
                '{{ route("getGiftCardDetailsAtOrderHistory") }}?id=' + id,
                function(data){
                    if(data?.success){
                        $('#card_category').text(data?.data?.banner?.category?.name);
                        $('#card_title').text(data?.data?.banner?.title);
                        $('#card_description').text(data?.data?.banner?.sub_title);
                        $('#card_value').text(data?.data?.amount + ' ' + data?.data?.coin_type);
                        $("#cardDetalsModel").modal('show');
                    } else {
                        alert(data?.message);
                    }
                    
                }
            );
        }

        (function($) {
            "use strict";

            function renderHistoryTable(url,table){
                $(table).DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: true,
                    ajax: url,
                    // order: [7, 'desc'],
                    autoWidth: false,
                    language: {
                        paginate: {
                            next: 'Next &#8250;',
                            previous: '&#8249; Previous'
                        }
                    },
                    columns: [
                        {"data": "buyer"},
                        {"data": "seller"},
                        {"data": "price"},
                        {"data": "amount"},
                        {"data": "status"},
                        {"data": "created_at"},
                        {"data": "card_details"},
                    ]
                });
            }
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'all','#all_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'1','#escrow_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'2','#payment_done_order_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'3','#payment_transffer_order_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'4','#canceled_order_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'6','#refund_order_table');
            renderHistoryTable('{{route('getGiftCardOrderHistory')}}/?status='+'7','#released_order_table');
        })(jQuery);
    </script>
@endsection
