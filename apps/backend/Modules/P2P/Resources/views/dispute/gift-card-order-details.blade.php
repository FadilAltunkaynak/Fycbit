@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
    <link rel="stylesheet" href="{{asset('assets/admin/css/chat.css')}}">
@endsection
@section('sidebar')
    @include('p2p::layouts.sidebar', ['menu' => 'gift_card', 'sub_menu' => 'gift_card_dispute_list'])
@endsection
@section('content')
@php($user = auth()->user())
<!-- breadcrumb -->
<div class="custom-breadcrumb">
    <div class="row">
        <div class="col-6">
            <ul>
                <li class="active-item">{{$title ?? __('Disputed List')}}</li>
            </ul>
        </div>
        <div class="col-6 d-flex justify-content-end">

            @if (
                isset($order_details->dispute_details) && isset($order_details->dispute_details->assigned_admin)
                && $order_details->dispute_details->assigned_admin == $user->id
                && $order_details->dispute_details->status == STATUS_INACTIVE
            )
                            @if(isset($order_details->payment_currency_type) && $order_details->payment_currency_type == PAYMENT_CURRENCY_FIAT)
                                <button type="button" class="btn btn-warning mr-2" class="btn btn-warning" data-toggle="modal"
                                    data-target="#refund_modal">
                                    {{ __("Refund") }}
                                </button>
                            @endif
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#release_modal">
                                {{ __("Release") }}
                            </button>
            @elseif(isset($order_details->dispute_details) && !isset($order_details->dispute_details->assigned_admin))
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assign_to">
                    {{ __("Assign To") }}
                </button>
            @endif
        </div>
    </div>
</div>
<!-- /breadcrumb -->

<div class="user-management">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white">
                        {{ __('Order Details')}}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if (isset($order_details))
                            <div class="col-md-12">
                                <div class="profile-info-table">
                                    <ul>
                                        <li>
                                            <span>{{__('Buyer')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ isset($order_details->buyer) ? $order_details->buyer->first_name . ' ' . $order_details->buyer->last_name : __('N/A') }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Seller')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ isset($order_details->seller) ? $order_details->seller->first_name . ' ' . $order_details->seller->last_name : __('N/A') }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Order ID')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $order_details->uid }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Currency Type')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $order_details->currency_type }}</strong></span>
                                        </li>
                                        <li>
                                            <span>{{__('Price')}}</span>
                                            <span class="dot">:</span>
                                            <span><strong>{{ $order_details->price }}</strong></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="profile-info-table">
                                    <ul>
                                        <li>
                                            <span>{{__('Order Status')}}</span>
                                            <span class="dot">:</span>
                                            <span class="p-3">
                                                {{tradeStatusListP2P($order_details->status)}}
                                            </span>
                                        </li>
                                        <li>
                                            <span>{{__('Payment Status')}}</span>
                                            <span class="dot">:</span>
                                            @if ($order_details->payment_status == STATUS_ACTIVE)
                                                <span class="btn btn-sm btn-success p-3">
                                                    {{ __('Success')}}
                                                </span>
                                            @else
                                                <span class="btn btn-sm btn-warning p-3">
                                                    {{ __('Pending')}}
                                                </span>
                                            @endif
                                        </li>
                                        <li>
                                            <span>{{__('Transaction Id')}}</span>
                                            <span class="dot">:</span>
                                            <span>
                                                {{ $order_details->transaction_id}}
                                            </span>
                                        </li>
                                        <li>
                                            <span>{{__('Reported by')}}</span>
                                            <span class="dot">:</span>
                                            <span>
                                                {{isset($order_details->reporting_user) ? $order_details->reporting_user->first_name . ' ' . $order_details->reporting_user->last_name : __('N/A')}}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <span> {{__('Not Found')}}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (
            isset($order_details->dispute_details) && isset($order_details->dispute_details->assigned_admin) &&
            $order_details->dispute_details->assigned_admin == $user->id
        )
                    <div class="col-6">
                        <div class="row chat-card" id="conversations_list">
                            <div class="col-12" id="append_conversation">
                                @if (isset($conversation_list))
                                    @foreach ($conversation_list as $item)
                                        @if ($item->sender_id != $user->id)
                                            <div class="row mb-3">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <img class="sender-receiver-img" src="{{showUserImageP2P($item->sender_id)}}">
                                                    <!-- <span>
                                                                            {{showUserNickNameP2P($item->sender_id)}}
                                                                        </span> -->
                                                </div>
                                                <div class="col-md-9 ml-2">
                                                    <div class="row">
                                                        <div class="sender-conversation">
                                                            <p>
                                                                {!!$item->message!!}
                                                            </p>
                                                            @if (isset($item->file))
                                                                <a class="text-white"
                                                                    href="{{filePathP2P(CONVERSATION_ATTACHMENT_PATH, $item->file)}}"
                                                                    target="_blank">
                                                                    <img width="50" src="{{filePathP2P(CONVERSATION_ATTACHMENT_PATH, $item->file)}}">
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row d-flex flex-row-reverse mb-3">
                                                <div class="ml-2 d-flex flex-column align-items-center justify-content-center">
                                                    <img class="sender-receiver-img" src="{{showUserImageP2P($item->sender_id)}}">
                                                    <!-- <span>
                                                                            {{showUserNickNameP2P($item->sender_id)}}
                                                                        </span> -->
                                                </div>
                                                <div class="col-md-9 ">
                                                    <div class="row d-flex flex-row-reverse">
                                                        <div class="receiver-conversation">

                                                            <p>
                                                                {!!$item->message!!}
                                                            </p>

                                                            @if (isset($item->file))

                                                                <a class="text-white"
                                                                    href="{{filePathP2P(CONVERSATION_ATTACHMENT_PATH, $item->file)}}"
                                                                    target="_blank">
                                                                    <img width="50" src="{{filePathP2P(CONVERSATION_ATTACHMENT_PATH, $item->file)}}">
                                                                </a>

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
                            <input type="hidden" name="gift_card_order_id" value="{{$order_details->id}}">
                            <div class="row m-4">
                                <div class="w-100">
                                    <label class="bg-white p-2">
                                        <input id="input_file" class="text-dark" name="file" type="file">
                                    </label>
                                </div>
                                <input id="send-message-box" class="text-dark p-2 send-box-conversation" name="message"
                                    type="text" />
                                <button class="send-button-conversation">{{__('Send')}}</button>

                            </div>
                        </form>
                    </div>
        @endif

    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="text-white">
                    {{ __('Dispute Details')}}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if (isset($order_details->dispute_details))

                        <div class="col-md-6">
                            <div class="profile-info-table">
                                <ul>
                                    <li>
                                        <span>{{__('Assigned By')}}</span>
                                        <span class="dot">:</span>
                                        <span><strong>{{ showUserNickNameP2P($order_details->dispute_details->updated_by) }}</strong></span>
                                    </li>
                                    <li>
                                        <span>{{__('Assigned To')}}</span>
                                        <span class="dot">:</span>
                                        <span><strong>{{ showUserNickNameP2P($order_details->dispute_details->assigned_admin) }}</strong></span>
                                    </li>
                                    <li>
                                        <span>{{__('Dispute Status')}}</span>
                                        <span class="dot">:</span>
                                        <span>
                                            @if ($order_details->dispute_details->status == STATUS_ACTIVE)
                                                <span class="btn btn-sm btn-success p-3">
                                                    {{ __('Success')}}
                                                </span>
                                            @else
                                                <span class="btn btn-sm btn-warning p-3">
                                                    {{ __('Pending')}}
                                                </span>
                                            @endif
                                        </span>
                                    </li>
                                    <li>
                                        <span>{{__('Reason Subject')}}</span>
                                        <span class="dot">:</span>
                                        <span><strong>{{ $order_details->dispute_details->reason_heading }}</strong></span>
                                    </li>
                                    <li>
                                        <span>{{__('Reason Details')}}</span>
                                        <span class="dot">:</span>
                                        <span><strong>{{ $order_details->dispute_details->details }}</strong></span>
                                    </li>
                                    <li>
                                        <span>{{__('Attachment')}}</span>
                                        <span class="dot">:</span>
                                        <span>
                                            <a href="{{asset('storage') . '/' . PAYMENT_SLIP_PATH . '/' . $order_details->dispute_details->image}}"
                                                target="_blank">
                                                <img style="width:40px;height:40px;"
                                                    src="{{asset('storage') . '/' . PAYMENT_SLIP_PATH . '/' . $order_details->dispute_details->image}}"
                                                    alt="">
                                            </a>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    @else
                        <span> {{__('Not Found')}}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!--Assign to Modal -->
<div class="modal fade" id="assign_to" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('Assign Dispute Details')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @php($admin_list = adminListP2P())
            <form action="{{route('giftCardaAssignDisputeDetails')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="dispute_uid"
                            value="{{isset($order_details->dispute_details) ? $order_details->dispute_details->uid : '0'}}">
                        <div class="col-md-12">
                            <label for="">{{__('Select Employee')}}</label>
                            <div class="cp-select-area">
                                <select name="employee_id" class="selectpicker" data-width="100%"
                                    data-live-search="true" data-actions-box="true">
                                    @if(isset($admin_list))
                                        @foreach($admin_list as $admin_details)
                                            <option value="{{ $admin_details->id }}">
                                                {{ $admin_details->first_name . ' ' . $admin_details->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--refund  Modal -->
<div class="modal fade" id="refund_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('Refund Dispute Order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('giftCardRefundDisputeDetails')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="dispute_uid"
                            value="{{isset($order_details->dispute_details) ? $order_details->dispute_details->uid : '0'}}">
                        <div class="col-md-12">
                            <h4 class="text-white">
                                {{__('Are you sure to refund this order?')}}
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--release  Modal -->
<div class="modal fade" id="release_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('Release Dispute Order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('giftCardReleaseDisputeDetails')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="dispute_uid"
                            value="{{isset($order_details->dispute_details) ? $order_details->dispute_details->uid : '0'}}">
                        <div class="col-md-12">
                            <h4 class="text-white">
                                {{__('Are you sure to release this order?')}}
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
    <script>
        (function ($) {
            "use strict";
            let container = document.querySelector('#conversations_list');
            $(document).ready(function () {
                container.scrollTop = container.scrollHeight;
            });

            $("#send_message_form").on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: '{{route('giftCardSendMessage')}}',
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: 'application/json',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {

                        // console.log(response);
                        if (response.success == true) {
                            $('#send-message-box').val('');
                            $('#input_file').val('');
                        } else {
                            $('#send-message-box').val('');
                            $('#input_file').val('');

                            VanillaToasts.create({
                                text: response.message,
                                type: 'warning',
                                timeout: 4000
                            });
                        }
                    },
                    error: function (response) {
                        var errors = response.responseJSON.errors;
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                var error = errors[key][0];

                                VanillaToasts.create({
                                    text: error,
                                    type: 'warning',
                                    timeout: 4000
                                });
                            }
                        }

                    }
                });
            });

            jQuery(document).ready(function () {
                Pusher.logToConsole = true;
                let senderId = '{{Auth::id()}}';
                var id = '{{Auth::id() . '-' . $order_details->uid}}';
                console.log(id, "ddd");
                Echo.channel('New-Message-' + id)
                    .listen('.Conversation', (data) => {
                        console.log(data);
                        if (data.success == true) {
                            let html_view = '';
                            if (data.success == true) {
                                var nickname = data.data.user.nickname !== null ? data.data.user.nickname : 'Not Found';
                                if (data.data.user_id != senderId) {

                                    html_view += '<div class="row mb-3"><div class="d-flex flex-column align-items-center justify-content-center">';
                                    html_view += '<img class="sender-receiver-img" src="' + data.data.sender_image_link + '"></div><div class="col-md-9 ml-2"><div class="row">';
                                    html_view += '<div class="receiver-conversation">';
                                    if (data.data.message != null) {
                                        html_view += '<p >' + data.data.message + '</p>';
                                    }
                                    if (data.data.file_path !== '') {
                                        html_view += '<a class="text-white m-1" href="' + data.data.file_path + '" target="_blank"><img width="50" src="' + data.data.file_path + '"></a>'

                                    }
                                    html_view += '</div></div></div></div>';
                                } else {
                                    html_view += '<div class="row d-flex flex-row-reverse mb-3"><div class="ml-2 d-flex flex-column align-items-center justify-content-center">';
                                    html_view += '<img class="sender-receiver-img" src="' + data.data.sender_image_link + '"></div><div class="col-md-9"><div class="row d-flex flex-row-reverse">';
                                    html_view += '<div class="sender-conversation">';
                                    if (data.data.message != null) {
                                        html_view += '<p >' + data.data.message + '</p>';
                                    }
                                    if (data.data.file_path !== '') {

                                        html_view += '<a class="text-white m-1" href="' + data.data.file_path + '" target="_blank"><img width="50" src="' + data.data.file_path + '"></a>'

                                    }
                                    html_view += '</div></div></div></div>';
                                }
                                $('#append_conversation').append(html_view);
                                container.scrollTop = container.scrollHeight;

                            }
                        }
                    })
            });
        })(jQuery);
    </script>
@endsection