<?php

namespace Vhar\Robokassa\Responses\CurrenciesList;

readonly final class Currency
{
    /** 
     * 
     * @param string      $label    Код валюты
     * @param string      $name     Наименование валюты
     * @param string      $alias    Псевдоним наименования валюты
     * @param string|null $minValue Минимальная стоимость заказа
     * @param string|null $maxValue Максмальная стоимость заказа
     */
    private function __construct(
        public string  $label,
        public string  $name,
        public string  $alias,
        public ?string $minValue,
        public ?string $maxValue,
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['Label'],
            $params['Name'],
            $params['Alias'],
            $params['MinValue'] ?? null,
            $params['MaxValue'] ?? null,
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
