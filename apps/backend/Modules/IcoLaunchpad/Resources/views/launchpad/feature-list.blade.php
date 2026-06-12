@extends('admin.master')
@section('title', isset($title) ? $title : __('ICO List'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'launchapad_feature_list'])
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
            <div class="col-sm-7 text-right">
                <a class="add-btn theme-btn" href="{{route('launchpadFeatureSettings')}}">{{__('Add New')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="header-bar p-4">
                    <div class="table-title">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Title')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($feature_list))
                                    @foreach($feature_list as $value)
                                        <tr>
                                            <td> {{$value->title}} </td>

                                            <td>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="processForm('{{$value->id}}')"
                                                           id="notification" name="status" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <ul class="d-flex activity-menu">
                                                    <li class="viewuser">
                                                        <a href="{{route('launchpadFeatureSettingsEdit', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    </li>


                                                    <li class="viewuser">
                                                        <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($value->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                        <div id="delete1WV4d6uF6Ytu8v1Pl_{{($value->id)}}" class="modal fade delete" role="dialog">
                                                            <div class="modal-dialog modal-sm">
                                                                <div class="modal-content">
                                                                    <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                                    <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                                    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                        <a class="btn btn-danger"href="{{route('launchpadFeatureSettingsDelete', encrypt($value->id))}}">{{__('Confirm')}} </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                url: "{{ route('launchpadFeatureStatus') }}",
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
