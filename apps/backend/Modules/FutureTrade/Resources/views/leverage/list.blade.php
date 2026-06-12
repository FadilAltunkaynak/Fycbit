@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coin-pairs'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row align-items-center">
            <div class="col-md-8">
                <ul>
                    <li class="active-item">{{__('Future Leverage Settings')}}</li>
                </ul>
            </div>
            <div class="col-md-4 text-right">
                <a class="btn btn-outline-primary" href="{{ route('future.coin-pairs.edit', [$coinPair->uid]) }}">
                    <i class="fa fa-arrow-left mr-1"></i>{{ __('Return to Coin Pair') }}
                </a>
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
                        <a class="btn btn-primary mb-3" href="{{ route("future.leverage.edit", ["coin_pair_uid"=>$coinPair->uid]) }}">{{ __("Add New") }}</a>
                        <table id="table-coin-pairs" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="all">{{ __("Tier")}}</th>
                                    <th scope="col">{{ __("Position Bracket (Notional Value in USDT)")}}</th>
                                    <th scope="col">{{ __("Max Leverage")}}</th>
                                    <th scope="col">{{ __("Maintenance Margin Rate")}}</th>
                                    <th scope="col">{{ __("Maintenance Amount (USDT)")}}</th>
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
                    <a id="delete_leverage_a" class="btn btn-danger" href="#">{{__('Confirm')}} </a>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('script')
<script>

    "use strict";

    function deleteLeverageModel(e){
        e.preventDefault();
        let url = e.target.closest('.delete-url').dataset.link;
        document.getElementById('delete_leverage_a').setAttribute('href', url);
        $('#delete_model').modal('show');
    };

    $('#table-coin-pairs').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        responsive: true,
        stateSave: true,
        retrieve: true,
        order: [4, 'desc'],
        language: {
            paginate: {
                next: 'Next &#8250;',
                previous: '&#8249; Previous'
            }
        },
        columns: [
            {
                "data": "tier",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {"data": "position_range"},
            {"data": "max_leverage"},
            {"data": "maintenance_margin_rate"},
            {"data": "maintenance_amount"},
            {"data": "action", "orderable": false, "searchable": false}
        ]
    });

</script>
@endsection
