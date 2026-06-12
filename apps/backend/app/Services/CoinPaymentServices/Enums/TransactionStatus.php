<?php

namespace App\Services\CoinPaymentServices\Enums;

use App\Services\CoinPaymentServices\Responses\NotifierResponse\CoinPaymentWebhookResponse;

enum TransactionStatus: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Failed = 'Failed';
    case Cancelled = 'Cancelled';
    case ConfirmedOnBlockchain = 'ConfirmedOnBlockchain';
    case Expired = 'Expired';
    case PendingReceive = 'PendingReceive';

    /**
     * Check Complete Status
     */
    public static function isComplete(CoinPaymentWebhookResponse $transaction): bool
    {
        return match ($transaction->transactionStatus) {
            self::Completed->value => true,
            self::ConfirmedOnBlockchain->value => true,
            self::PendingReceive->value => $transaction->confirmations > 1,
            default => false,
        };
    }
}
