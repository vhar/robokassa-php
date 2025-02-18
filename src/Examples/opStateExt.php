<?php

use Vhar\Robokassa\Robokassa;
use Vhar\Robokassa\Common\Merchant;

$merchant = [
    'login'     => 'merchant_login',
    'password1' => 'password1',
    'password2' => 'password2',
    'hashType'  => 'md5',
];
$merchant  = Merchant::from($merchant);
$robokassa = new Robokassa($merchant);

$status = $robokassa->opStateExt(2024021501);

print_r($status->toArray());
