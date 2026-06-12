@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'crypto_currency'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Crypto') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        $demoTrade = isset($module) && isset($module['DemoTrade']) ? true : false;
    @endphp
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <!-- <h3>{{ $title }}</h3> -->
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn-new mb-2 mr-1">
                            <a href="{{ route('adminCoinRate') }}">{{ __('Update Currency Rate') }}</a>
                        </div>
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{ route('adminAddCoin') }}">{{ __('Add New Currency') }}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table w-100">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Coin Name') }}</th>
                                    <th scope="col">{{ __('Type') }}</th>
                                    <th scope="col">{{ __('Coin Code/Type') }}</th>
                                    <th scope="col">{{ __('Active Provider') }}</th>
                                    <th>{{ __('Coin Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    @if ($demoTrade)
                                        <th>{{ __('Demo Trade') }}</th>
                                    @endif
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
        (function ($) {
            "use strict";

            $('#table').DataTable({
                serverSide: true,
                responsive: false,
                ajax: "{{ route('adminCoinList') }}",
                order: [5, 'desc'],
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [{
                    "data": "name",
                    "orderable": true
                },
                {
                    "data": "currency_type",
                    "orderable": true
                },
                {
                    "data": "coin_type",
                    "orderable": true
                },
                {
                    "data": "active_provider",
                    "orderable": true
                },
                {
                    "data": "coin_price",
                    "orderable": true,
                },
                {
                    "data": "status",
                    "orderable": false
                },
                {
                    "data": "created_at",
                    "orderable": true
                },
                    @if ($demoTrade)
                                                                                    {
                            "data": "is_demo_trade",
                            "orderable": false
                        },
                    @endif{
                    "data": "actions",
                    "orderable": false
                },
                ],
            });
        })(jQuery);

        function processForm(active_id) {
            $.ajax({
                type: "POST",
                url: "{{ route('adminCoinStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'active_id': active_id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
        @if ($demoTrade)
            function changeDemoTradeStatus($coin) {
                let url = '{{ route('demoTradeCoinStatus') }}' + $coin;
                $.get(url, (data) => {
                    if (data?.success) {
                        VanillaToasts.create({
                            text: data?.message,
                            type: 'success',
                            timeout: 40000
                        });
                        return;
                    }
                    VanillaToasts.create({
                        text: data?.message,
                        type: 'warning',
                        timeout: 40000
                    });
                });
            }
        @endif
    </script>
@endsection