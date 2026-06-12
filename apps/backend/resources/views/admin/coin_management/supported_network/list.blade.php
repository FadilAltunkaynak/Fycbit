@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'supported_network'])
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
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="table-title">
                        <!-- <h3></h3> -->
                    </div>
                    <div class="right d-flex align-items-center">
                        <div class="add-btn">
                            <a href="{{ route('creteSupportedNetworkPage') }}">{{ __('+ New Supported Network') }}</a>
                        </div>
                    </div>
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Gas Limit') }}</th>
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
                "{{ route('supportedNetworkStatus') }}", {
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
                ajax: "{{ route('supportedNetworkList') }}",
                columns: [{
                        data: "name",
                    },
                    {
                        data: "gas_limit"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: "actions"
                    }
                ],
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
            });
        })(jQuery);
    </script>
@endsection
