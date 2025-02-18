<?php

namespace Vhar\Robokassa\Responses\OperationStateResponse;

readonly final class Field
{
    /** 
     * Если ссылка для оплаты счета была свормирована методом createInvoice, 
     * платежная система всегда вернет пользовательски параметр "shp_interface" со значением "InvoiceService.WebApi".
     * 
     * @param string|null $name  Имя пользовательского параметра.
     * @param string|null $value Значение пользовательского параметра.
     */
    private function __construct(
        public ?string $name,
        public ?string $value,
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['Name'] ?? null,
            urldecode($params['Value'] ?? null),
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
