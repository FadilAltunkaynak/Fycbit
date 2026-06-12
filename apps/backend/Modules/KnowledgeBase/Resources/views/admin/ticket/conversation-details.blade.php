@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')
<link rel="stylesheet" href="{{asset('assets/modules/knowledgebase/css/admin-style.css')}}">
@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_support_list','sub_menu'=>''])
@endsection

@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    @php($ticket_details = $data['ticket_details'])
    <div class="user-management pt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="row" >
                    @if (isset($ticket_details))
                        <div class="col-md-12 admin-ticket-header">
                            <h3>{{__('Ticket ID')}}: : #{{$ticket_details->id}}</h3>
                            <h5>{{__('Ticket Title')}}: {{$ticket_details->title}}</h4>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="row">
                            <div class="col-md-3">
                                <label for="">{{__('Agent')}}</label>
                                <select onchange="ticketAgentChange(this.value, '{{$ticket_details->unique_code}}','{{__('You want to change the Agent!')}}')" name="agent_id" class="form-control">
                                    <option value="">{{__('Select Option')}}</option>
                                    @if (isset($agent_list))
                                        @foreach ($agent_list as $agent)
                                            <option value="{{$agent->id}}"
                                                {{$ticket_details->assigned_agent_id == $agent->id?'selected':''}}>{{$agent->first_name .' '. $agent->last_name}}</option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">{{__('Project')}}</label>
                                <select onchange="ticketProjectChange(this.value, '{{$ticket_details->unique_code}}','{{__('You want to change the ticket project!')}}')" name="project_id" class="form-control">
                                    <option value="">{{__('Select Option')}}</option>
                                    @if (isset($project_list['project_list']))
                                        @foreach ($project_list['project_list'] as $project)
                                            <option value="{{$project->id}}"
                                                {{$ticket_details->project_id == $project->id?'selected':''}}>{{$project->name}}</option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">{{__('Category Status')}}</label>
                                <select onchange="ticketCategoryChange(this.value, '{{$ticket_details->unique_code}}','{{__('You want to change the ticket category!')}}')" name="category_id" class="form-control">
                                    <option value="">{{__('Select Option')}}</option>
                                    @if (isset($category_list))
                                        @foreach ($category_list as $category)
                                            <option value="{{$category->id}}"
                                                {{$ticket_details->category_id == $category->id?'selected':''}}>{{$category->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="">{{__('Status')}}</label>
                                <select onchange="ticketStatusChange(this.value, '{{$ticket_details->unique_code}}','{{__('You want to change the ticket status!')}}')" name="ticket_status" class="form-control">

                                    @foreach (ticketStatus() as $ticket_status_key=>$ticket_status)
                                        <option value="{{$ticket_status_key}}"
                                            {{$ticket_details->status == $ticket_status_key?'selected':''}}>{{$ticket_status}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                        </div>
                    @endif
                </div>
                @php($user = auth()->user())
                <div class="row chat-card" id="conversations_list">
                    <div class="col-12" id="append_conversation">
                        @if (isset($data['conversation_list']))

                            @foreach ($data['conversation_list'] as $item)
                                @if ($item->user_id != $user->id && $item->conversation_type == CONVERSATION_TYPE_RAGULAR)
                                    <div class="row">
                                        <div class="">
                                            <img class="sender-receiver-img" src="{{showUserImageSupport($item->user_id)}}">
                                        </div>
                                        <div class="col-md-9 ml-2">
                                            <div class="row">
                                                <div class="sender-conversation">
                                                    <p >
                                                        {!!$item->message!!}
                                                    </p>
                                                    @if (isset($item->conversationAttachment))
                                                       @foreach ($item->conversationAttachment as $key=>$attachment)
                                                            @if ($attachment->file_type == 'file')
                                                                <a class="text-white" href="{{$attachment->file_link}}" target="_blank">
                                                                    {{__('File '.$key+1)}} </a>
                                                            @elseif($attachment->file_type == 'img')
                                                                <a class="text-white" href="{{$attachment->file_link}}" target="_blank">
                                                                    <img width="50" src="{{$attachment->file_link}}">
                                                                </a>
                                                            @endif
                                                       @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @elseif($item->conversation_type == CONVERSATION_TYPE_RAGULAR)
                                    <div class="row d-flex flex-row-reverse">
                                        <div class="ml-2">
                                            <img class="sender-receiver-img" src="{{showUserImageSupport($item->user_id)}}">
                                        </div>
                                        <div class="col-md-9 ">
                                            <div class="row d-flex flex-row-reverse">
                                                <div class="receiver-conversation">

                                                    <p >
                                                        {!!$item->message!!}
                                                    </p>

                                                    @if (isset($item->conversationAttachment))
                                                       @foreach ($item->conversationAttachment as $key=>$attachment)
                                                            @if ($attachment->file_type == 'file')
                                                                <a class="text-white" href="{{$attachment->file_link}}" target="_blank">
                                                                    {{__('File '.$key+1)}} </a>
                                                            @elseif($attachment->file_type == 'img')
                                                                <a class="text-white" href="{{$attachment->file_link}}" target="_blank">
                                                                    <img width="50" src="{{$attachment->file_link}}">
                                                                </a>
                                                            @endif
                                                       @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($item->conversation_type == CONVERSATION_TYPE_STATUS)
                                    <div class="row ">
                                        <div class="col-md-12 ">
                                            <div class="row d-flex justify-content-center">
                                                <div class="receiver-conversation-note">
                                                    <p >
                                                        {{$item->message}}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif


                    </div>
                </div>
                <form id="send_message_form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ticket_unique_code" value="{{$data['ticket_details']->unique_code}}">
                    <div class="row m-4">
                        <div class="w-100">
                            <label class="bg-white p-2" >
                                <input id="input_file" class="text-dark" name="files_name[]"  type="file" multiple>
                            </label>
                        </div>
                        <input id="send-message-box" class="text-dark p-2 send-box-conversation" name="message" type="text"/>
                        <button class="send-button-conversation">{{__('Send')}}</button>

                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form action="{{route('admin_support_ticket_note_store')}}" method="post">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{$ticket_details->id}}">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">{{__('Add Note')}}</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-success float-right mt-2" type="submit">{{__('Save')}}</button>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="text-white">
                            {{__('Ticket Note List')}}
                        </h2>
                    </div>
                    <div class="note-card">
                        @if (isset($ticket_note_list) && $ticket_note_list->count()>0)
                            @foreach ($ticket_note_list as $ticket_note)
                                <div class="note-card-item">
                                    <div class="note-card-details">
                                        <p class="text-white p-1">{{$ticket_note->notes}}</p>
                                    </div>
                                    <div class="note-card-delete-btn">
                                        <a class="btn btn-sm btn-warning"
                                            href="{{route('admin_support_ticket_note_delete',$ticket_note->unique_code)}}"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="note-card-item">
                                <div class="note-card-details">
                                    <p class="text-white p-1">{{__('You do not have any note')}}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
<script src="{{asset('assets/modules/knowledgebase/js/sweetalert.js')}}"></script>
<script>
    let container = document.querySelector('#conversations_list');
    $( document ).ready(function() {
        container.scrollTop = container.scrollHeight;
    });

    $("#send_message_form").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '{{route('support_agent_ticket_conversation_send')}}',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            success: function(response){
                // console.log(response);
                if(response.success == true)
                {
                    $('#send-message-box').val('');
                    $('#input_file').val('');
                }else{
                    $('#send-message-box').val('');
                    $('#input_file').val('');

                    VanillaToasts.create({
                        text: response.message,
                        type: 'warning',
                        timeout: 4000
                    });
                }
            }
        });
    });

    jQuery(document).ready(function () {
        Pusher.logToConsole = true;
        let senderId = '{{Auth::id()}}';
        var id = '{{Auth::id().'-'.$ticket_details->unique_code}}';
        console.log(id)
        Echo.channel('New-Message-'+id)
            .listen('.Conversation', (data) => {
                // console.log(data);
                if(data.success == true){
                    let html_view = '';
                    if(data.success == true)
                    {
                        var attachment = data.data.attachment;

                        if(data.data.user_id != senderId){

                            html_view += '<div class="row"><div class="">';
                            html_view += '<img class="sender-receiver-img" src="'+data.data.sender_image_link+'"></div><div class="col-md-9 ml-2"><div class="row">';
                            html_view += '<div class="receiver-conversation">';
                            if(data.data.message != null){
                                html_view += '<p >'+data.data.message +'</p>';
                            }
                            if(attachment.length !=0 )
                            {
                                for(var i = 0; i < attachment.length ; i++)
                                {
                                    if (attachment[i].file_type == 'file')
                                    {
                                        html_view += '<a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank">File '+(i+1) +')}} </a>';
                                    }else{
                                        html_view += '<a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank"><img width="50" src="'+attachment[i].file_link+'"></a>'
                                    }

                                }
                            }
                            html_view += '</div></div></div></div>';
                        }else{
                            html_view += '<div class="row d-flex flex-row-reverse"><div class="ml-2">';
                            html_view += '<img class="sender-receiver-img" src="'+data.data.sender_image_link+'"></div><div class="col-md-9"><div class="row d-flex flex-row-reverse">';
                            html_view += '<div class="sender-conversation">';
                            if(data.data.message != null){
                                html_view += '<p >'+data.data.message +'</p>';
                            }
                            if(attachment.length !=0 )
                            {
                                for(var i = 0; i < attachment.length ; i++)
                                {
                                    if (attachment[i].file_type == 'file')
                                    {
                                        html_view += '<a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank">File '+(i+1) +' </a>';
                                    }else{
                                        html_view += '<a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank"><img width="50" src="'+attachment[i].file_link+'"></a>'
                                    }

                                }
                            }
                            html_view += '</div></div></div></div>';
                        }
                            $('#append_conversation').append(html_view);
                            container.scrollTop = container.scrollHeight;

                    }
                }
            })
    });

    function ticketStatusChange(status,ticket_unique_code,message)
    {
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((value) => {
                if(value)
                {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('support_agent_ticket_status_change')}}',
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'ticket_status': status,
                            'ticket_unique_code': ticket_unique_code
                        },
                        success: function(response){
                            // console.log(response);
                            if(response.success)
                            {
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'success',
                                    timeout: 4000
                                });

                            }else{
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'warning',
                                    timeout: 4000
                                });

                            }

                        }
                    });
                }
            });
    }

    function ticketCategoryChange(category_id,ticket_unique_code,message)
    {
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((value) => {
                if(value)
                {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('support_agent_ticket_category_change')}}',
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'category_id': category_id,
                            'ticket_unique_code': ticket_unique_code
                        },
                        success: function(response){
                            // console.log(response);
                            if(response.success)
                            {
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'success',
                                    timeout: 4000
                                });

                            }else{
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'warning',
                                    timeout: 4000
                                });

                            }

                        }
                    });
                }
            });
    }

    function ticketProjectChange(project_id,ticket_unique_code,message)
    {
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((value) => {
                if(value)
                {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('support_agent_ticket_project_change')}}',
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'project_id': project_id,
                            'ticket_unique_code': ticket_unique_code
                        },
                        success: function(response){
                            // console.log(response);
                            if(response.success)
                            {
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'success',
                                    timeout: 4000
                                });

                            }else{
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'warning',
                                    timeout: 4000
                                });

                            }

                        }
                    });
                }
            });
    }

    function ticketAgentChange(agent_id,ticket_unique_code,message)
    {
        swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
            })
            .then((value) => {
                if(value)
                {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('support_agent_ticket_agent_change')}}',
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'agent_id': agent_id,
                            'ticket_unique_code': ticket_unique_code
                        },
                        success: function(response){
                            // console.log(response);
                            if(response.success)
                            {
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'success',
                                    timeout: 4000
                                });

                            }else{
                                VanillaToasts.create({
                                    text: response.message,
                                    type: 'warning',
                                    timeout: 4000
                                });

                            }

                        }
                    });
                }
            });
    }
</script>
@endsection
