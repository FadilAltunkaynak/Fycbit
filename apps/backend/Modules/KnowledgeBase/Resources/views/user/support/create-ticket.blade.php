@extends('knowledgebase::layouts.master')

@section('content')
@php($user = auth()->user())
@php($notification_list = getNotificationList())
@php($allsettings = knowledgebaseSupportSettings())
@php($cover_image = $allsettings['cover_image'] ? url('/').'/'.FILE_KNOWLEDGE_BASE_VIEW_PATH.$allsettings['cover_image'] :  asset('assets/modules/knowledgebase/image/home_bg.jpg'))

<section class="bg_image py-5 " style="background-image:url({{$cover_image}})">
    <div class="container">
        <div class="row">
            <div class="text-center text-white mt-5">
                <h1>{{$allsettings['support_page_cover_first_title']}}</h1>
                <p>{{$allsettings['support_page_cover_second_title']}}</p>
            </div>
        </div>
    </div>
</section>

<!-- body text section start -->
<section class="my-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card p-3">
                    <h4 class="fw_600">{{__('Create New Ticket')}}</h4>
                    <form action="{{route('support_create_ticket_store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p_color pt-4">
                            <label>{{__('Choose Project')}}:</label>
                            <select class="form-select py-2 p_color mt-1" name="project_id" id="">
                                <option value="">{{__('Select project')}}</option>
                                @if (isset($project_list))
                                    @foreach($project_list as $project)
                                        <option value="{{$project->id}}">{{$project->name}}</option>
                                    @endforeach
                                @endif
                            </select>

                            <label class="pt-4">{{__('Title')}} :</label>
                            <input class="form-control" type="text" name="title" value="{{old('title')}}">

                            <label class="pt-4">{{__('Description')}}</label>
                            <textarea id="description" name="description" rows="5" class="form-control-new textarea note-editable">{{old('description')}}</textarea>

                            <label class="pt-4">{{__('Purchase Code')}} ({{__('optional')}}) :</label>
                            <input class="form-control" type="text" name="purchase_code" value="{{old('purchase_code')}}">

                            <label class="pt-4">{{__('Attach File')}}:</label>
                            <input class="form-control" type="file" name="files[]" multiple>
                            <button
                            class="btn btn-info fw-bolder text-white mt-4 px-5 py-2">
                            {{__('Submit')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!--  body text section end -->


@endsection

@section('script')

<script src="{{asset('assets/modules/knowledgebase/js/tinymce/tinymce.min.js')}}"></script>
  <script>
    tinymce.init({
      selector: 'textarea#description', // Replace this CSS selector to match the placeholder element for TinyMCE
      plugins: 'code table lists',
      toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
    });
  </script>
@endsection
