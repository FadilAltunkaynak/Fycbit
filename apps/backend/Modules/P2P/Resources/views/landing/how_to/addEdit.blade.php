@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'landing', 'sub_menu' => 'how_to'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('Setting')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
   <div class="user-management pt-4">
        <div class="form-area plr-65 profile-info-form">
            <form enctype="multipart/form-data" method="POST" action="{{route('landingHowToSetP2p')}}">
                @csrf
                <input type="hidden" name="type" value="{{$type}}" />
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Landing Page Title')}}</label>
                                    <input class="form-control" type="text" name="header" @if(isset($dat->header)) value="{{$dat->header}}" @endif>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Landing Page Description')}}</label>
                                    <textarea class="form-control" rows="5" name="description">@if(isset($dat->description)){{$dat->description}} @endif</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="#">{{__('Landing Banner Image')}}</label>
                                    <div id="file-upload" class="section-width">
                                        <input type="file" name="image"  id="file" ref="file"
                                            class="dropify" @if(isset($dat->image)) data-default-file="{{asset(LANDING_PAGE_HOW_PATH.$dat->image)}}"@endif />
                                    </div>
                                </div>
                            </div>
                        
                            <div class="col-md-12">
                                <button class="button-primary theme-btn">{{__('Update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')

@endsection
