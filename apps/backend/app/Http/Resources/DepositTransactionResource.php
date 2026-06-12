<?php

namespace App\Http\Resources;

use App\Model\Network;
use App\Traits\NumberFormatTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class DepositTransactionResource extends JsonResource
{
    use NumberFormatTrait;

    public function toArray($request)
    {

        $network = Network::find($this->network_id);
        $networkName = $network ? $network->name : 'Not Found';

        return [
            'id' => $this->id,
            'address' => $this->address,
            'fees' => $this->truncateNum($this->address),
            'sender_wallet_id' => $this->sender_wallet_id,
            'receiver_wallet_id' => $this->receiver_wallet_id,
            'address_type' => $this->address_type,
            'coin_id' => $this->coin_id,
            'coin_type' => $this->coin_type,
            'network_id' => $this->network_id,
            'network' => $networkName,
            'amount' => truncate_num($this->amount),
            'txId' => $this->transaction_id,
            'status' => $this->status,
            'confirmations' => $this->confirmations,
            'from_address' => $this->from_address,
            'updated_by' => $this->updated_by,
            'network_type' => $this->network_type,
            'is_admin_receive' => $this->is_admin_receive,
            'received_amount' => $this->truncateNum($this->received_amount),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
