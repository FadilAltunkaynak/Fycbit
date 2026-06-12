@php($user = auth()->user())
    <div class="support-left-user py-4">
        <div class="support-user-img text-center mb-4">
            @if (isset($user->photo))
                <img src="{{show_image($user->id,'user')}}" alt="" />
            @else
                <img src="{{asset('assets/modules/knowledgebase/image/user.jpeg')}}" alt="" />
            @endif
            <h6 class="my-2">{{ isset($user) ? $user->first_name.' '.$user->last_name : '' }}</h4>
            <small class="fw-bolder text-secondary">{{ isset($user) ? $user->email : '' }}</small>
        </div>
        <ul class="user-item-list">
            <li><a href="{{route('support_dashboard')}}">{{ __('Dashboard') }}</a></li>
            <li><a href="{{settings('exchange_url')}}" target="__blank">{{ __('Exchange') }}</a></li>
            <li><a href="{{route('knowledgebase_user_index')}}">{{ __('Knowledge') }}</a></li>
            <li><a href="{{ route('support_create_ticket') }}">{{ __('Create Ticket') }}</a></li>
            <li><a href="{{ url()->current().'?status='.TICKET_STATUS_PENDING }}">{{ __('Pending Ticket') }} ({{$ticket_count['total_pending_ticket_count']??0}})</a></li>
            <li><a href="{{ url()->current().'?status='.TICKET_STATUS_OPEN }}">{{ __('Open Ticket') }} ({{$ticket_count['total_open_ticket_count']??0}})</a></li>
            <li><a href="{{ url()->current().'?status='.TICKET_STATUS_CLOSE }}">{{ __('Close Ticket') }} ({{$ticket_count['total_close_ticket_count']??0}})</a></li>
            <li><a href="{{ url()->current().'?status='.TICKET_STATUS_CLOSE_FOREAVER }}">{{ __('Close Forever Ticket') }} ({{$ticket_count['total_close_forever_ticket_count']??0}})</a></li>
        </ul>
    </div>
