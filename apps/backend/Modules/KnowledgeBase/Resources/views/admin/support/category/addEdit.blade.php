@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')

@endsection
@section('sidebar')
    @include('knowledgebase::layouts.sidebar', ['menu' => 'knowledgebase_support', 'sub_menu' => 'support-category'])
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
                        <form action="{{route('support_category_save')}}" method="post">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="unique_code" value="{{$item->unique_code}}">
                                <input type="hidden" name="id" value="{{$item->id}}">
                            @endif
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Category Name')}}</label>
                                        <input type="text" name="name" class="form-control" placeholder="{{__('Category Name')}}"
                                               @if(isset($item)) value="{{$item->name}}" @else value="{{old('name')}}" @endif>
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