@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'news','sub_menu'=>'main_categorys'])
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

    <div class="user-management">
        <form action="{{ route('newsCategoryTranslateUpdateText') }}" method="post">
            @csrf
            @if(isset($category))
                <input type="hidden" value="{{ $category->id }}" name="category_id" />
            @endif
            @if(isset($language_details))
                <input type="hidden" value="{{ $language_details->key }}" name="lang_key" />
            @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Title") }}({{$language_details->name}}) </label>
                            <input name="title" class="form-control" value="{{ isset($category_translation) ? $category_translation->title : $category->title }}" type="text" placeholder="{{ __("Category Title") }}" />
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                    @if(isset($category->id))
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    @else
                        <button class="button-primary theme-btn">{{__('Create')}}</button>
                    @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
@endsection
