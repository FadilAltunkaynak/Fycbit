@extends('admin.master')
@section('title', isset($title) ? $title :  __('Setup Dynamic Form'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'setup_dynamic_form'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-8">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        $dynamic_form_for_ico_title = settings('dynamic_form_for_ico_title');
        $dynamic_form_for_ico_description = settings('dynamic_form_for_ico_description');
    @endphp
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('setupDynamicFormSave')}}" method="post" >
                            @csrf

                            <div class="row">
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="form_id">{{__('Title')}}</label>
                                        <input type="text" name="dynamic_form_for_ico_title" class="form-control" id="title" placeholder="{{__('Title')}}"
                                               @if(isset($dynamic_form_for_ico_title)) value="{{$dynamic_form_for_ico_title}}" @else value="{{old('dynamic_form_for_ico_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="form_id">{{__('Description')}}</label>
                                        <textarea name="dynamic_form_for_ico_description" class="form-control" rows="3">{{isset($dynamic_form_for_ico_description)?$dynamic_form_for_ico_description:old('dynamic_form_for_ico_description')}}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <button class="button-primary theme-btn">@if(isset($dynamic_form_for_ico_title)) {{__('Update')}} @else {{__('Save')}} @endif</button>
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
