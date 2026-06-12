@extends('admin.master')
@section('title', isset($title) ? $title : __('Dynamic form create for ICO'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'launchapad_page_settings'])
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
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('launchpadPageSettingsUpdate')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-lg-12 mt-20">
                                            <div class="single-uplode">
                                                <div class="uplode-catagory">
                                                    <span>{{__('Upload Cover Image')}}</span>
                                                </div>
                                                <div class="form-group buy_coin_address_input ">
                                                    <div id="file-upload" class="section-p">
                                                        <input type="file" name="image" value=""
                                                            id="file" ref="file" class="dropify"
                                                            @if(isset($launchpad_cover_image) && (!empty($launchpad_cover_image)))  data-default-file="{{asset(FILE_ICO_VIEW_PATH.$launchpad_cover_image)}}" @endif />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mt-20">
                                            <div class="single-uplode">
                                                <div class="uplode-catagory">
                                                    <span>{{__('Upload About Image')}}</span>
                                                </div>
                                                <div class="form-group buy_coin_address_input ">
                                                    <div id="file-upload" class="section-p">
                                                        <input type="file" name="main_image" value=""
                                                            id="file" ref="file" class="dropify"
                                                            @if(isset($launchpad_main_image) && (!empty($launchpad_main_image)))  data-default-file="{{asset(FILE_ICO_VIEW_PATH.$launchpad_main_image)}}" @endif />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="meta_keywords">{{__('Launchpad First Title')}} </label>
                                                <input type="text" name="launchpad_first_title" class="form-control"
                                                @if(!empty($launchpad_first_title)) value="{{$launchpad_first_title}}" @else value="{{old('launchpad_first_title')}}" @endif>
                                            </div>

                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Launchpad First Description')}}</label>
                                                <textarea class="form-control" name="launchpad_first_description" id="" rows="1">@if(!empty($launchpad_first_description)) {{$launchpad_first_description}} @else {{old('launchpad_first_description')}} @endif</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Launchpad Second Title')}}</label>
                                                <input type="text" name="launchpad_second_title" class="form-control"
                                                @if(!empty($launchpad_second_title)) value="{{$launchpad_second_title}}" @else value="{{old('launchpad_second_title')}}" @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="">{{__('Launchpad Second Description')}}</label>
                                                <textarea class="form-control" name="launchpad_second_description" id="" rows="1">@if(!empty($launchpad_second_description)) {{$launchpad_second_description}} @else {{old('launchpad_second_description')}} @endif</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Why Choose Us Title Text')}}</label>
                                                <input type="text" name="launchpad_why_choose_us_text" class="form-control"
                                                @if(!empty($launchpad_why_choose_us_text)) value="{{$launchpad_why_choose_us_text}}" @else value="{{old('launchpad_why_choose_us_text')}}" @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="google_analytics_tracking_id">{{__('Apply To Launchpad Button Text')}}</label>
                                                <input type="text" name="launchpad_apply_to_button_text" class="form-control"
                                                @if(!empty($launchpad_apply_to_button_text)) value="{{$launchpad_apply_to_button_text}}" @else value="{{old('launchpad_apply_to_button_text')}}" @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label for="">{{__('Apply To Launchpad')}} </label>
                                            <div class="cp-select-area">
                                                <select name="launchpad_apply_to_status" class="form-control">
                                                <option value="{{ENABLE}}">{{__('Active')}}</option>
                                                <option value="{{DISABLE}}">{{__('In-Active')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(!empty($launchpad_first_title)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')

@endsection
