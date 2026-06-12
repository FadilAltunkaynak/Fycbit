@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu' => 'comment'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Blog Comments')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="col-12 ml-0">
            <div class="card-body">
            <h4 class="text-white">Blog Title : {{ $post ?? __('Post not found') }}</h4>
                <div class="table-area payment-table-area">
                    <div class="table-responsive">
                        <table id="table-blog" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col">{{__('Name')}}</th>
                                <th scope="col">{{__('Email')}}</th>
                                <th scope="col">{{__('Website')}}</th> 
                                <th scope="col">{{__('Message')}}</th> 
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
            ajax: '{{route('commentList')}}'+'{{ $id ?? 0 }}',
            order: [2],
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
                {"data": "name"},
                {"data": "email"},
                {"data": "website"},
                {"data": "message"},
                {"data": "actions"}
            ]
        });
    }
    getListData();

    function callApiHere(type, main, sub = 0){
        $.get(
            "{{ route('getSubCategorys') }}"+main,
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
