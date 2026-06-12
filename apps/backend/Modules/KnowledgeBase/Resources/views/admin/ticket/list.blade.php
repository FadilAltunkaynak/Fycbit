@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')
@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_support_list','sub_menu'=>$sub_menu])
@endsection

@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{$title}} </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-12">
                <div class="pb-2">
                    <form action="{{route('support_ticket_list')}}" method="GET">
                        <input type="hidden" name="assigned_type" value="{{$sub_menu}}">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">{{__('Search')}}</label>
                                <input class="form-control" type="text" name="search"
                                @if (request('search'))
                                    value="{{request('search')}}"
                                @endif
                                placeholder="{{__('Search by ticket id, title, purchase code, user')}}">
                            </div>
                            <div class="col-md-2">
                                <label for="">{{__('Filter By Project')}}</label>
                                <select name="project_type" id="" class="form-control">
                                    <option value="">{{__('Select option')}}</option>
                                    @if (isset($project_list))
                                        @foreach ($project_list as $project)
                                            <option value="{{$project->id}}"
                                                {{request('project_type') == $project->id ? 'selected' : ''}}>{{$project->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">{{__('Filter By Category')}}</label>
                                <select name="category_type" id="" class="form-control">
                                    <option value="">{{__('Select option')}}</option>
                                    @if (isset($category_list))
                                        @foreach ($category_list as $category)
                                            <option value="{{$category->id}}"
                                                {{request('category_type') == $category->id ? 'selected' : ''}}>{{$category->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="">{{__('Filter By Status')}}</label>
                                <select name="status_type" class="form-control">
                                    <option value="">{{__('Select option')}}</option>
                                        @foreach (ticketStatus() as $key=>$ticket_status)
                                            <option value="{{$key}}"
                                            {{request('status_type') == $key ? 'selected' : ''}}>{{$ticket_status}}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-success" style="margin-top: 29px;">
                                    {{__('Filter')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-area mt-5">
                    <div class="table-responsive">
                        <table id="table" class=" table custom-table display text-lg-center" width="100%">
                            <thead>
                            <tr>
                                <th scope="col" class="all">{{__('Ticket ID')}}</th>
                                <th scope="col" class="all">{{__('Ticket Details')}}</th>
                                <th scope="col" class="all">{{__('User Details')}}</th>
                                <th scope="col" class="all">{{__('Assigned To')}}</th>
                                <th scope="col" class="all">{{__('Ticket Category')}}</th>
                                <th scope="col" class="all">{{__('Ticket Project')}}</th>
                                <th scope="col" class="all">{{__('Status')}}</th>
                                <th scope="col" class="all">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($ticket_list))
                                @foreach($ticket_list as $ticket)
                                    <tr>
                                        <td> #{{$ticket->id}} </td>
                                        <td>
                                            <div>
                                                <span>
                                                    <b>{{ __('Title') }}:</b>
                                                    {!! Str::limit($ticket->title,40)!!}
                                                </span><br>
                                                <span>
                                                    <b>{{ __('Last Message') }}:</b>
                                                    {!! Str::limit($ticket->last_conversation->message,30) !!}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ isset($ticket->user) ?$ticket->user->first_name .' '.$ticket->user->first_name:__('Not Found')}} <br>
                                            {{ isset($ticket->user) ?$ticket->user->email :__('Not Found')}}
                                        </td>
                                        <td>
                                            {{ isset($ticket->agent) ?$ticket->agent->first_name .' '.$ticket->agent->first_name:__('Not Assigned')}}
                                        </td>
                                        <td>
                                            {{isset($ticket->category)?$ticket->category->name:__('Not Assigned')}}
                                        </td>
                                        <td>
                                            {{isset($ticket->project)?$ticket->project->name:__('Not selected')}}
                                        </td>
                                        <td>

                                            @if ($ticket->status == TICKET_STATUS_OPEN)
                                                <span class="badge badge-success">
                                                    {{ticketStatus($ticket->status)}}
                                                </span>
                                            @elseif($ticket->status == TICKET_STATUS_CLOSE)
                                                <span class="badge badge-warning">
                                                    {{ticketStatus($ticket->status)}}
                                                </span>
                                            @elseif($ticket->status == TICKET_STATUS_CLOSE_FOREAVER)
                                                <span class="badge badge-danger">
                                                    {{ticketStatus($ticket->status)}}
                                                </span>
                                            @else
                                                <span class="badge badge-info">
                                                    {{ticketStatus($ticket->status)}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn {{$ticket->is_seen_by_user == SEEN? 'btn-warning':'btn-success'}}"
                                                href="{{route('support_agent_ticket_conversation', $ticket->unique_code)}}">{{__('View Details')}}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
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
(function($) {
        "use strict";
        $('#table').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering:  false,
            select: false,
            bDestroy: true
        });
    })(jQuery);
</script>
@endsection
