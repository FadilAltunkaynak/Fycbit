@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'landing','sub_menu'=>'advantage'])
@endsection
@section('content')
@php($settings = allsetting())
<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Advantage Of P2P Exchange')}}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <form action="{{route('setLandingHowToP2p')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage One Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_advantage_1_heading"
                                            value="{{isset($settings['p2p_advantage_1_heading'])?$settings['p2p_advantage_1_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage One Description") }}</label>
                                        <textarea class="form-control" name="p2p_advantage_1_des" rows="4">{{isset($settings['p2p_advantage_1_des'])?$settings['p2p_advantage_1_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Advantage One Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_advantage_1_icon" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_advantage_1_icon'])) data-default-file="{{p2pLandingImg('p2p_advantage_1_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Two Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_advantage_2_heading"
                                            value="{{isset($settings['p2p_advantage_2_heading'])?$settings['p2p_advantage_2_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Two Description") }}</label>
                                        <textarea class="form-control" name="p2p_advantage_2_des" rows="4">{{isset($settings['p2p_advantage_2_des'])?$settings['p2p_advantage_2_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Advantage Two Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_advantage_2_icon" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_advantage_2_icon'])) data-default-file="{{p2pLandingImg('p2p_advantage_2_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Three Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_advantage_3_heading"
                                            value="{{isset($settings['p2p_advantage_3_heading'])?$settings['p2p_advantage_3_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Three Description") }}</label>
                                        <textarea class="form-control" name="p2p_advantage_3_des" rows="4">{{isset($settings['p2p_advantage_3_des'])?$settings['p2p_advantage_3_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Advantage Three Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_advantage_3_icon" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_advantage_3_icon'])) data-default-file="{{p2pLandingImg('p2p_advantage_3_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Four Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_advantage_4_heading"
                                            value="{{isset($settings['p2p_advantage_4_heading'])?$settings['p2p_advantage_4_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Advantage Four Description") }}</label>
                                        <textarea class="form-control" name="p2p_advantage_4_des" rows="4">{{isset($settings['p2p_advantage_4_des'])?$settings['p2p_advantage_4_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Advantage Four Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_advantage_4_icon" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_advantage_4_icon'])) data-default-file="{{p2pLandingImg('p2p_advantage_4_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Advantage Right Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_advantage_right_image" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_advantage_right_image'])) data-default-file="{{p2pLandingImg('p2p_advantage_right_image')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button class="button-primary theme-btn" type="submit">
                                {{__('Save')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



@endsection
@section('script')
    <script>
        (function($) {
            $("#counterparty_condition").selectpicker('val',"{{ $setting->counterparty_condition ?? '' }}");
            })(jQuery);
    </script>

@endsection
