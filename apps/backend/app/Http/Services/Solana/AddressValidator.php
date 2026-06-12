<?php

namespace App\Http\Services\Solana;

use StephenHill\Base58;

class AddressValidator
{
    public function __construct(private Base58 $base58Decoder) {}

    public function validateAddress(string $address): bool
    {
        try {
            return strlen($this->base58Decoder->decode($address)) === 32;
        } catch (\Exception $e) {
            return false;
        }
    }
}
