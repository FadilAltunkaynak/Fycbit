@extends('knowledgebase::layouts.master')
@section('style')

@endsection

@section('content')
@php($user = auth()->user())
@php($allsettings = knowledgebaseSupportSettings())
<!-- body text section start -->
<section class="my-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="chat_box rounded border p-3" id="conversations_list">
                    <div class="chat_list" >
                        <div class="row" id="append_conversation">
                            @if (isset($conversation_list))
                                @foreach ($conversation_list as $item)
                                    @if ($item->user_id != $user->id && $item->conversation_type == CONVERSATION_TYPE_RAGULAR)
                                        <div class="col-lg-8">
                                            <div class="d-flex my-2">
                                                <div>
                                                    <img class="chat_img" src="{{showUserImageSupport($item->user_id)}}" alt="" />
                                                </div>
                                                @if (isset($item->message))
                                                    <small class="chat_text me-2">{!!$item->message!!}</small>
                                                @endif
                                                @if (isset($item->conversationAttachment))
                                                    @foreach ($item->conversationAttachment as $key=>$attachment)
                                                        @if ($attachment->file_type == 'file')
                                                            <small class="chat_text me-2"><a class="" href="{{$attachment->file_link}}" target="_blank">
                                                                {{__('File '.$key+1)}} </a>
                                                            </small>
                                                            @elseif($attachment->file_type == 'img')
                                                            <small class="chat_text me-2"><a class="text-white" href="{{$attachment->file_link}}" target="_blank">
                                                                <img width="50" src="{{$attachment->file_link}}"></a>
                                                            </small>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        @elseif($item->conversation_type == CONVERSATION_TYPE_RAGULAR)
                                        <div class="col-lg-8 ms-auto">
                                            <div class="d-flex justify-content-end my-2">
                                                @if (isset($item->message))
                                                    <small class="chat_text me-2">{!!$item->message!!}</small>
                                                @endif

                                                @if (isset($item->conversationAttachment))
                                                    @foreach ($item->conversationAttachment as $key=>$attachment)
                                                            @if ($attachment->file_type == 'file')
                                                            <small class="chat_text me-2">
                                                                <a class="" href="{{$attachment->file_link}}" target="_blank">{{__('File '.$key+1)}} </a>
                                                            </small>
                                                            @elseif($attachment->file_type == 'img')
                                                                <a href="{{$attachment->file_link}}" target="_blank">
                                                                    <img class="rounded me-2 p-2" width="100" src="{{$attachment->file_link}}">
                                                                </a>
                                                            @endif
                                                    @endforeach
                                                @endif
                                                <div>
                                                    <img class="chat_img" src="{{showUserImageSupport($item->user_id)}}" alt="" />
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-4">
                    <form id="send_message_form" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="ticket_unique_code" value="{{$ticket_details->unique_code}}">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" class="form-control py-2" id="send-message-box"
                                aria-describedby="emailHelp" name="message"/>
                            <div class="chat_file_upload">
                                <input class="form-control" type="file" id="input_fiPendingle" name="files_name[]" />
                            </div>
                            <button class="btn chat_btn">{{__('send')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4 mt-5 mt-lg-0">
                @if (isset($ticket_details))
                    <div class="p_color chat-side-info mb-4 p-3 rounded">
                        <h5 class="uppercase">
                        {{$ticket_details->title}}
                        </h5>
                        <p> <small>{{__('ID')}}: # {{$ticket_details->id}}</small></p>
                        <!-- <p> <small>{{__('Title')}}:{{$ticket_details->title}}</small></p> -->
                        <p> <small>{{__('Status')}}:  {!! ticketStatusAdmin($ticket_details->status) !!}</small></p>
                            <p> <small>{{__('Assign To')}}: {{isset($ticket_details->agent) ? $ticket_details->agent->first_name.' '.$ticket_details->agent->last_name : 'Not Assign'}}</small></p>
                        <p> <small>{{__('Date')}}: {{$ticket_details->last_conversation_time}}</small></p>
                    </div>

                    <form action="{{route('user_support_ticket_note_store')}}" method="post">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{$ticket_details->id}}">
                        <h5 for="" class="uppercase p_color">{{__('Note')}}</h5>
                        <div class="side-ticket-add d-flex w-100 border p-3 rounded chat-side-info">
                            <input class="w-100 px-2 rounded" type="text" name="notes">
                            <button class="chat_btn btn ms-2" type="submit">{{__('Save')}}</button>
                        </div>
                    </form>
                    @endif

                    @if (isset($ticket_note_list))
                        @foreach ($ticket_note_list as $ticket_note)
                            <ul class="rounded chat-side-info mt-3 p-3">
                                <li>
                                    <div>
                                        {{$ticket_note->notes}}
                                    </div>
                                    <div>
                                        <a class="chat_btn btn mt-3" href="{{route('user_support_ticket_note_delete',$ticket_note->unique_code)}}"><small>{{__('Delete')}}</small></a>
                                    </div>
                                </li>
                            </ul>
                        @endforeach
                    @endif
            </div>

        </div>
    </div>
</section>
<!--  body text section end -->




@endsection

@section('script')
<script>
    let container = document.querySelector('#conversations_list');
    $( document ).ready(function() {
        container.scrollTop = container.scrollHeight;
    });

    $("#send_message_form").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '{{route('support_ticket_conversation_send')}}',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            success: function(response){
                console.log(response);
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

        Echo.channel('New-Message-'+id)
            .listen('.Conversation', (data) => {
                // console.log(data);
                if(data.success == true){
                    let html_view = '';
                    if(data.success == true)
                    {
                        var attachment = data.data.attachment;

                        if(data.data.user_id != senderId){

                            html_view += '<div class="col-md-8"> <div class="d-flex my-2"><div>';
                            html_view += '<img class="chat_img" src="'+data.data.sender_image_link+'"></div>';

                            if(data.data.message != null){
                                html_view += '<small class="chat_text me-2">'+data.data.message +'</small>';
                            }
                            if(attachment.length !=0 )
                            {
                                for(var i = 0; i < attachment.length ; i++)
                                {
                                    if (attachment[i].file_type == 'file')
                                    {
                                        html_view += '<small class="chat_text me-2"><a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank">File '+(i+1) +')}} </a></small>';
                                    }else{
                                        html_view += '<small class="chat_text me-2"><a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank"><img width="50" src="'+attachment[i].file_link+'"></a></small>'
                                    }

                                }
                            }
                            html_view += '</div></div>';
                        }else{
                            html_view += ' <div class="col-md-8 ms-auto"><div class="d-flex justify-content-end my-2">';

                            if(data.data.message != null){
                                html_view += '<small class="chat_text me-2">'+data.data.message +'</small>';
                            }
                            if(attachment.length !=0 )
                            {
                                for(var i = 0; i < attachment.length ; i++)
                                {
                                    if (attachment[i].file_type == 'file')
                                    {
                                        html_view += '<small class="chat_text me-2"><a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank">File '+(i+1) +' </a></small>';
                                    }else{
                                        html_view += '<small class="chat_text me-2"><a class="text-white m-1" href="'+attachment[i].file_link+'" target="_blank"><img width="50" src="'+attachment[i].file_link+'"></a></small>'
                                    }

                                }
                            }
                            html_view += '<div><img class="chat_img" src="'+data.data.sender_image_link+'"></div></div></div>';

                        }
                            $('#append_conversation').append(html_view);
                            container.scrollTop = container.scrollHeight;

                    }
                }
            })
    });

</script>
{{-- <script>
    jQuery(document).ready(function () {

    Pusher.logToConsole = true;
    let user_id = '{{Auth::id()}}';

    Echo.channel('New-Ticket-Notification-Send-To-User-'+user_id)
        .listen('.Notification', (data) => {
            console.log(data);

        })
});
</script> --}}
@endsection
