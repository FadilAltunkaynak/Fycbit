@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'settings'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('Blog Settings')}}</li>
                </ul>
            </div>
            <div class="col-md-6 ">
                <a href="{{route('BlogSettingsTranslate')}}" class="add-btn theme-btn float-right">
                    {{__('Update Language')}}
                </a>
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
                            <a class="@if(isset($tab) && $tab=='feature') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="capcha" href="#feature" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Blog Feature') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='search') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="capcha" href="#search" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Blog Search') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='comment') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="capcha" href="#comment" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Blog Comment') }}</span>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="@if(isset($tab) && $tab=='footer') active @endif nav-link " id="pills-user-tab"
                               data-toggle="pill" data-controls="capcha" href="#footer" role="tab"
                               aria-controls="pills-user" aria-selected="true">
                                <span>{{ __('Footer Setting') }}</span>
                            </a>
                        </li> --}}
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='feature')  active @endif" id="feature"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('blognews::blog.settings.homeSettings')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='search')  active @endif" id="search"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('blognews::blog.settings.searchSettings')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='footer')  active @endif" id="footer"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('blognews::blog.settings.footerSettings')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='comment')  active @endif" id="comment"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('blognews::blog.settings.commentSettings')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
            $("#blog_comment_enable").selectpicker('val',"{{ $setting->blog_comment_enable ?? '' }}");    
            $("#blog_auto_comment_approval").selectpicker('val',"{{ $setting->blog_auto_comment_approval ?? '' }}");    
    </script>
@endsection
