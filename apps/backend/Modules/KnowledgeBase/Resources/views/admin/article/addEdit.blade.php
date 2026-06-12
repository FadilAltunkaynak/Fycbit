@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')
    <style>
        .icon {
            font-family: 'FontAwesome', 'sans-serif';
        }
    </style>
@endsection
@section('sidebar')
    @include('knowledgebase::layouts.sidebar', ['menu' => 'knowledgebase_settings', 'sub_menu' => 'article'])
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
    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form id="edit" action="{{route('knowledgebase_articleSave')}}" method="post" enctype="multipart/form-data">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="unique_code" value="{{$item->unique_code}}">
                            @endif
                            <div class="row">
                                <div class="col-md-4 mt-20">
                                    <label for="country">{{__('Category')}} </label>
                                    <div class="cp-select-area">
                                        <select id="category_id" name="category_id" class="selectpicker icon" title="{{ __('Select Category') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            @if (isset($category_list))
                                                @foreach ($category_list as $category)
                                                    <option value="{{$category->id}}" class="fa"
                                                        {{isset($item) && ($item->category_id == $category->id) ? 'selected' : '' }}> 
                                                         {{$category->name}}
                                                    </option>
                                                @endforeach
                                            @endif

                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 mt-20">
                                    <label for="country">{{__('Sub Category')}} </label>
                                    <div class="cp-select-area" >
                                        <select id="sub_category_id" name="sub_category_id" class="form-control" title="{{ __('Select Sub Category') }}" >
                                            <option value="" > {{__('Choose Sub Category')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-20">
                                    <label for="country">{{__('Icon')}} </label>
                                    <div class="cp-select-area">
                                        <select name="icon_class" class="selectpicker icon" title="{{ __('Select Icon') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            @foreach (fontawesomeIcon() as $icon)
                                                <option value="{{$icon->class_name}}" class="fa"
                                                    {{isset($item) && ($item->icon_class == $icon->class_name) ? 'selected' : '' }}> 
                                                    &#x{{$icon->unicode}}; {{$icon->title}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Article Title')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{__('Article Title')}}"
                                               @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
                                    </div>
                                </div>


                                <div class="col-md-6 mt-20">
                                    <div class="single-uplode">
                                        <div class="uplode-catagory">
                                            <span>{{__('Feature Image')}}</span>
                                        </div>
                                        <div class="form-group buy_coin_address_input ">
                                            <div id="file-upload" class="section-p">
                                                <input type="file" name="image" value=""
                                                    id="file" ref="file" class="dropify"
                                                    @if(isset($item))  data-default-file="{{asset(FILE_KNOWLEDGE_BASE_VIEW_PATH . $item->feature_image)}}" @endif />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <label for="country">{{__('Status')}} </label>
                                    <div class="cp-select-area">
                                        <select name="status" class="selectpicker" title="{{ __('Select Status') }}" data-live-search="true" data-width="100%"
                                            data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">

                                            <option value="{{STATUS_ACTIVE}}"
                                                {{isset($item) && ($item->status == STATUS_ACTIVE) ? 'selected' : '' }}>
                                                {{__('Active')}}
                                            </option>
                                            <option value="{{STATUS_INACTIVE}}"
                                                {{isset($item) && ($item->status == STATUS_INACTIVE)? 'selected':'' }}>
                                                {{__('Inactive')}}
                                            </option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="">{{__('Description')}}</label>
                                        <input type="hidden" id="body" name="body" value="" />
                                        <textarea rows="6" name="" id="editor" class="form-control-new textarea note-editable" >@if(isset($item)){!! ($item->description) !!} @else {{old('description')}} @endif</textarea>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    <script>
        $( document ).ready(function() {
            var category_id = $('#category_id').val();
            var sub_category_id = '{{isset($item) ? $item->sub_category_id : ""}}';

            getOptionByCategory(category_id,sub_category_id);
        });
            $('#category_id').change(function(){
                var category_id = $(this).val();
                var sub_category_id = '{{isset($item) ? $item->sub_category_id : ""}}'
                getOptionByCategory(category_id,sub_category_id);
            });
            function getOptionByCategory(category_id,sub_category_id)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ route('knowledgebase_setSubCategory') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'category_id': category_id,
                        'sub_category_id':sub_category_id
                    },
                    success: function (data) {
                        if(data.success == true)
                        {
                            $('#sub_category_id').empty().append(data.data);
                        }
                    }
                });
            }
            //text editor
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
            })(jQuery);
    </script>
@endsection