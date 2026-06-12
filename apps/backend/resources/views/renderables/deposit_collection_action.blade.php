@php
    use App\Enums\DepositCollectionStatus;
@endphp

<div class="activity-icon">
    <ul style="gap: 10px">
        @if($data['status'] == DepositCollectionStatus::PENDING->value)
            <li class="deleteuser m-0">
                <a title="Accept" href="#" onclick="acceptRequest('{{ $data['acceptRoute'] }}')" class="text-success"
                    style="font-size: 20px">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </a>
            </li>
        @endif
        @if (!empty($data['rejectNote']))
            <li class="deleteuser m-0">
                <a href="#" data-reject-note="{{ $data['rejectNote'] }}" onclick="detailsRequest(this)" class="text-danger"
                    style="font-size: 20px">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </a>
            </li>
        @endif
    </ul>
</div>