<?php

namespace Vhar\Robokassa\Enums;

/**
 * Система налогообложения.
 * @var string FULL_PREPAYMENT Предоплата 100%. Полная предварительная оплата до момента передачи предмета расчёта
 * @var string PAYMENT         Предоплата. Частичная предварительная оплата до момента передачи предмета расчёта
 * @var string FULL_PAYMENT    Полный расчёт. Полная оплата, в том числе с учетом аванса (предварительной оплаты) в момент передачи предмета расчёта
 * @var string ADVANCE         Аванс
 * @var string PARTIAL_PAYMENT Частичный расчёт и кредит. Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит
 * @var string CREDIT          Передача в кредит. Передача предмета расчёт без его оплаты в момент его передачи с последующей оплатой в кредит
 * @var string CREDIT_PAYMENT  Оплата кредита. Оплата предмета расчёта после его передачи с оплатой в кредит (оплата кредита)
 * 
 * @see https://docs.robokassa.ru/fiscalization/#example
 */

enum PaymentMethodEnum: string
{
    case FULL_PREPAYMENT = 'full_prepayment';
    case PAYMENT = 'prepayment';
    case FULL_PAYMENT = 'full_payment';
    case ADVANCE = 'advance';
    case PARTIAL_PAYMENT = 'partial_payment';
    case CREDIT = 'credit';
    case CREDIT_PAYMENT = 'credit_payment';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
