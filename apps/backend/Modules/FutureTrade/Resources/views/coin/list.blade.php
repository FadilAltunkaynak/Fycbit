@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coins'])
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
                    <a href="{{ route("fetchNewCoins") }}" class="btn btn-primary mb-2">{{ __("Fatch Coins") }}</a>
                    <div class="table-responsive">
                        <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="all">{{__('Coin Name')}}</th>
                                    <th scope="col">{{__('Coin Type')}}</th>
                                    <th scope="col">{{__('Network')}}</th>
                                    <th scope="col">{{__('Coin Price')}}</th>
                                    <th scope="col">{{__('Coin Status')}}</th>
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
                   {"data": "name"},
                   {"data": "coin_type"},
                   {"data": "network"},
                   {"data": "price"},
                   {"data": "status"},
                   {"data": "action"}
                ]
            });
    })(jQuery);

    function coinStatusChangeCallback(response){
        console.log(response);
        if(response.success){
            VanillaToasts.create({
                    text: response.message,
                    backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                    type: 'success',
                    timeout: 40000
            });
        } else {
            VanillaToasts.create({
                    text: response.message,
                    backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                    type: 'warning',
                    timeout: 40000
            });
        }
    }

    function changeCoinStatus(coin){
        // set post url
        let url = '{{ route("coinStatusChange") }}';

        // sending post request
        $.post(
            // post url
            url,
            // post data
            {
                _token: '{{ csrf_token() }}',
                    coin: coin
            },
            // response callback
            coinStatusChangeCallback
        );
    }
</script>
@endsection
