@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'settings'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        if(isset($language_details))
        {
            $lang_key = $language_details->key;   
        }else {
            $lang_key = 'en';
        }
    @endphp
    
    <div class="user-management">
        <form action="{{ route('BlogSettingsTranslateUpdateText') }}" method="post">
            @csrf
            
            
                <input type="hidden" value="{{ $language_details->key }}" name="lang_key" />
            
            <div class="card-body">
                <div class="row">
                    @if (isset($setting->blog_feature_heading))
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Feature Heading") }} ({{__('English')}}) </label>
                                <input name="blog_feature_heading[en]" class="form-control" value="{{$setting->blog_feature_heading}}" type="text" readonly />
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="control-label">{{ __("Feature Heading") }} ({{$language_details->name}}) </label>
                                <input name="blog_feature_heading[{{$lang_key}}]" class="form-control" value="{{$setting_translation_text->blog_feature_heading?? $setting->blog_feature_heading}}" type="text" placeholder="{{ __("Feature Heading") }}" />
                            </div>
                        </div>
                    @endif

                    @if (isset($setting->blog_feature_heading))
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class=" control-label" for="blog_feature_description">{{__('Feature Description')}} ({{__('English')}})</label>
                                <textarea id="blog_feature_description" name="blog_feature_description[en]" class="form-control" readonly>{{ $setting->blog_feature_description ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class=" control-label" for="blog_feature_description">{{__('Feature Description')}} ({{$language_details->name}})</label>
                                <textarea id="blog_feature_description" name="blog_feature_description[{{$lang_key}}]" 
                                class="form-control" placeholder="{{ __('Feature Description') }}">{{ $setting_translation_text->blog_feature_description ?? $setting->blog_feature_description }}</textarea>
                            </div>
                        </div>
                    @endif
                    
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
@endsection
