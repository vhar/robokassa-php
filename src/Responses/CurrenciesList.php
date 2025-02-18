<?php

namespace Vhar\Robokassa\Responses;

use Vhar\Robokassa\Responses\CurrenciesList\CurrencyGroup;

readonly final class CurrenciesList
{
    /** 
     * Cписок валют, доступных для оплаты заказов магазина
     * 
     * @param CurrencyGroup[] $currencyGroup Группы валют. Могут использоваться для более удобного 
     *                                       отображения валют в пользовательском интерфейсе.
     * 
     * @see https://docs.robokassa.ru/xml-interfaces/#currency
     */
    private function __construct(
        public array $currencyGroup,
    ) {
        //
    }

    public static function from(array $params): self
    {
        $groups = [];

        foreach ($params['Groups'] as $group) {
            $groups[] = CurrencyGroup::from($group);
        }

        return new self($groups);
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
