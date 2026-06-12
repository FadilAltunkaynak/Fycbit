@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'ico_list'])
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
    <form action="{{route('updateLanguageTextToken')}}" method="post">
        @csrf
        @if(isset($token_details))
            <input type="hidden" value="{{ $token_details->id }}" name="ico_token_id" />
        @endif
        @if(isset($language_details))
            <input type="hidden" value="{{ $language_details->key }}" name="lang_key" />
        @endif
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">{{ __("Title") }}({{__('English')}}) </label>
                        <textarea class="form-control" rows="10">{{$token_details->details_rule}}</textarea>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">{{ __("Title") }}({{$language_details->name}}) </label>
                        <textarea name="details_rule" class="form-control" rows="10">@if (isset($token_translation)){{$token_translation->details_rule}}@endif</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 col-12 mt-20">
                @if(isset($token_details->id))
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