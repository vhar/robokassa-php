<?php

namespace Vhar\Robokassa\Responses;

readonly final class CreatedInvoice
{
    /** 
     * Ответ о созданном счета
     * 
     * @param string $id        GUID счета из платежной системы
     * @param int    $invId     Номер счета. Если передавался при создании, 
     *                          тот же или автоматически сгенерированный платежной системой 
     * @param string $url       URL адрес формы оплаты
     * @param bool   $isSuccess Статус создания счета
     */
    private function __construct(
        public string $id,
        public int    $invId,
        public string $url,
        public bool   $isSuccess,

    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['id'],
            $params['invId'],
            $params['url'],
            $params['isSuccess'],
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
