<?php

namespace Vhar\Robokassa\Common;

use Vhar\Robokassa\Enums\SnoEnum;
use Vhar\Robokassa\Common\ReceiptItem;

readonly final class Receipt
{
    /** 
     * Данные для фискального чека
     * 
     * @param ReceiptItem[] $items Массив данных о позициях чека
     * @param SnoEnum|null  $sno   Система налогообложения. Необязательное поле, если у организации имеется только один тип налогообложения.
     *                             Данный параметр обзятально задается в личном кабинете магазина)
     * 
     * @see https://docs.robokassa.ru/fiscalization/#example
     */
    private function __construct(
        public array    $items,
        public ?SnoEnum $sno,
    ) {
        //
    }

    public static function from(array $params): self
    {
        if (empty($params['items'])) {
            throw new \InvalidArgumentException('The "items" parameter is not defined.');
        } else {
            $items = [];

            foreach ($params['items'] as $item) {
                $items[] = ReceiptItem::from($item);
            }
        }

        if (!empty($params['sno'])) {
            $sno = SnoEnum::tryFrom($params['sno']);
        }

        return new self($items, $sno ?? null);
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
