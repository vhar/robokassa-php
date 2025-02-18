<?php

use Vhar\Robokassa\Robokassa;
use Vhar\Robokassa\Common\Invoice;
use Vhar\Robokassa\Common\Merchant;


$merchant = [
    'login'          => 'merchant_login',
    'password1'      => 'password1',
    'password2'      => 'password2',
    'test_password1' => 'test_password1',
    'test_password2' => 'test_password2',
    'is_test'        => true,
    'hashType'       => 'md5',
];

/**
 * Массив данных для фискального чека
 * @see https://docs.robokassa.ru/fiscalization/#example
 */

$receipt = [
    'sno'   => 'usn_income',
    'items' => [
        [
            'sum'            => 1,
            'name'           => 'Сервис 1',
            'quantity'       => 1,
            'payment_method' => 'full_payment',
            'payment_object' => 'service',
            'tax'            => 'none',
        ],
    ]
];

/**
 * Массив данных счета
 * @see https://docs.robokassa.ru/script-parameters/
 */
$orderData = [
    'MerchantLogin' => 'merchant_login',
    'OutSum'      => 1,
    'Description' => 'Счет № 1',
    'Culture'     => 'ru',
    'Receipt'     => $receipt,
    'Shp_2'       => 'Доп Параметр 2',
    'Shp_1'       => 'Доп Параметр 1',
    'ResultUrl2'  => 'https://example.com/result',
    'SuccessUrl2' => 'https://example.com/success',
    'FailUrl2'    => 'https://example.com/fail',
];

$merchant  = Merchant::from($merchant);
$robokassa = new Robokassa($merchant);

$invoice = Invoice::from($orderData);
$link    = $robokassa->createPaymentLink($invoice);

echo $link;
