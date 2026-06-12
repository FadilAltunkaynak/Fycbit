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
                        <form id="edit" action="{{route('knowledgebase_articleSectionSave')}}" method="post" enctype="multipart/form-data">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="unique_code" value="{{$item->unique_code}}">
                            @endif
                            <div class="row">
                                <input type="hidden" name="article_id" value="{{$article->id}}">
                                <input type="hidden" name="article_unique_code" value="{{$article->unique_code}}">
                                <div class="col-md-12 mt-20">
                                    <div class="form-group">
                                        <label for="account_holder_name">{{__('Title')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{__('Section Title')}}"
                                               @if(isset($item)) value="{{$item->title}}" @else value="{{old('title')}}" @endif>
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
                                <div class="col-md-6 mt-20">
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