@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'news','sub_menu'=>'setting'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('News Settings')}}</li>
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
                        <a class="@if(isset($tab) && $tab=='comment') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#comment" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('Comment Setting') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='comment')  active @endif" id="comment"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('blognews::news.settings.commentSettings')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
        $("#news_comment_enable").selectpicker('val',"{{ $setting->news_comment_enable ?? '' }}");    
        $("#news_auto_comment_approval").selectpicker('val',"{{ $setting->news_auto_comment_approval ?? '' }}");    
    </script>
@endsection
