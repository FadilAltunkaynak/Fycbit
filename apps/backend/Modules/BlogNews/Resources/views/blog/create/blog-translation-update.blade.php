@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'blog'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        $title = null;
        $body = null;
        if (isset($blog_post_details)) {
            $title = $blog_post_details->title;
            $body = $blog_post_details->body;
        }
        if (isset($blog_post_translation)) {
            $title = $blog_post_translation->title;
            $body = $blog_post_translation->body;
        }
    @endphp
    <div class="user-management">
        <form action="{{ route('blogTranslateUpdateText') }}" method="post">
            @csrf
            @if(isset($blog_post_details))
                <input type="hidden" value="{{ $blog_post_details->id }}" name="blog_post_id" />
            @endif
            @if(isset($language_details))
                <input type="hidden" value="{{ $language_details->key }}" name="lang_key" />
            @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group">
                            <label class="control-label">{{ __("Title") }} ({{$language_details->name}}) </label>
                            <input name="title" class="form-control" value="{{ $title }}" type="text" placeholder="{{ __("Blog Post Title") }}" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="account_holder_name">{{__('Body')}} ({{$language_details->name}})</label>
                            <textarea type="text" name="body" class="form-control" placeholder="{{__('Blog Body')}}" >{!! $body ?? '' !!}</textarea>
                            <span class="text-danger"><strong>{{ $errors->first('body') }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-12 mt-20">
                    @if(isset($category->id))
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    @else
                        <button class="button-primary theme-btn">{{__('Create')}}</button>
                    @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('script')
<script src="{{ asset('storage/blog_news/assets/tinymce/tinymce.min.js') }}" referrerpolicy=""></script>
    <script>
        tinymce.init({
            selector:'textarea[name="body"]',
            height: '400px',
            plugins: ['searchreplace', 'link', 'anchor', 'image', 'media', 'charmap','fullscreen','code', 'preview','lists','help','wordcount'],
            toolbar: 'cut copy paste pastetext | undo redo | ' +
                    'bold italic underline strikethrough subscript superscript | '+
                    'numlist bullist | outdent indent | blockquote |alignleft aligncenter alignright alignjustify | '+
                    'image imagetools media preview print |',

            imagetools_toolbar: 'rotateleft rotateright | flipv fliph ',
            toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
            image_advtab: true ,
            media_live_embeds:true,
            
            external_filemanager_path:"{{ asset('storage/blog_news/assets/tinymce/plugins/responsivefilemanager/filemanager').'/' }}",
            filemanager_title:"{{ __('Filemanager') }}" ,
            external_plugins: { "filemanager" : "{{ asset('storage/blog_news/assets/tinymce/plugins/responsivefilemanager/filemanager/plugin.min.js') }}"},
            filemanager_access_key:"myPrivateKey" ,

            render_callback: function (editor) {
                $(".tox-promotion").empty();
            }
        });
    </script>
@endsection
