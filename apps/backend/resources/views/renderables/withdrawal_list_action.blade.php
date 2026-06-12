@php
    use App\Enums\WithdrawStatus;
@endphp

<div class="activity-icon">
    <ul style="gap: 10px">
        <li class="deleteuser m-0">
            <a title="{{ __('View') }}" href="#" onclick="viewWithdrawalDetails('withdrawal','{{ $data['id'] }}')"
                class="text-info" style="font-size: 20px">
                <i class="fa fa-eye" aria-hidden="true"></i>
            </a>
        </li>

        @if(in_array($data['status'], [WithdrawStatus::PENDING->value, WithdrawStatus::FAILED->value]))
            <li class="deleteuser m-0">
                <a title="{{ __('Accept') }}" href="#"
                    onclick="acceptRequest('{{ $data['id'] }}', '{{ $data['acceptRoute'] }}', '{{ $data['status'] == WithdrawStatus::FAILED->value ? 0 : $data['status'] }}', '{{ $data['status'] == WithdrawStatus::FAILED->value ? 1 : 0 }}')"
                    class="text-success" style="font-size: 20px">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </a>
            </li>
            @if($data['status'] == WithdrawStatus::FAILED->value)
                <li class="deleteuser m-0">
                    <a title="{{ __('Mark Accept') }}" href="#"
                        onclick="acceptRequest('{{ $data['id'] }}', '{{ $data['asAcceptRoute'] }}', '{{ $data['status'] }}')"
                        class="text-success" style="font-size: 20px">
                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                    </a>
                </li>
            @endif
            <li class="deleteuser m-0">
                <a title="{{ __('Reject') }}" href="#"
                    onclick="rejectRequest('{{ $data['id'] }}', '{{ $data['rejectRoute'] }}')" class="text-danger"
                    style="font-size: 20px">
                    <i class="fa fa-times-circle" aria-hidden="true"></i>
                </a>
            </li>
        @endif
    </ul>
</div>