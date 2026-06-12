@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'payment_method'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Payment Method')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        
        <div class="row">
            <div class="col-12">
                <div class="mr-2 mb-1">
                     <a href="{{ route('p2pPaymentMethodCreate') }}" class="btn mr-3 float-right btn-primary">{{ __("Create Payment Method") }}</a>
               </div><br>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-coin" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Method Name')}}</th>
                                    <th scope="col">{{__('Image')}}</th>
                                    <th scope="col">{{__('Bank Form')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Created at')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
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
                //ajax: 'aaa',
                //order: [1, 'desc'],
                //autoWidth: true,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                   {"data": "name"},
                   {"data": "logo"},
                   {"data": "payment_type"},
                   {"data": "status"},
                   {"data": "created_at"},
                   {"data": "action"}
                ]
            });
    })(jQuery);
</script>
@endsection
