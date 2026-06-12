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
                    <li class="active-item">{{$title}}</li>
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
                        <table id="table-blog" class="table table-borderless custom-table display text-center" width="100%">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Blog Title')}}</th>
                                    <th scope="col">{{__('Language Name')}}</th>
                                    <th scope="col">{{__('Action')}}</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($language_list))
                                    @foreach ($language_list as $language)
                                        <tr>
                                            <td>{{$news_post_details->title}}</td>
                                            <td>{{$language->name}}</td>
                                            <td>
                                                <a class="btn btn-primary" href="{{route('newsPostTranslateUpdatePage',['id'=>encrypt($news_post_details->id),'lan_key'=>$language->key])}}">{{__('Update Text')}}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    
                                @endif
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
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                ordering:  true,
                select: false,
                bDestroy: true,
                order: [0, 'asc'],
                responsive: true,
                autoWidth: false,
                language: {
                    "decimal":        "",
                    "emptyTable":     "{{__('No data available in table')}}",
                    "info":           "{{__('Showing')}} _START_ to _END_ of _TOTAL_ {{__('entries')}}",
                    "infoEmpty":      "{{__('Showing')}} 0 to 0 of 0 {{__('entries')}}",
                    "infoFiltered":   "({{__('filtered from')}} _MAX_ {{__('total entries')}})",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "{{__('Show')}} _MENU_ {{__('entries')}}",
                    "loadingRecords": "{{__('Loading...')}}",
                    "processing":     "",
                    "search":         "{{__('Search')}}:",
                    "zeroRecords":    "{{__('No matching records found')}}",
                    "paginate": {
                        "first":      "{{__('First')}}",
                        "last":       "{{__('Last')}}",
                        "next":       '{{__('Next')}} &#8250;',
                        "previous":   '&#8249; {{__('Previous')}}'
                    },
                    "aria": {
                        "sortAscending":  ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                },
            });
    })(jQuery);
</script>
@endsection
