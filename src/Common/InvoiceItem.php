<?php

namespace Vhar\Robokassa\Common;

use Vhar\Robokassa\Enums\PaymentMethodEnum;
use Vhar\Robokassa\Enums\PaymentObjectEnum;
use Vhar\Robokassa\Enums\TaxEnum;

readonly final class InvoiceItem
{
    /**
     * @param string                 $Name             Наименование товара. Строка, максимальная длина 128 символа. 
     *                                                 Если в наименовании товара Вы используете специальные символы, например кавычки, то их обязательно необходимо экранировать.
     * @param int                    $Quantity         Количество товаров
     * @param float                  $Sum              Полная сумма в рублях за итоговое количество данного товара с учетом всех возможных скидок, бонусов и специальных цен. 
     *                                                 Десятичное положительное число: целая часть не более 8 знаков, дробная часть не более 2 знаков.
     * @param float                  $Cost             Полная сумма в рублях за единицу товара с учетом всех возможных скидок, бонусов и специальных цен. 
     *                                                 Десятичное положительное число: целая часть не более 8 знаков, дробная часть не более 2 знаков. 
     *                                                 Параметр можно передавать вместо параметра sum.
     *                                                 При передаче параметра общая сумма товарных позиций рассчитывается по формуле (cost*quantity)=sum.
     * @param TaxEnum $tax                             Это поле устанавливает налоговую ставку в ККТ. Определяется для каждого вида товара по отдельности, 
     *                                                 но за все единицы конкретного товара вместе.
     * @param PaymentMethodEnum|null $PaymentMethod    Признак способа расчёта. Если этот параметр не передан, 
     *                                                 то в чеке будет указано значение параметра по умолчанию из Личного кабинета.
     * @param PaymentObjectEnum|null $PaymentObject    Признак предмета расчёта. Если этот параметр не передан, 
     *                                                 то в чеке будет указано значение параметра по умолчанию из Личного кабинета.
     * @param string|null            $NomenclatureCode Маркировка товара, передаётся в том виде, как она напечатана на упаковке товара. 
     *                                                 Параметр является обязательным только для тех магазинов, которые продают товары подлежащие обязательной маркировке. 
     *                                                 Код маркировки расположен на упаковке товара, рядом со штрих-кодом или в виде QR-кода.
     * 
     * @see https://docs.robokassa.ru/pay-interface/#jwt
     */
    private function __construct(
        public string             $Name,
        public int                $Quantity,
        public ?float             $Sum,
        public TaxEnum            $Tax,
        public float              $Cost,
        public ?PaymentMethodEnum $PaymentMethod,
        public ?PaymentObjectEnum $PaymentObject,
        public ?string            $NomenclatureCode
    ) {
        //
    }
    public static function from(array $params): self
    {
        if (empty($params['Name'])) {
            throw new \InvalidArgumentException('The "Name" parameter is not defined.');
        } elseif (strlen($params['Name']) > 128) {
            throw new \InvalidArgumentException('The "Name" parameter must not exceed 128 characters in length.');
        } else {
            $name = $params['Name'];
        }

        if (empty($params['Quantity'])) {
            $quantity = 1;
        } elseif (!intval($params['Quantity'])) {
            throw new \InvalidArgumentException('The "Quantity" parameter must be integer greater than 1.');
        } else {
            $quantity = intval($params['Quantity']);
        }

        if (empty($params['Cost'])) {
            throw new \InvalidArgumentException('The "Cost" parameter is not defined.');
        } elseif (!preg_match('/^[0-9]{1,8}([.][0-9]{1,2})?$/', $params['Cost'])) {
            throw new \InvalidArgumentException('The "Cost" parameter must be a positive decimal number: the integer part is no more than 8 digits, the fractional part is no more than 2 digits.');
        } else {
            $costString = number_format($params['Cost'], 2, '.', '');
            $cost = floatval($costString);

            $sum = $cost * $quantity;
        }

        if (empty($params['Tax'])) {
            throw new \InvalidArgumentException('The "Tax" parameter is not defined.');
        } else {
            $tax = TaxEnum::from($params['Tax']);
        }

        if (!empty($params['PaymentMethod'])) {
            $payment_method = PaymentMethodEnum::from($params['PaymentMethod']);
        }

        if (!empty($params['PaymentObject'])) {
            $payment_object = PaymentObjectEnum::from($params['PaymentObject']);
        }

        return new self(
            $name,
            $quantity,
            $sum,
            $tax,
            $cost,
            $payment_method ?? null,
            $payment_object ?? null,
            $params['NomenclatureCode'] ?? null
        );
    }

    public static function fromReceiptItem(array $params): self
    {
        if (empty($params['name'])) {
            throw new \InvalidArgumentException('The "name" parameter is not defined.');
        } elseif (strlen($params['name']) > 128) {
            throw new \InvalidArgumentException('The "name" parameter must not exceed 128 characters in length.');
        } else {
            $name = $params['name'];
        }

        if (empty($params['quantity'])) {
            $quantity = 1;
        } elseif (!intval($params['quantity'])) {
            throw new \InvalidArgumentException('The "quantity" parameter must be integer greater than 1.');
        } else {
            $quantity = intval($params['quantity']);
        }

        if (empty($params['sum'])) {
            throw new \InvalidArgumentException('The "sum" parameter is not defined.');
        } elseif (!preg_match('/^[0-9]{1,8}([.][0-9]{1,2})?$/', $params['sum'])) {
            throw new \InvalidArgumentException('The "sum" parameter must be a positive decimal number: the integer part is no more than 8 digits, the fractional part is no more than 2 digits.');
        } else {
            $sumString = number_format($params['sum'], 2, '.', '');
            $sum = floatval($sumString);

            $cost = round($sum / $quantity, 2);
        }

        if (empty($params['tax'])) {
            throw new \InvalidArgumentException('The "tax" parameter is not defined.');
        } else {
            $tax = TaxEnum::from($params['tax']);
        }

        if (!empty($params['payment_method'])) {
            $payment_method = PaymentMethodEnum::from($params['payment_method']);
        }

        if (!empty($params['payment_object'])) {
            $payment_object = PaymentObjectEnum::from($params['payment_object']);
        }

        return new self(
            $name,
            $quantity,
            $sum,
            $tax,
            $cost,
            $payment_method ?? null,
            $payment_object ?? null,
            $params['nomenclature_code'] ?? null
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
