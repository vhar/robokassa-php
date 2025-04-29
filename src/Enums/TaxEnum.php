<?php

namespace Vhar\Robokassa\Enums;

/**
 * Налоговая ставка в ККТ
 * 
 * @var string VAT0   НДС по ставке 0%
 * @var string VAT5   НДС по ставке 5%
 * @var string VAT105 НДС чека по расчетной ставке 5/105
 * @var string VAT7   НДС по ставке 7%
 * @var string VAT107 НДС чека по расчетной ставке 7/107
 * @var string VAT10  НДС чека по ставке 10%
 * @var string VAT110 НДС чека по расчетной ставке 10/110
 * @var string VAT20  НДС чека по ставке 20%
 * @var string VAT120 НДС чека по расчетной ставке 20/120
 * 
 * @see https://docs.robokassa.ru/fiscalization/#example
 */
enum TaxEnum: string
{
    case NONE = 'none';
    case VAT0 = 'vat0';
    case VAT5 = 'vat5';
    case VAT105 = 'vat105';
    case VAT7 = 'vat7';
    case VAT107 = 'vat107';
    case VAT10 = 'vat10';
    case VAT110 = 'vat110';
    case VAT20 = 'vat20';
    case VAT120 = 'vat120';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
