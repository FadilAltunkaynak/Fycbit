@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'landing', 'sub_menu' => 'header_setting'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('PeerToPeer Page Header')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
   <div class="user-management pt-4">
        <div class="form-area plr-65 profile-info-form">
            <form enctype="multipart/form-data" method="POST" action="{{route('setLandingSettingsP2p')}}">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Landing Page Title')}}</label>
                                    <input class="form-control" type="text" name="p2p_banner_header" @if(isset($adm_setting['p2p_banner_header'])) value="{{$adm_setting['p2p_banner_header']}}" @endif>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="#">{{__('Landing Page Description')}}</label>
                                    <textarea class="form-control" rows="5" name="p2p_banner_des">@if(isset($adm_setting['p2p_banner_des'])){{$adm_setting['p2p_banner_des']}} @endif</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="#">{{__('Landing Banner Image')}}</label>
                                            <div id="file-upload" class="section-width">
                                                <input type="file" name="p2p_banner_img"  id="file" ref="file"
                                                    class="dropify" @if(isset($adm_setting['p2p_banner_img'])) data-default-file="{{asset(P2P_LANDING_PATH.$adm_setting['p2p_banner_img'])}}"@endif />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button class="button-primary theme-btn">{{__('Update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
