@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include($inclue,['menu'=>'custom-pages'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Custom Page')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <form action="{{ route('createCustomPagesProcess') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @if(isset($page))
                                <input type="hidden" value="{{ encrypt($page->id ?? 0) }}" name="id" />
                            @endif
                            <input type="hidden" value="{{ $type }}" name="type" />
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="account_holder_name">{{__('Title')}}</label>
                                        <input type="text" value="{{ $page->title ?? old('title') }}" name="title" class="form-control" placeholder="{{__('Blog Title')}}" >
                                        <span class="text-danger"><strong>{{ $errors->first('title') }}</strong></span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Status") }}</label>
                                        <div class="cp-select-area">
                                            <select name="status" class="form-control">
                                                <option @if(isset($page->status) && $page->status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("ACTIVE")}}</option>
                                                <option @if(isset($page->status) && $page->status == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{__("INACTIVE")}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="account_holder_name">{{__('')}}</label>
                                <textarea type="text" name="body" class="form-control" placeholder="{{__('Body')}}" >{!! $page->body ?? '' !!}</textarea>
                                <span class="text-danger"><strong>{{ $errors->first('body') }}</strong></span>
                            </div>
                            <div class="form-group">
                                @if(isset($page))
                                    <input type="submit" class="btn btn-primary" value="Update Page" />
                                @else
                                    <input type="submit" class="btn btn-primary" value="Create Page" />
                                @endif
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
    <script src="{{ asset('modules/blog_news/tinymce/tinymce.min.js') }}" referrerpolicy=""></script>
    <script>
        tinymce.init({
            selector:'textarea',
            height: '400px',
            plugins: ['searchreplace', 'link', 'anchor', 'image', 'media', 'charmap','fullscreen','code', 'preview','lists','help','wordcount'],
            toolbar: 'cut copy paste pastetext | undo redo | searchreplace | selectall | link unlink anchor | ' +
                    'bold italic underline strikethrough subscript superscript | '+
                    'numlist bullist | outdent indent | blockquote |alignleft aligncenter alignright alignjustify | '+
                    'help | image imagetools media preview print |',

            imagetools_toolbar: 'rotateleft rotateright | flipv fliph ',
            toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
            image_advtab: true ,
            media_live_embeds:true,
            
            external_filemanager_path:"{{ asset('modules/blog_news/tinymce/plugins/responsivefilemanager/filemanager').'/' }}",
            filemanager_title:"{{ __('Filemanager') }}" ,
            external_plugins: { "filemanager" : "{{ asset('modules/blog_news/tinymce/plugins/responsivefilemanager/filemanager/plugin.min.js') }}"},
            filemanager_access_key:"myPrivateKey" ,

            render_callback: function (editor) {
                $(".tox-promotion").empty();
            }
        });
    </script>
    <script>
        $(document).ready(()=>{
            $(".tox-promotion").empty();
        });
    </script>
@endsection
