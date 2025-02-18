<?php

namespace Vhar\Robokassa\Enums;

enum HashTypeEnum: string
{
    case MD5 = 'md5';
    case RIPEMD160 = 'ripemd160';
    case SHA1 = 'sha1';
    case SHA256 = 'sha256';
    case SHA384 = 'sha384';
    case SHA512 = 'sha512';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
