@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'coin_network'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Network') }}</li>
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
                        <div class="add-btn-new mb-2 ml-2">
                            <a href="{{ route('createCoinNetwork') }}">{{ __('Add New Coin Network') }}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Logo') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Network') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Updated At') }}</th>
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
        function changeNetworkStatus(id) {
            $.post(
                "{{ route('changeCoinNetworkStatus') }}", {
                    id: id,
                    _token: "{{ csrf_token() }}",
                },
                function(response) {
                    if (response.success) {
                        VanillaToasts.create({
                            text: response.message,
                            type: 'success',
                            timeout: 40000
                        });
                    } else {
                        VanillaToasts.create({
                            text: response.message,
                            type: 'warning',
                            timeout: 40000
                        });
                    }
                }
            )
        }

        (function($) {
            "use strict";
            $('#table').DataTable({
                serverSide: true,
                paging: true,
                ordering:  true,
                select: false,
                bDestroy: true,
                columns: [
                    {"data": "logo"},
                    {"data": "coin_type", "searchable": true},
                    {"data": "type"},
                    {"data": "network_name", "searchable": true},
                    {"data": "status"},
                    {"data": "created_at"},
                    {"data": "updated_at"},
                    {"data": "actions"}
                ]
            });
        })(jQuery);
    </script>
@endsection
