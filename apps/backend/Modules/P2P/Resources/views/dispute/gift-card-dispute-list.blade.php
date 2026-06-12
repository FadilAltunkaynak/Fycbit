@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'gift_card', 'sub_menu'=>'gift_card_dispute_list'])
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
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Buyer')}}</th>
                                    <th scope="col">{{__('Seller')}}</th>
                                    <th scope="col">{{__('Currency Type')}}</th>
                                    <th scope="col">{{__('Price')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Created at')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
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
                //ajax: 'aaa',
                //order: [1, 'desc'],
                //autoWidth: true,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                   {"data": "buyer_name"},
                   {"data": "seller_name"},
                   {"data": "currency_type"},
                   {"data": "price"},
                   {"data": "status"},
                   {"data": "created_at"},
                   {"data": "action"}
                ]
            });
    })(jQuery);
</script>
@endsection
