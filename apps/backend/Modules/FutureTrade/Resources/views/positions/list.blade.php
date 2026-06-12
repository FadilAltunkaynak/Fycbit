@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'position_list'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Futures position List')}}</li>
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
                        <table id="table-positions" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="all">{{ __("ID")}}</th>
                                    <th scope="col">{{ __("Type")}}</th>
                                    <th scope="col" class="all">{{ __("Code")}}</th>
                                    <th scope="col">{{ __("User")}}</th>
                                    <th scope="col">{{ __("Price")}}</th>
                                    <th scope="col">{{ __("Amount")}}</th>
                                    <th scope="col">{{ __("Created At")}}</th>
                                    <th scope="col">{{ __("Status")}}</th>
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

    <div class="modal fade" id="positionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">{{__('Position Details')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3" id="modal_body">
                    
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    "use strict";

    $('#table-positions').DataTable({
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
            {"data": "uid",
                render: function (data, type) {
                    if (type !== 'display' || !data) return data;

                    const start = data.slice(0, 2);
                    const end   = data.slice(-3);
                    const masked = `${start}******${end}`;

                    return `<span title="${data}">${masked}</span>`;
                }
            },
            {"data": "type"},
            {"data": "code"},
            {"data": "user"},
            {"data": "price"},
            {"data": "amount"},
            {"data": "updated_at"},
            {"data": "status"},
            {"data": "action", "orderable": false, "searchable": false}
        ]
    });


    function orderGetHandler(response){
        console.log(response);
        if(response.success){
            $("#modal_body").html(response.data.html);
            $("#positionModal").modal('show');
        } else {
            VanillaToasts.create({
                    text: response.message,
                    backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                    type: 'warning',
                    timeout: 40000
            });
        }
    }

    function getPositionDetails(uid){
        let url = '{{ route("future.position.get-modal-position") }}';
        $.get( url, { uid: uid }, orderGetHandler );
    }
</script>
@endsection