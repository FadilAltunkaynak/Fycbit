@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')

@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_support_settings','sub_menu'=>'knowledgebase-site-text-settings'])
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
                <a class="btn theme-btn float-right" href="{{route('knowledgebase_site_text_reset')}}">{{__('Reset Text')}}</a>
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
                        <form action="{{route('knowledgebase_site_text_setting_update')}}" method="post">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{$allsettings['knowledgebase']}}</label>
                                        <input type="text" name="knowledgebase" class="form-control" placeholder="{{$allsettings['knowledgebase']}}"
                                               @if(isset($allsettings['knowledgebase'])) value="{{$allsettings['knowledgebase']}}" @else value="{{old('knowledgebase')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{$allsettings['support']}}</label>
                                        <input type="text" name="support" class="form-control" placeholder="{{$allsettings['support']}}"
                                               @if(isset($allsettings['support'])) value="{{$allsettings['support']}}" @else value="{{old('support')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Knowledgebase Page Cover First Title')}}</label>
                                        <input type="text" name="knowledgebase_page_cover_first_title" class="form-control" placeholder="{{$allsettings['knowledgebase_page_cover_first_title']}}"
                                               @if(isset($allsettings['knowledgebase_page_cover_first_title'])) value="{{$allsettings['knowledgebase_page_cover_first_title']}}" @else value="{{old('knowledgebase_page_cover_first_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Knowledgebase Page Cover Second Title')}}</label>
                                        <input type="text" name="knowledgebase_page_cover_second_title" class="form-control" placeholder="{{$allsettings['knowledgebase_page_cover_second_title']}}"
                                               @if(isset($allsettings['knowledgebase_page_cover_second_title'])) value="{{$allsettings['knowledgebase_page_cover_second_title']}}" @else value="{{old('knowledgebase_page_cover_second_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Support Page Cover First Title')}}</label>
                                        <input type="text" name="support_page_cover_first_title" class="form-control" placeholder="{{$allsettings['support_page_cover_first_title']}}"
                                               @if(isset($allsettings['support_page_cover_first_title'])) value="{{$allsettings['support_page_cover_first_title']}}" @else value="{{old('support_page_cover_first_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Support Page Cover Second Title')}}</label>
                                        <input type="text" name="support_page_cover_second_title" class="form-control" placeholder="{{$allsettings['support_page_cover_second_title']}}"
                                               @if(isset($allsettings['support_page_cover_second_title'])) value="{{$allsettings['support_page_cover_second_title']}}" @else value="{{old('support_page_cover_second_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Create Ticket Button Text')}}</label>
                                        <input type="text" name="create_ticket_button_text" class="form-control" placeholder="{{$allsettings['create_ticket_button_text']}}"
                                               @if(isset($allsettings['create_ticket_button_text'])) value="{{$allsettings['create_ticket_button_text']}}" @else value="{{old('create_ticket_button_text')}}" @endif>
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