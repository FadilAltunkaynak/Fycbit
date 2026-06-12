@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coin-pairs'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Future Coin Pairs')}}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="mr-2 mb-1">
                </div><br>
                <div class="card-body">
                    <div class="table-responsive">
                        <a class="btn btn-primary mb-3" href="{{ route("future.coin-pairs.edit") }}">{{ __("Add New Coin Pair") }}</a>
                        <table id="table-coin-pairs" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="all">{{ __("Code/Symbol")}}</th>
                                    <th scope="col">{{ __("Base Currency")}}</th>
                                    <th scope="col">{{ __("Trade Currency")}}</th>
                                    <th scope="col">{{ __("Bot Status")}}</th>
                                    <th scope="col">{{ __("Status")}}</th>
                                    <th scope="col">{{ __("Created At")}}</th>
                                    <th scope="col" class="all">{{ __("Action")}}</th>
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

    <div id="delete_model" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                    <a id="delete_coin_pair_a" class="btn btn-danger" href="#">{{__('Confirm')}} </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    "use strict";

    function deleteCoinPairModel(e){
        e.preventDefault();
        let url = e.target.closest('.delete-url').dataset.url;
        document.getElementById('delete_coin_pair_a').setAttribute('href', url);
        $('#delete_model').modal('show');
    };

    $('#table-coin-pairs').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        stateSave: true,
        retrieve: true,
        order: [0, 'desc'],
        language: {
            paginate: {
                next: 'Next &#8250;',
                previous: '&#8249; Previous'
            }
        },
        columns: [
            {"data": "code"},
            {"data": "base_coin_code"},
            {"data": "trade_coin_code"},
            {"data": "bot_status", "orderable": false, "searchable": false},
            {"data": "status", "orderable": false, "searchable": false},
            {"data": "created_at"},
            {"data": "action", "orderable": false, "searchable": false}
        ]
    });

    var statusEliment = null;
    function changeCoinStatus(e,uid){
        let url = '{{ route("future.coin-pairs.status.update") }}';
        statusEliment = e.target;
        $.post(
            url, {
                _token: '{{ csrf_token() }}',
                uid: uid
            },
            coinStatusChangeCallback
        );
    }

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
            statusEliment.checked = !statusEliment.checked;
            VanillaToasts.create({
                    text: response.message,
                    backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                    type: 'warning',
                    timeout: 40000
            });
        }
    }

</script>
@endsection
