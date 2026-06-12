@extends('admin.master')
@section('title', isset($title) ? $title : __('Launchpad Feature Settings'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'launchapad_feature_list'])
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
                        <form action="{{route('icoSettingsSave')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label>{{__('ICO token buy request')}}</label>
                                                <div class="cp-select-area">
                                                    <select name="icoTokenBuy_admin_approved" class="form-control">
                                                        <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Auto Accept")}}</option>
                                                        <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Need Admin Approved")}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="button-primary theme-btn">{{__('Save')}}</button>
                                        </div>
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
<script>
    
</script>
@endsection
