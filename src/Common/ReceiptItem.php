<?php

namespace Vhar\Robokassa\Common;

use Vhar\Robokassa\Enums\PaymentMethodEnum;
use Vhar\Robokassa\Enums\PaymentObjectEnum;
use Vhar\Robokassa\Enums\TaxEnum;

readonly final class ReceiptItem
{
    /**
     * @param string                 $name             Наименование товара. Строка, максимальная длина 128 символа. 
     *                                                 Если в наименовании товара Вы используете специальные символы, например кавычки, то их обязательно необходимо экранировать.
     * @param int                    $quantity         Количество товаров
     * @param float                  $sum              Полная сумма в рублях за итоговое количество данного товара с учетом всех возможных скидок, бонусов и специальных цен. 
     *                                                 Десятичное положительное число: целая часть не более 8 знаков, дробная часть не более 2 знаков.
     * @param float|null             $cost             Полная сумма в рублях за единицу товара с учетом всех возможных скидок, бонусов и специальных цен. 
     *                                                 Десятичное положительное число: целая часть не более 8 знаков, дробная часть не более 2 знаков. 
     *                                                 Параметр можно передавать вместо параметра sum.
     *                                                 При передаче параметра общая сумма товарных позиций рассчитывается по формуле (cost*quantity)=sum.
     * @param TaxEnum $tax                             Это поле устанавливает налоговую ставку в ККТ. Определяется для каждого вида товара по отдельности, 
     *                                                 но за все единицы конкретного товара вместе.
     * @param PaymentMethodEnum|null $payment_method   Признак способа расчёта. Если этот параметр не передан, 
     *                                                 то в чеке будет указано значение параметра по умолчанию из Личного кабинета.
     * @param PaymentObjectEnum|null $payment_object   Признак предмета расчёта. Если этот параметр не передан, 
     *                                                 то в чеке будет указано значение параметра по умолчанию из Личного кабинета.
     * @param string|null            $NomenclatureCode Маркировка товара, передаётся в том виде, как она напечатана на упаковке товара. 
     *                                                 Параметр является обязательным только для тех магазинов, которые продают товары подлежащие обязательной маркировке. 
     *                                                 Код маркировки расположен на упаковке товара, рядом со штрих-кодом или в виде QR-кода.
     * 
     * @see https://docs.robokassa.ru/fiscalization/#example
     */
    private function __construct(
        public string             $name,
        public int                $quantity,
        public float              $sum,
        public TaxEnum            $tax,
        public ?float             $cost,
        public ?PaymentMethodEnum $payment_method,
        public ?PaymentObjectEnum $payment_object,
        public ?string            $nomenclature_code
    ) {
        //
    }
    public static function from(array $params): self
    {
        if (empty($params['name'])) {
            throw new \InvalidArgumentException('The "name" parameter is not defined.');
        } elseif (strlen($params['name']) > 128) {
            throw new \InvalidArgumentException('The "name" parameter must not exceed 128 characters in length.');
        } else {
            $name = $params['name'];
        }

        if (empty($params['quantity'])) {
            throw new \InvalidArgumentException('The "quantity" parameter is not defined.');
        } else {
            $quantity = $params['quantity'];
        }

        if (empty($params['sum'])) {
            throw new \InvalidArgumentException('The "sum" parameter is not defined.');
        } elseif (!preg_match('/^[0-9]{1,8}([.][0-9]{1,2})?$/', $params['sum'])) {
            throw new \InvalidArgumentException('The "sum" parameter must be a positive decimal number: the integer part is no more than 8 digits, the fractional part is no more than 2 digits.');
        } else {
            $sum = number_format($params['sum'], 2, '.', '');
        }

        if (empty($params['tax'])) {
            throw new \InvalidArgumentException('The "tax" parameter is not defined.');
        } else {
            $tax = TaxEnum::from($params['tax']);
        }

        if (!empty($params['cost'])) {
            if (!preg_match('/^[0-9]{1,8}([.][0-9]{1,2})?$/', $params['cost'])) {
                throw new \InvalidArgumentException('The "cost" parameter must be a positive decimal number: the integer part is no more than 8 digits, the fractional part is no more than 2 digits.');
            }

            if ($params['cost'] * $params['quantity'] != $params['sum']) {
                throw new \InvalidArgumentException('The multiplication of the "cost" and "quantity" fields does not match the value of the "sum" field.');
            }

            $cost = $params['cost'];
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
            $cost ?? null,
            $payment_method ?? null,
            $payment_object ?? null,
            $params['nomenclature_code'] ?? null
        );
    }

    public static function fromInvoiceItem(array $params): self
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
            $cost = number_format($params['Cost'], 2, '.', '');
            $sum = number_format(($cost * $quantity), 2, '.', '');
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

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
