@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'news','sub_menu'=>'main_category'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Add Category')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        <form action="{{ route('newsCategorySubmit') }}" method="post">
            @csrf
            @if(isset($category->id))
                <input type="hidden" value="{{ $category->id }}" name="id" />
            @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ __("Title") }}</label>
                            <input name="title" class="form-control" value="{{ isset($category->title) ? $category->title : old('title') }}" type="text" placeholder="{{ __("Category Title") }}" />
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label class="col-md-2 control-label">{{ __("Status") }}</label>
                            <div class="cp-select-area">
                                <select name="status" class="form-control">
                                    <option @if(isset($category['status']) && $category->status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("ON")}}</option>
                                    <option @if(isset($category['status']) && $category->status == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("OFF")}}</option>
                                </select>
                            </div>
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
