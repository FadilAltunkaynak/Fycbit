@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'setting'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('Setting')}}</li>
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
                        <a class="@if(isset($tab) && $tab=='condition') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#condition" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('Counterparty Condition') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='verification') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#verification" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('KYC Verification') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='condition')  active @endif" id="condition"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::setting.condition')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='verification')  active @endif" id="verification"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::setting.verification')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
        (function($) {
            $("#counterparty_condition").selectpicker('val',"{{ $setting->counterparty_condition ?? '' }}");
        })(jQuery);
    </script>
@endsection
