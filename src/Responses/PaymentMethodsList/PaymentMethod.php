<?php

namespace Vhar\Robokassa\Responses\PaymentMethodsList;

readonly final class PaymentMethod
{
    /** 
     * @param string      $code        Код метода оплаты
     * @param string|null $description Описание метода оплаты
     */
    private function __construct(
        public string  $code,
        public ?string $description,
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['Code'],
            $params['Description'] ?? null
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
