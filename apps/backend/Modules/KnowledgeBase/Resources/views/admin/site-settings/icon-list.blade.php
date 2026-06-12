@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')

@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_support_settings','sub_menu'=>'knowledgebase-icon-settings'])
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
            <div class="col-3">
                <a class="btn theme-btn float-right" href="{{route('knowledgebase_site_icon_add')}}">{{__('Add New')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="header-bar">
                    
                </div>
                <div class="table-area">
                    <div class="table-responsive">
                        <table id="table" class=" table table-borderless custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Name')}}</th>
                                <th scope="col" class="all">{{__('Class Name')}}</th>
                                <th scope="col" class="all">{{__('Unicode')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($icon_list))
                            @foreach($icon_list as $icon)
                                <tr>
                                    <td> {{$icon->title}} </td>
                                    <td>
                                        {{$icon->class_name}}
                                    </td>
                                   <td>
                                        {{$icon->unicode}}
                                   </td>
                                    <td>
                                        <ul class="d-flex activity-menu">
                                            <li class="viewuser">
                                                <a href="{{route('knowledgebase_site_icon_edit', $icon->id)}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </li>
                                            
                                            <li class="viewuser">
                                                <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($icon->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                                <div id="delete1WV4d6uF6Ytu8v1Pl_{{($icon->id)}}" class="modal fade delete" role="dialog">
                                                    <div class="modal-dialog modal-sm">
                                                        <div class="modal-content">
                                                            <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                            <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                <a class="btn btn-danger"href="{{route('knowledgebase_site_icon_delete', $icon->id)}}">{{__('Confirm')}} </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li> 
                                        </ul>
                                    </td> 
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
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
    (function($) {
        "use strict";
        $('#table').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering:  false,
            select: false,
            bDestroy: true
        });
    })(jQuery);
</script>
@endsection