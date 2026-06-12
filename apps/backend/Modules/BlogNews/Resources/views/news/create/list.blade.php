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
                    <li class="active-item">{{__('News')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="">
            
        </div>
        <div class="col-12 ml-0">
            <div class="card-body">
                <div class="table-area payment-table-area">
                <a href="{{ route('createNewsPage') }}" class="btn mr-3 float-right btn-primary">{{ __("Create News") }}</a>
                    <div class="table-responsive">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="account_holder_name">{{__('Category')}}</label>
                                    <select id="category" data-type="{{ TYPE_MAIN_CATEGORY }}" class="selectpicker" data-style="bg-dark" data-width="100%">
                                        <option value="0">{{ __("All News") }}</option>
                                        @foreach($category ?? [] as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                        @endforeach
                                    </select>  
                                </div>
                            </div>
                            {{-- <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="account_holder_name">{{__('Sub Category')}}</label>
                                    <select id="sub_category" data-type="{{ TYPE_SUB_CATEGORY }}" class="selectpicker" data-style="bg-dark" data-width="100%">
                                        @if(isset($sub_category) && isset($blog))
                                            @foreach($sub_category as $sub)
                                                <option value="{{ $sub->id }}">{{ $sub->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="text-danger"><strong>{{ $errors->first('sub_category') }}</strong></span>
                                </div>
                            </div> --}}
                        </div>
                        <table id="table-blog" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col">{{__('Thumbnail')}}</th>
                                <th scope="col">{{__('Blog Title')}}</th>
                                <th scope="col">{{__('Publish')}}</th>
                                <th scope="col">{{__('Status')}}</th>
                                <th scope="col">{{__('Translation')}}</th>
                                <th scope="col">{{__('Action')}}</th> 
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
<script>
    function getListData(type = 1,main = 0, sub = 0) {
        $('#table-blog').DataTable({
            dom: '',
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: '{{route('newsPostData')}}'+'/'+type+'/'+main+'/'+sub,
            order: [2, 'desc'],
            // autoWidth: true,
            language: {
                paginate: {
                    next: 'Next &#8250;',
                    previous: '&#8249; Previous'
                }
            },
            columnDefs: [
                { width: '50px', targets: 0 },
                { className: 'dt-left', targets: [1] }
            ],
            columns: [
                {"data": "thumbnail"},
                {"data": "title"},
                {"data": "published"},
                {"data": "status"},
                {"data": "translation"},
                {"data": "actions"}
            ]
        });
    }
    getListData();

    function callApiHere(type, main, sub = 0){
        $.get(
            "{{ route('getNewsSubCategorys') }}"+main,
            function(data){
                if(data.success){
                    if(type == {{ TYPE_MAIN_CATEGORY }}){
                        $('select[id="sub_category"]').selectpicker('destroy');
                        $('select[id="sub_category"]').html('');
                        (data.data).forEach((cat)=>{
                            $html = '<option value="'+cat.id+'" >'+cat.title+'</option>';
                            $('select[id="sub_category"]').append($html);
                        });
                        $('select[id="sub_category"]').selectpicker('render');
                    }
                    $('#table-blog').DataTable().clear();
                    $('#table-blog').DataTable().destroy();
                    getListData(type,main,sub);
                }
            }
        );
    }

    $(document).ready(()=>{
        $('select[id="category"]').on('change', ()=>{
            let id = $('select[id="category"]').val();
            let type = $('select[id="category"]').data('type');
            callApiHere(type, id);
        });
        $('select[id="sub_category"]').on('change', ()=>{
            let main = $('select[id="category"]').val();
            let sub = $('select[id="sub_category"]').val();
            let type = $('select[id="sub_category"]').data('type');
            callApiHere(type, main, sub);
        });
    });
</script>
@endsection
