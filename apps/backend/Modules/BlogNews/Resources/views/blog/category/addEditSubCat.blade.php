@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'sub_category'])
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
        <div class="card-body">
        <form action="{{ route('SubCategorySubmit') }}" method="post">
            @csrf
            @if(isset($category->id))
                <input type="hidden" value="{{ encrypt($category->id) }}" name="id" />
            @endif
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">{{ __("Title") }}</label>
                        <input name="title" value="{{ $category->title ?? old('title') }}" class="form-control" type="text" placeholder="{{ __("Category Title") }}" />
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label class="control-label">{{ __("Main Category") }}</label>
                        <div class="cp-select-area">
                            <select name="category" class="form-control" data-style="bg-dark">
                                @foreach($categorys as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label class="control-label">{{ __("Status") }}</label>
                        <div class="cp-select-area">
                            <select name="status" class="form-control">
                                <option @if(isset($category->status) && $category->status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("ON")}}</option>
                                <option @if(isset($category->status) && $category->status == STATUS_REJECTED) selected @endif value="{{STATUS_REJECTED}}">{{__("OFF")}}</option>
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
        </form>
        </div>
    </div>

@endsection
@section('script')
  <script>
        $('select[name="category"]').selectpicker('val', '{{ $category->main_id ?? '' }}');
  </script>
@endsection
