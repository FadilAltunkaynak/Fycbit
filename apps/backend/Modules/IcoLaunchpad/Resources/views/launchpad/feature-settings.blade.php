@extends('admin.master')
@section('title', isset($title) ? $title : __('Launchpad Feature Settings'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'launchapad_feature_list'])
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

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('launchpadFeatureSettingsSave')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            @if (isset($item))
                               <input type="hidden" name="id" value="{{$item->id}}" />

                            @endif
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row">
                                        <div class="col-lg-12 mt-20">
                                            <div class="single-uplode">
                                                <div class="uplode-catagory">
                                                    <span>{{__('Upload Cover Image')}}</span>
                                                </div>
                                                <div class="form-group buy_coin_address_input ">
                                                    <div id="file-upload" class="section-p">
                                                        <input type="file" name="image" value=""
                                                            id="file" ref="file" class="dropify"
                                                            @if(isset($item))  data-default-file="{{asset(path_image().$item->image)}}" @endif />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-12 mt-20">
                                            <div class="form-group">
                                                <label for="meta_keywords">{{__('Title')}} </label>
                                                <input type="text" name="title" class="form-control"
                                                @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                                <div class="form-group">
                                                    <label for="meta_keywords">{{__('Description')}} </label>
                                                    <textarea name="description" class="form-control" rows="3">{{isset($item)? $item->description: old('description')}}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>{{__('Page Link Type')}}</label>
                                                <select name="page_type" id="custom_page_link_type" class="form-control-new">
                                                    <option value="">{{__('Select')}}</option>
                                                    @foreach(custom_page_link_type() as $key => $val)
                                                        <option value="{{$key}}" @if(isset($item) && $item->page_type == $key) selected @endif>{{$val}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group @if(isset($item) && ($item->page_type == CUSTOM_PAGE_LINK_URL)) d-block @else d-none @endif" id="pageLink" >
                                                <label>{{__('Page Link')}}</label>
                                                <input id="page_link" class="form-control-new" type="text" name="page_link" placeholder="{{__('Add here page full url')}}"
                                                    @if(isset($item))value="{{$item->page_link}}" @else value="{{old('page_link')}}" @endif>
                                            </div>
                                            <div class="form-group @if(isset($item) && ($item->page_type == CUSTOM_PAGE_LINK_PAGE)) d-block @else d-none @endif" id="descriptionLink">
                                                <label for="">{{__('Custom Page Description')}}</label>
                                                <input type="hidden" id="body" name="body" value="" />
                                                <textarea rows="10" name="custom_page_description" id="editor" class="form-control-new textarea note-editable" >@if(isset($item)){!! ($item->custom_page_description) !!} @else {{old('description')}} @endif</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="button-primary theme-btn">@if(!empty($launchpad_first_title)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                        </div>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
    (function($) {
            "use strict";
            var $summernote = $('#editor');
            var isCodeView;

            $(() => {
                $summernote.summernote({
                    height: 500,
                    focus: true,
                    codeviewFilter: false,
                    codeviewFilterRegex: /<\/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|ilayer|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|t(?:itle|extarea)|xml)[^>]*?>/gi,
                });
            });

            $summernote.on('summernote.codeview.toggled', () => {
                isCodeView = $('.note-editor').hasClass('codeview');
            });

            $("#edit").submit( (event) => {
                var body = $summernote.summernote('code');
                document.getElementById('body').setAttribute('value', body);

            });

            $('#custom_page_link_type').on('change', function () {
                let a = $(this).val();
                if (a == 1){
                    document.getElementById('descriptionLink').classList.remove("d-none");
                    document.getElementById('descriptionLink').classList.add("d-block");
                    document.getElementById('pageLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                } else if(a == 2) {
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('descriptionLink').classList.add("d-none");
                    document.getElementById('pageLink').classList.remove("d-none");
                    document.getElementById('pageLink').classList.add("d-block");
                } else {
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                    document.getElementById('descriptionLink').classList.remove("d-block");
                    document.getElementById('pageLink').classList.add("d-none");
                }
            })
        })(jQuery);
</script>
@endsection
