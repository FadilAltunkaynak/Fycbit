@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'gift_card', 'sub_menu'=>'gift_card_header'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ $title ?? "" }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="card-body">
            <form action="{{ route('saveGetGiftHeader') }}" method="post" enctype="multipart/form-data">
                @csrf
                    <div class="col-xl-6 mb-xl-0 mb-4">
                        <div class="form-group">
                            <label>{{__('Header')}}</label>
                            <input type="text" name="gift_card_trade_page_header" class="form-control" @if(isset($setting)) value="{{ $setting['gift_card_trade_page_header'] ?? "" }}" @else value="{{old('gift_card_trade_page_header')}}" @endif>
                        </div>
                        <div class="form-group">
                            <label>{{__('Description')}}</label>
                            <input type="text" name="gift_card_trade_page_description" class="form-control" @if(isset($setting)) value="{{ $setting['gift_card_trade_page_description'] ?? "" }}" @else value="{{old('gift_card_trade_page_description')}}" @endif>
                        </div>
                    </div>
                    <div class="col-xl-6 mb-xl-0 mb-4">
                        <div class="form-group">
                            <label>{{__('Background')}}</label>
                            <input type="file" name="gift_card_trade_page_image" class="dropify" id=""
                            @if(isset($setting))
                                data-default-file="{{ isset($setting) ? asset(IMG_PATH.($setting['gift_card_trade_page_image'] ?? '')) : '' }}"
                            @endif>
                        </div>
                    </div>
                   
                    <div class="col-12">
                        <div class="form-group">
                            @if(isset($banner))
                                <input type="hidden" name="uid" value="{{$banner->uid}}">
                            @endif
                            <button type="submit" class="button-primary theme-btn">{{__('Save')}}</button>
                        </div>
                    </div>
            </form>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection