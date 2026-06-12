@extends('admin.master',['menu'=>'ico', 'sub_menu'=>'ico_list'])
@section('title', isset($title) ? $title : __('List of ICO'))
@section('style')
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
    
@endsection
@section('script')
    
@endsection
