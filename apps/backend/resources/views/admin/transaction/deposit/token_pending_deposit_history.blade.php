@extends('admin.master', ['menu' => 'deposit', 'sub_menu' => 'pending_token_deposit'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li>{{ __('Token Deposit') }}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    <div class="w-100">
                        <div class="p-3 custom-box-shadow">
                            <h5 style="color: #cbcfd7">
                                {{ __('Click the accept icon next to any record to transfer the deposited coin / token from user wallet to System wallet.') }}
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-area">
                        <div class="table-responsive">
                            <table id="table" class="table w-100">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Coin') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('From Address') }}</th>
                                        <th>{{ __('To Address') }}</th>
                                        <th>{{ __('Tx Hash') }}</th>
                                        <th>{{ __('Actions') }}</th>
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
    <!-- /User Management -->

    @if ($buy_token)
        <!-- ICO Token buy history -->
        <div class="user-management">
            <div class="row">
                <div class="col-12">
                    <div class="header-bar p-4">
                        <div class="table-title">
                            <h3>{{ __('Transaction History of Token Buy') }}</h3>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-area">
                            <div>
                                <div class="p-3 custom-box-shadow">
                                    <h5 style="color: #cbcfd7">
                                        {{ __('When user deposit ERC20/BEP20/TRC20 token , then we should keep it to admin address, because when user make withdrawal then the token will sent from admin address') }}
                                    </h5>
                                </div>
                                <div class="p-3 custom-box-shadow mt-4">
                                    <h5 class="text-danger">{{ __('Step 1: ') }}:
                                        {{ __('First send estimate gas fees to user address') }}
                                    </h5>
                                    <h5 class="text-danger">{{ __('Step 2: ') }}:
                                        {{ __('Then send token from user address to admin address') }}
                                    </h5>
                                </div>
                                <div class="p-3 custom-box-shadow mt-4">

                                    <h5 class="text-success">
                                        {{ __('You just accept the record from action nothing else, we will handle everything in background') }}
                                    </h5>
                                </div>
                                <div class="p-3 custom-box-shadow mt-4">

                                    <h5 class="text-warning">
                                        {{ __('Note: if you ignore this manual approval process, you can use a command, that will automatically handle this. The command is "adjust-token-deposit" , you can run it always in background ') }}
                                    </h5>
                                </div>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-area">
                                <div>
                                    <table id="table" class="table table-borderless custom-table display text-center"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Amount') }}</th>
                                                <th scope="col">{{ __('Coin') }}</th>
                                                <th scope="col">{{ __('Network') }}</th>
                                                <th scope="col">{{ __('From Address') }}</th>
                                                <th scope="col">{{ __('To Address') }}</th>
                                                <th scope="col">{{ __('Tx Hash') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Created At') }}</th>
                                                <th class="all" scope="col">{{ __('Actions') }}</th>
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
        <!-- /ICO Token buy history -->
    @endif

    <div class="modal fade" id="detailsModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Failure Reason</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger action-btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('admin.common.accept_html')
@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";

            $('#table').DataTable({
                serverSide: true,
                responsive: false,
                stateSave: true,
                ajax: "{{ route('adminPendingDepositHistory') }}",
                order: [6, 'desc'],
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [{
                    "data": "created_at",
                    "orderable": false,
                    render: function (data, type, row, meta) {
                        return `<p style="white-space: nowrap">${data}</span>`
                    }
                },
                {
                    "data": "status",
                    "orderable": false
                },
                {
                    "data": "coin_type",
                    "orderable": true
                },
                {
                    "data": "amount",
                    "orderable": true
                },
                {
                    "data": "from_address",
                    "orderable": true
                },
                {
                    "data": "address",
                    "orderable": true
                },
                {
                    "data": "transaction_id",
                    "orderable": false
                },
                {
                    data: "actions",
                    name: 'actions',
                    orderable: false,
                }],
            });

            $('#ico-buy-table').DataTable({
                serverSide: true,
                responsive: false,
                stateSave: true,
                ajax: "{{ route('icoTokenBuyListAccept') }}",
                order: [6, 'desc'],
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [{
                    "data": "amount",
                    "orderable": true
                },
                {
                    "data": "coin_type",
                    "orderable": true
                },
                {
                    "data": "from_address",
                    "orderable": true
                },
                {
                    "data": "address",
                    "orderable": true
                },
                {
                    "data": "transaction_id",
                    "orderable": false
                },
                {
                    "data": "status",
                    "orderable": false
                },
                {
                    "data": "created_at",
                    "orderable": true
                },
                {
                    "data": "actions",
                    "orderable": false
                },
                ],
            });

        })(jQuery);

        function acceptRequest(route) {
            $('#accept_request_modal .action-btn').attr('href', route);
            $('#accept_request_modal').modal('show');
        }

        function detailsRequest(element) {
            const text = element.getAttribute('data-reject-note');
            const html = `<p>${text}<p>`;
            $('#detailsContent').html(html);
            $('#detailsModal').modal('show');
        }
    </script>
@endsection