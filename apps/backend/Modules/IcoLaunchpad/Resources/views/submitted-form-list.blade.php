@extends('admin.master')
@section('title', isset($title) ? $title : __('Submitted form for ICO'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'submitted_form_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">

                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('User Name')}}</th>
                                    <th scope="col">{{__('User Email')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($submitted_form_list))
                                    @foreach($submitted_form_list as $value)
                                        <tr>
                                            <td> {{ isset($value->user)?$value->user->first_name. ' '. $value->user->last_name:__('Not Found')}} </td>
                                            <td> {{isset($value->user)?$value->user->email:__('Not Found')}} </td>
                                            <td>
                                                @if ($value->status == STATUS_ACCEPTED)
                                                    <span class="badge badge-success">
                                                        {{deposit_status_text(STATUS_ACCEPTED)}}
                                                    </span>
                                                @elseif($value->status == STATUS_PENDING)
                                                    <span class="badge badge-warning">
                                                        {{deposit_status_text(STATUS_PENDING)}}
                                                    </span>
                                                @else 
                                                    <span class="badge badge-danger">
                                                        {{deposit_status_text(STATUS_REJECTED)}}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <ul class=" d-flex activity-menu">
                                                    <li>
                                                        <a class="user-two btn btn-info btn-sm"
                                                         title="{{__('view')}}" href="{{ route("submitted-form-details",["form_id" => encrypt($value->id)]) }}">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">{{__('No data found')}}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>

    $('#table').DataTable({
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering:  true,
            select: false,
            bDestroy: true,
            order: [0, 'asc'],
            responsive: true,
            autoWidth: false,
            language: {
                "decimal":        "",
                "emptyTable":     "{{__('No data available in table')}}",
                "info":           "{{__('Showing')}} _START_ to _END_ of _TOTAL_ {{__('entries')}}",
                "infoEmpty":      "{{__('Showing')}} 0 to 0 of 0 {{__('entries')}}",
                "infoFiltered":   "({{__('filtered from')}} _MAX_ {{__('total entries')}})",
                "infoPostFix":    "",
                "thousands":      ",",
                "lengthMenu":     "{{__('Show')}} _MENU_ {{__('entries')}}",
                "loadingRecords": "{{__('Loading...')}}",
                "processing":     "",
                "search":         "{{__('Search')}}:",
                "zeroRecords":    "{{__('No matching records found')}}",
                "paginate": {
                    "first":      "{{__('First')}}",
                    "last":       "{{__('Last')}}",
                    "next":       '{{__('Next')}} &#8250;',
                    "previous":   '&#8249; {{__('Previous')}}'
                },
                "aria": {
                    "sortAscending":  ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
        });
</script>
@endsection
