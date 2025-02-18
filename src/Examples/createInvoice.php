<?php

use Carbon\Carbon;
use Vhar\Robokassa\Robokassa;
use Vhar\Robokassa\Common\Merchant;
use Vhar\Robokassa\Common\InvoiceJWT;


$merchant = [
    'login'     => 'merchant_login',
    'password1' => 'password1',
    'password2' => 'password2',
    'hashType'  => 'md5',
];

/**
 * Массив товарных позиций
 * @see https://docs.robokassa.ru/fiscalization/#example
 */
$invoiceItems = [
    [
        'Cost'             => 1,
        'Name'             => 'Сервис 1',
        'Quantity'         => 1,
        'PaymentMethod'    => 'full_payment',
        'PaymentObject'    => 'service',
        'Tax'              => 'none',
        'NomenclatureCode' => 'IYVITCUR%XE^$X%C^T&VITC^RX&%ERC^TIRX%&ERCUITRXE&ZX%R^CTIR^XUE%ZN1m9E+1¦?5O?6¦?168'
    ],
];

/**
 * Массив данных счета
 * @see https://docs.robokassa.ru/pay-interface/#jwt
 */
$orderData = [
    'MerchantLogin' => 'merchant_login',
    'InvoiceType'  => 'OneTime',
    'OutSum'       => 1,
    'Description'  => 'Счет № 1',
    'ExpirationDate' => Carbon::now()->timezone('Europe/Moscow')->addMinutes(5)->format("Y-m-d\\TH:i:s.u"),
    'MerchantComments' => 'Оплатить до ' . Carbon::now()->timezone('Europe/Moscow')->addMinutes(5)->format("Y-m-d H:i:s"),
    'Culture'      => 'ru',
    'ResultUrl2'   => 'https://example.com/result',
    'SuccessUrl2'  => 'https://example.com/success',
    'FailUrl2'     => 'https://example.com/fail',
    'InvoiceItems' => $invoiceItems
];

$merchant  = Merchant::from($merchant);
$robokassa = new Robokassa($merchant);

$invoice  = InvoiceJWT::from($orderData);
$response = $robokassa->createInvoice($invoice);

print_r($response->toArray());
