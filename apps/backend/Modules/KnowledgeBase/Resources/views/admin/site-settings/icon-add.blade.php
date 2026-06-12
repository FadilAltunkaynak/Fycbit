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
            <div class="col-12">
                <h3 class="text-warning">{{__('Icon class name and unicode must be collected from Font Awesome 4 icon list. To go Font Awesome Icon list ')}} <a target="_blank" href="https://fontawesome.com/v4/icons/">{{__('Click Here')}}</a></h3>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('knowledgebase_site_icon_update')}}" method="post">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="id" value="{{$item->id}}">
                            @endif
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Name')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{__('Icon Name')}}"
                                               @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Class Name')}}</label>
                                        <input type="text" name="class_name" class="form-control" placeholder="{{__('Ex: fa-address-book ')}}"
                                               @if(isset($item)) value="{{$item->class_name}}" @else value="{{old('class_name')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Unicode')}}</label>
                                        <input type="text" name="unicode" class="form-control" placeholder="{{__('Ex: f2b9')}}"
                                               @if(isset($item)) value="{{$item->unicode}}" @else value="{{old('unicode')}}" @endif>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
@endsection