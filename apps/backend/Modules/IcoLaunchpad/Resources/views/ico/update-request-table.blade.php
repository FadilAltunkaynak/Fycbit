@extends('admin.master')
@section('title', isset($title) ? $title : __('ICO List'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'ico_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-5">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php($user = auth()->user())
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Type')}}</th>
                                    <th scope="col">{{__('Current Value')}}</th>
                                    <th scope="col">{{__('Requested Value')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($update_info_list))
                                    @foreach($update_info_list as $value)
                                        <tr>
                                            <td> {{$value->column_name}} </td>
                                            <td> {{$value->previous_value}}</td>
                                            <td> {{$value->requested_value}}</td>
                                            <td>
                                                <ul class="d-flex activity-menu">
                                                    <li class="viewuser">
                                                        <a href="{{route('updateRequestTableInfoAccept', encrypt($value->id))}}" 
                                                            title="{{__("Accept")}}" class="btn btn-primary btn-sm">
                                                            {{__('Accept')}}
                                                        </a>
                                                    </li>
                                                    <li class="viewuser">
                                                        <a href="{{route('updateRequestTableInfoReject', encrypt($value->id))}}" 
                                                            title="{{__("Denied")}}" class="btn btn-danger btn-sm">
                                                            {{__('Denied')}}
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
  (function($) {
            "use strict";
            $('#table').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering:  true,
                select: false,
                bDestroy: true
            });
        })(jQuery);
        
        function processForm(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('icoStatusChange') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
</script>
@endsection