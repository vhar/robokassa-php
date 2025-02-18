<?php

namespace Vhar\Robokassa\Responses\CurrenciesList;

use Vhar\Robokassa\Responses\CurrenciesList\Currency;

readonly final class CurrencyGroup
{
    /** 
     * Группа валют, доступных для оплаты заказов магазина
     * 
     * @param string     $code        Код группы
     * @param string     $description Текстовое описание группы
     * @param Currency[] $currencies  Валюты, входящие в группу
     */
    private function __construct(
        public string $code,
        public string $description,
        public array  $currencies,
    ) {
        //
    }

    public static function from(array $params): self
    {
        $currencies = [];
        foreach ($params['Items']['Currency'] as $currency) {

            $currencies[] = Currency::from($currency);
        }

        return new self(
            $params['Code'],
            $params['Description'],
            $currencies
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
