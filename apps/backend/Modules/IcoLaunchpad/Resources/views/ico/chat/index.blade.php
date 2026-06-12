@extends('admin.master')
@section('title', isset($title) ? $title : __('ICO List'))
@section('style')
<style>
    .chat-card {
        margin:26px;
        padding:30px;
        color:aliceblue;
        border: 1px solid #979797;
        height: 60vh;
        overflow-y: scroll;
        font-size: 20px;
    }
    .sender-conversation {
        padding:8px;
        margin-bottom:10px;
        border-radius:5px;
        background: #646664;
    }
    .receiver-conversation {
        padding:8px;
        margin-bottom:10px;
        background: #646664;
        text-align: right;
        border-radius:5px;;
    }
    .send-box-conversation {
        width:80%;
        height:50px;
    }
    .send-button-conversation {
        width: 18%;
        height:50px;
        background:green;
        margin-left:5px;
        border:none;
    }
    .ico-token-table {
        font-size:20px;
    }
    .sender-receiver-img {
        width:40px;
        height:40px;
        border-radius: 50%;
    }
</style>
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'ico_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-5">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- /breadcrumb -->
        @php($user = auth()->user())
        @php($allowed = getAllowedFiles())
        <div class="row">
            <div class="col-6">
                <div class="row chat-card" id="conversations_list">
                    <div class="col-12" id="append_conversation">
                        @if (isset($conversation_list))
                            @foreach ($conversation_list as $item)
                                @if ($item->sender_id != $user->id)
                                    <div class="row">
                                        <div class="">
                                            <img class="sender-receiver-img" src="{{showUserImage($item->sender_id)}}">
                                        </div>
                                        <div class="col-md-9 ml-2">
                                            <div class="row">
                                                <div class="sender-conversation">
                                                    <p >
                                                        {{$item->message}}
                                                    </p>
                                                    @if (isset($item->file_name))
                                                        <?php
                                                                $type = explode(".",$item->file_name);
                                                                if (in_array($type[1], $allowed)) {
                                                                    $file_type = 'file';
                                                                } else {
                                                                    $file_type = 'img';
                                                                }
                                                        ?>
                                                        @if ($file_type == 'file')
                                                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">
                                                                {{$item->file_name}} </a>
                                                        @elseif($file_type == 'img')
                                                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">
                                                                <img width="50" src="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}">
                                                            </a>
                                                        @endif
                                                        
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    <div class="row d-flex flex-row-reverse">
                                        <div class="ml-2">
                                            <img class="sender-receiver-img" src="{{showUserImage($item->sender_id)}}">
                                        </div>
                                        <div class="col-md-9 ">
                                            <div class="row d-flex flex-row-reverse">
                                                <div class="receiver-conversation">
                                            
                                                    <p >
                                                        {{$item->message}}
                                                    </p>
                                                    
                                                    @if (isset($item->file_name))
                                                        <?php
                                                                $type = explode(".",$item->file_name);
                                                                if (in_array($type[1], $allowed)) {
                                                                    $file_type = 'file';
                                                                } else {
                                                                    $file_type = 'img';
                                                                }
                                                        ?>
                                                        @if ($file_type == 'file')
                                                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">
                                                                {{$item->file_name}} </a>
                                                        @elseif($file_type == 'img')
                                                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">
                                                                <img width="50" src="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}">
                                                            </a>
                                                        @endif
                                                        
                                                    @endif
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
                    <input type="hidden" name="token_id" value="{{$token_id}}">
                    <input type="hidden" name="receiver_id" value="{{$ico_token_details->user_id}}">
                    <div class="row m-4">
                        <div class="w-100">
                            <label class="bg-white p-2" >
                                <input id="input_file" class="text-dark" name="file"  type="file">
                            </label>
                        </div>
                        <textarea id="send-message-box" class="text-dark p-2 send-box-conversation" name="message" type="text"></textarea>
                        <button class="send-button-conversation">{{__('Send')}}</button>

                    </div>
                </form>
            </div>
            <div class="col-6">
                <div class="row">
                    <div class="col-12">
                        <h3 class="text-white">{{__('Token Details')}}:</h3>
                    </div>
                    <div class="col-12">
                        <table class="table table-dark">

                            <tbody class="ico-token-table">
                                @if (isset($ico_token_details))
                                    <tr>
                                        <th class="p-4" scope="row">1</th>
                                        <td class="p-4">{{__('User Email')}}</td>
                                        <td class="p-4">{{isset($ico_token_details->user)?$ico_token_details->user->email:__('Not Found')}}</td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">2</th>
                                        <td class="p-4">{{__('Approved Status')}}</td>
                                        <td class="p-4">
                                            @if ($ico_token_details->approved_status == STATUS_ACCEPTED)
                                                <span class="badge badge-success">
                                                    {{deposit_status(STATUS_ACCEPTED)}}
                                                </span>
                                            @elseif($ico_token_details->approved_status == STATUS_PENDING)
                                                <span class="badge badge-warning">
                                                    {{deposit_status(STATUS_PENDING)}}
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    {{deposit_status(STATUS_REJECTED)}}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">3</th>
                                        <td class="p-4">{{__('Token Name')}}</td>
                                        <td class="p-4">
                                            {{$ico_token_details->token_name}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">4</th>
                                        <td>{{__('Base Coin')}}</td>
                                        <td>
                                            {{$ico_token_details->base_coin}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">5</th>
                                        <td>{{__('Coin Type')}}</td>
                                        <td>
                                            {{$ico_token_details->coin_type}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">6</th>
                                        <td>{{__('Wallet Address')}}</td>
                                        <td>
                                            append_conversation         {{$ico_token_details->wallet_address}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">7</th>
                                        <td>{{__('Gas Limit')}}</td>
                                        <td>
                                            {{$ico_token_details->gas_limit}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="p-4" scope="row">8</th>
                                        <td>{{__('Decimal')}}</td>
                                        <td>
                                            {{$ico_token_details->decimal}}
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                          </table>
                    </div>
                </div>
            </div>
        </div>
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
            url: '{{route('getICOChatConversationStore')}}',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            success: function(response){
                console.log(response);
                if(response.success == true)
                {
                    // $('#conversations_list').empty().html(response.view);
                    $('#send-message-box').val('');
                    $('#input_file').val('');
                    container.scrollTop = container.scrollHeight;
                }
            }
        });
    });

    jQuery(document).ready(function () {
        Pusher.logToConsole = true;
        let senderId = '{{Auth::id()}}';
        var id = '{{Auth::id().'-'.$token_id}}';
        console.log(id)
        Echo.channel('New-Message-'+id)
            .listen('.Conversation', (data) => {
                console.log(data);
                if (data.success == true) {
                    let message_html = '';
                    if (data.data.sender_id != senderId) {
                        message_html += '<div class="row ">';
                        message_html += '<div fileclass="">';
                        message_html += '<img class="sender-receiver-img" src="'+data.data.receiver_img+'">';
                        message_html += '</div>';
                        message_html += '<div class="col-md-9 ml-2">';
                        message_html += '<div class="row">';
                            message_html +='<div class="sender-conversation">';
                    } else {
                        message_html += '<div class="row d-flex flex-row-reverse">';
                        message_html += '<div class="ml-2">';
                        message_html += '<img class="sender-receiver-img" src="'+data.data.sender_img+'">';
                        message_html += '</div>';
                        message_html += '<div class="col-md-9 ">';
                        message_html += '<div class="row d-flex flex-row-reverse">';
                        message_html +='<div class="receiver-conversation">';
                        
                    }
                    message_html +='<p>'+data.data.message+'</p>';
                    if (data.data.file_type === 'file') {
                        message_html += '<a class="text-white" href="'+data.data.file_path_web+'" target="_blank">'+data.data.file_name +'</a>'
                        
                    }else if(data.data.file_type === 'img'){
                        message_html +=data.data.file_path_web;
                    }
                    message_html +='</div></div></div></div>';

                    $('#append_conversation').append(message_html);
                    container.scrollTop = container.scrollHeight;
                }

            })
    });
</script>
@endsection
