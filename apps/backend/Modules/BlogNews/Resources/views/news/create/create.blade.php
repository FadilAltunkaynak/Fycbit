@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'news','sub_menu'=>'news'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Create News')}}</li>
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

                        <form id="submitFormNews" action="{{ route('createNewsProcess') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @if(isset($news))
                                <input type="hidden" value="{{ $news->slug ?? '' }}" name="slug" />
                            @endif
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="account_holder_name">{{__('News Title')}}</label>
                                        <input maxlength="100" type="text" value="{{ $news->title ?? old('title') }}" name="title" class="form-control" placeholder="{{__('News Title')}}" >
                                        <span class="text-danger"><strong>{{ $errors->first('title') }}</strong></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="control-label" for="account_holder_name">{{__('Category')}}</label>
                                        <select name="category" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option>{{ __("Select a category") }}</option>
                                            @foreach($category as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                            @endforeach
                                        </select>  
                                        <span class="text-danger"><strong>{{ $errors->first('category') }}</strong></span>
                                    </div>
                                            {{-- <div class="col-12">
                                                <div class="form-group">
                                                    <label class=" control-label" for="account_holder_name">{{__('Sub Category')}}</label>
                                                    <select name="sub_category" class="selectpicker" data-width="100%">
                                                        @if(isset($sub_category) && isset($news))
                                                            @foreach($sub_category as $sub)
                                                                <option value="{{ $sub->id }}">{{ $sub->title }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <span class="text-danger"><strong>{{ $errors->first('sub_category') }}</strong></span>
                                                </div>
                                            </div> --}}
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Status") }}</label>
                                        <div class="cp-select-area">
                                            <select name="status" class="form-control">
                                                <option @if(isset($news->status) && $news->status == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("ACTIVE")}}</option>
                                                <option @if(isset($news->status) && $news->status == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{__("INACTIVE")}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Allow Comment") }}</label>
                                        <div class="cp-select-area">
                                            <select name="comment_allow" class="form-control" data-style="btn-dark">
                                                <option @if(isset($news->comment_allow) && $news->comment_allow == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Allow")}}</option>
                                                <option @if(isset($news->comment_allow) && $news->comment_allow == STATUS_INACTIVE) selected @endif value="{{STATUS_INACTIVE}}">{{__("Disallow")}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label" for="account_holder_name">{{__('News Thumbnail')}}</label>
                                        <input type="file" name="thumbnail" class="dropify" data-show-remove="false" data-default-file="{{ $news->thumbnail ?? '' }}"  />
                                        <span class="text-danger"><strong>{{ $errors->first('thumbnail') }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="account_holder_name">{{__('')}}</label>
                                <textarea type="text" name="body" class="form-control" placeholder="{{__('news Body')}}" >{!! $news->body ?? '' !!}</textarea>
                                <span class="text-danger"><strong>{{ $errors->first('body') }}</strong></span>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Keywords (Meta Tag)") }}</label>
                                        <input type="text" name="keywords" value="{{ $news->keywords ?? old("keywords") }}" class="form-control" placeholder="{{ __('Keywords') }}" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Summary & Description (Meta Tag)") }}</label>
                                        <textarea class="form-control" name="description">{!! $news->description ?? old("description") !!}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                @if(isset($news))
                                    <input type="submit" class="btn btn-primary bg-primary" value="Update News" />
                                @else
                                    <input type="submit" class="btn btn-primary bg-primary" value="Create News" />
                                @endif
                                @if(isset($news) && $news->publish)
                                @else
                                    <input type="submit" class="btn btn-info bg-success" name="publish" value="{{ __("Publish") }}" />
                                    <a href="#Publish_at" data-toggle="modal"  class="btn btn-info bg-success">{{ __("Publish At") }}</a>
                                    <div id="Publish_at" class="modal fade delete" role="dialog">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title"></h6>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="control-label">{{ __("Publish Date") }}</label>
                                                    <input type="date" id="publish_at_temp" class="form-control" min="2023-01-20"/>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Close") }}</button>
                                                    <button onclick="setTimePublishAt()" class="btn btn-success" type="button">{{ __('Publish') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function setTimePublishAt()
                                        {
                                            let submitButton = document.querySelector('input[name="publish"]');
                                            let submitData = document.querySelector('input[id="publish_at_temp"]').value;
                                            submitButton.outerHTML =  `<input type="hidden" name="publish_at" value="${submitData}" />`;
                                            let submitFormBlog = document.querySelector('#submitFormNews');
                                            submitFormBlog.submit();
                                        }
                                        let submitMinTimeOne = (new Date()).getDate() + 1;
                                        let submitMinTime = (new Date()).setDate(submitMinTimeOne);
                                        let submitMinTimeSet = (new Date(submitMinTime)).toLocaleDateString();
                                        let arrayTimeDateSet = submitMinTimeSet.split('/');
                                        let arrayTimeDateResolved = 10 > arrayTimeDateSet[0] ? '0'+arrayTimeDateSet[0] : arrayTimeDateSet[0];
                                        submitMinTimeSet = arrayTimeDateSet[2]+'-'+arrayTimeDateResolved+'-'+arrayTimeDateSet[1];
                                        document.querySelector('input[id="publish_at_temp"]').setAttribute('min', submitMinTimeSet);
                                    </script>
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
    <script>

        @if(isset($news))
            $('select[name="category"]').selectpicker('val', '{{ $news->category ?? '' }}');
            //$('select[name="sub_category"]').selectpicker('val', '{{ $news->sub_category ?? '' }}');
        @endif
        $(document).ready(()=>{
            $(".tox-promotion").empty();
            $('select[name="category"]').on('change', ()=>{
                // let id = $('select[name="category"]').val();
                // $.get(
                //     "{{ route('getNewsSubCategorys') }}"+id,
                //     function(data){
                //         if(data.success){
                //             $('select[name="sub_category"]').selectpicker('destroy');
                //             $('select[name="sub_category"]').html('');
                //             (data.data).forEach((cat)=>{
                //                 $html = '<option value="'+cat.id+'" >'+cat.title+'</option>';
                //                 $('select[name="sub_category"]').append($html);
                //             });
                //             $('select[name="sub_category"]').selectpicker('render');
                //         }
                //     }
                // );
            });
        });
    </script>
@endsection
