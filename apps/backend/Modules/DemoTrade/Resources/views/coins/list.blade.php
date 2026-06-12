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
                    <li class="active-item">{{__('Coins')}}</li>
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
                                    <th scope="col">{{__('Coin Type')}}</th>
                                    <th scope="col">{{__('Faucet Amount')}}</th>
                                    <th scope="col">{{__('Actions')}}</th>
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
                   {"data": "coin_type"},
                   {"data": "faucet_amount"},
                   {"data": "action"}
                ]
            });
    })(jQuery);
</script>
@endsection
