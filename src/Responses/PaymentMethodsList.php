<?php

namespace Vhar\Robokassa\Responses;

use Vhar\Robokassa\Responses\PaymentMethodsList\PaymentMethod;

readonly final class PaymentMethodsList
{
    /** 
     * Доступные методы оплаты
     * 
     * @param PaymentMethod[] $paymentMethods Массив способов оплаты
     */
    private function __construct(
        public array $paymentMethods,
    ) {
        //
    }

    public static function from(array $params): self
    {
        $methods = [];

        foreach ($params['Methods'] as $method) {
            $methods[] = PaymentMethod::from($method);
        }

        return new self($methods);
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
