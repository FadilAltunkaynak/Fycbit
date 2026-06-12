@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')
    <style>
        .icon {
            font-family: 'FontAwesome', 'sans-serif';
        }
    </style>
@endsection
@section('sidebar')
    @include('knowledgebase::layouts.sidebar', ['menu' => 'knowledgebase_settings', 'sub_menu' => 'sub-category'])
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
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('knowledgebase_subCategorySave')}}" method="post">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="unique_code" value="{{$item->unique_code}}">
                            @endif
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Sub Category Name')}}</label>
                                        <input type="text" name="name" class="form-control" placeholder="{{__('Sub Category Name')}}"
                                               @if(isset($item)) value="{{$item->name}}" @else value="{{old('name')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <label for="country">{{__('Category')}} </label>
                                    <div class="cp-select-area">
                                        <select name="category_id" class="selectpicker icon" title="{{ __('Select Category') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            @if (isset($category_list))
                                                @foreach ($category_list as $category)
                                                    <option value="{{$category->id}}" class="fa"
                                                        {{isset($item) && ($item->category_id == $category->id) ? 'selected' : '' }}> 
                                                         {{$category->name}}
                                                    </option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <label for="country">{{__('Icon')}} </label>
                                    <div class="cp-select-area">
                                        <select name="icon_class" class="selectpicker icon" title="{{ __('Select Icon') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            @foreach (fontawesomeIcon() as $icon)
                                                <option value="{{$icon->class_name}}" class="fa"
                                                    {{isset($item) && ($item->icon_class == $icon->class_name) ? 'selected' : '' }}> 
                                                    &#x{{$icon->unicode}}; {{$icon->title}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <label for="country">{{__('Status')}} </label>
                                    <div class="cp-select-area">
                                        <select name="status" class="selectpicker" title="{{ __('Select Status') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            <option value="{{STATUS_ACTIVE}}"
                                                {{isset($item) && ($item->status == STATUS_ACTIVE) ? 'selected' : '' }}>
                                                {{__('Active')}}
                                            </option>
                                            <option value="{{STATUS_INACTIVE}}"
                                                {{isset($item) && ($item->status == STATUS_INACTIVE) ? 'selected' : '' }}>
                                                {{__('Inactive')}}
                                            </option>

                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
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