@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'landing', 'sub_menu' => 'how_to'])
@endsection
@section('content')
@php($settings = allsetting())
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('How To Work Settings')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3 col-xl-2">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='buy') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#buy" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('How To Buy') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='sell') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#sell" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('How To Sell') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='buy')  active @endif" id="buy"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::landing.how_to.buy',$settings)
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='sell')  active @endif" id="sell"
                        role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::landing.how_to.sell',$settings)
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->


@endsection
@section('script')
@endsection
