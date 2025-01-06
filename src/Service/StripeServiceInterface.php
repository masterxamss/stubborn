<?php

namespace App\Service;


interface StripeServiceInterface
{
    public function createPayment($cart, $orderId): string;
    public function getPaymentId(): mixed;
    public function getPaymentOrder(): mixed;
}
