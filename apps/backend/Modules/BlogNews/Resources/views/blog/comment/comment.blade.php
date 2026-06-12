@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'comment'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Blog Pending Comments')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-blog-comment" class="table table-borderless custom-table display text-center" width="100%">
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
    
@endsection
@section('script')
<script>
        $('#table-blog-comment').DataTable({
            dom: '',
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: '{{route('BlogComment')}}',
            //order: [1, 'desc'],
            // autoWidth: true,
            language: {
                paginate: {
                    next: 'Next &#8250;',
                    previous: '&#8249; Previous'
                }
            },
            //columnDefs: [
            //    { width: '50px', targets: 0 },
            //    { className: 'dt-left', targets: [1] }
            //],
            columns: [
                {"data": "name"},
                {"data": "email"},
                {"data": "website"},
                {"data": "message"},
                {"data": "actions"}
            ]
        });
</script>
@endsection
