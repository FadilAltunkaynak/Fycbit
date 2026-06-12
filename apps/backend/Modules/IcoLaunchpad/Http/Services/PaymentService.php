<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Modules\IcoLaunchpad\Http\Services\PaymentService\PayPal;

class PaymentService
{
    private $type;
    private $class;
    public function __construct($type)
    {
        $this->type = $type;
        $this->class = $this->getPaymentMethod($this->type);
    }

    private function getPaymentMethod($type)
    {
        try {
            $methodsClass = [
                BANK_DEPOSIT => null,
                PAYPAL => PayPal::class,
                SKRILL => null,
                STRIPE => null,
            ];
            return $methodsClass[$type];
        } catch (\Exception $e) {
            storeException("getPaymentMethod:", $e->getMessage());
            return null;
        }
    }

    public function payment()
    {
        //
    }
}
