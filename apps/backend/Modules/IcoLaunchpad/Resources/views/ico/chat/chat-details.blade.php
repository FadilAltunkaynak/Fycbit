<div class="col-12" id="append_conversation">
    @if (isset($conversation_list))
        @foreach ($conversation_list as $item)
            @if ($item->sender_id != $user_id)
                <div class="row">
                    <div class="sender-conversation">
                        <p >
                            {{$item->message}}
                        </p>
                        @if (isset($item->file_name))
                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">{{$item->file_name}}</a>
                        @endif
                    </div>
                </div>

            @else
                <div class="row  d-flex justify-content-end">
                    <div class="receiver-conversation">
                        <p >
                            {{$item->message}}
                        </p>
                        @if (isset($item->file_name))
                            <a class="text-white" href="{{asset(FILE_ICO_CHAT_VIEW_PATH.$item->file_name)}}" target="_blank">{{$item->file_name}}</a>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>