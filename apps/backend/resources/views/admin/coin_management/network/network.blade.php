@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'network'])
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
                            <a href="{{ route('createNetwork') }}">{{ __('Add New Network') }}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Logo') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('RPC Url') }}</th>
                                    <th>{{ __('Explorer Url') }}</th>
                                    <th>{{ __('Status') }}</th>
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
                "{{ route('changeNetworkStatus') }}", {
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
                responsive: false,
                ajax: "{{ route('getNetworkList') }}",
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [{
                        "data": "logo"
                    },
                    {
                        "data": "name",
                        "searchable": true
                    },
                    {
                        "data": "base_type",
                        "searchable": true
                    },
                    {
                        "data": "rpc_url"
                    },
                    {
                        "data": "explorer_url"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "actions"
                    }
                ]
            });
        })(jQuery);
    </script>
@endsection
