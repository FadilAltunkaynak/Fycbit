@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')

@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_support_settings','sub_menu'=>'knowledgebase-site-settings'])
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
    <!-- User Management -->
    @php($allsettings = knowledgebaseSupportSettings())
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('knowledgebase_site_setting_update')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="single-uplode">
                                        <div class="uplode-catagory">
                                            <span>{{__('Logo')}}</span>
                                        </div>
                                        <div class="form-group buy_coin_address_input ">
                                            <div id="file-upload" class="section-p">
                                                <input type="file" name="logo" value=""
                                                    id="file" ref="file" class="dropify"
                                                    data-default-file="{{asset(FILE_KNOWLEDGE_BASE_VIEW_PATH).'/'.$allsettings['logo']}}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="single-uplode">
                                        <div class="uplode-catagory">
                                            <span>{{__('Cover Image')}}</span>
                                        </div>
                                        <div class="form-group buy_coin_address_input ">
                                            <div id="file-upload" class="section-p">
                                                <input type="file" name="cover_image" value=""
                                                    id="file" ref="file" class="dropify"
                                                    data-default-file="{{asset(FILE_KNOWLEDGE_BASE_VIEW_PATH).'/'.$allsettings['cover_image']}}"  />
                                            </div>
                                        </div>
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