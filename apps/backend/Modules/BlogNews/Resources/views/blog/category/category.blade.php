@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'blog','sub_menu'=>'main_category'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Category')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <div class="user-management">
        
        <div class="row">
            <div class="col-12">
                <div class="mr-2 mb-1">
                    <a href="{{ route('CategorySubmitPage') }}" class="btn mr-3 float-right btn-primary">{{ __("Create Category") }}</a>
                </div><br>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-blog" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Title')}}</th>
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

@endsection
@section('script')
<script>
     (function($) {
            "use strict";

            $('#table-blog').DataTable({
                dom: '',
                processing: true,
                serverSide: true,
                pageLength: 25,
                responsive: true,
                ajax: '{{ route('CategoryPage') }}',
                //order: [1, 'desc'],
               // autoWidth: true,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                columns: [
                   {"data": "title"},
                   {"data": "status"},
                   {"data": "translation"},
                   {"data": "actions"}
                ]
            });
    })(jQuery);
</script>
@endsection
