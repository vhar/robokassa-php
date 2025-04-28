<?php

namespace Vhar\Robokassa\Enums;

/**
 * Тип ссылки, одноразовая или многоразовая.
 * 
 * @var string ONE_TIME Одноразовая ссылка (счет выставляемый в ЛКК)
 * @var string REUSIBLE Многоразовая ссылка
 * 
 * @see https://docs.robokassa.ru/pay-interface/#jwt
 */

enum InvoiceTypeEnum: string
{
    case ONE_TIME = 'OneTime';
    case REUSIBLE = 'Reusable';


    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
