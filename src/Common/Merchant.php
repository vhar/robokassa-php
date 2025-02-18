<?php

namespace Vhar\Robokassa\Common;

use Vhar\Robokassa\Enums\HashTypeEnum;

readonly final class Merchant
{
    /**
     * @param string $login     Логин магазина
     * @param string $password1 Пароль 1
     * @param string $password2 Пароль 2
     * @param bool   $isTest    Тестовый режим
     * @param string $hashType  Тип алгоритма хеширования
     */
    private function __construct(
        public string $login,
        public string $password1,
        public string $password2,
        public bool   $isTest,
        public string $hashType,
    ) {
        //
    }

    /**
     * @param array $params Массив данных аккаунта
     * @throws \InvalidArgumentException
     * @return \Vhar\Robokassa\Common\Merchant
     * 
     * @see https://partner.robokassa.ru/Shops
     */
    public static function from(array $params): self
    {
        if (empty($params['login'])) {
            throw new \InvalidArgumentException('The "login" parameter is not defined.');
        } else {
            $login = $params['login'];
        }

        if (empty($params['hashType'])) {
            $hashType = 'sha256';
        } elseif (!in_array($params['hashType'], HashTypeEnum::options())) {
            throw new \InvalidArgumentException('The "hashType" parameter can only the values: ' . implode(', ', HashTypeEnum::options()));
        } else {
            $hashType = $params['hashType'];
        }

        if (!empty($params['is_test']) && $params['is_test'] === true) {
            $isTest = true;
        } else {
            $isTest = false;
        }

        if ($isTest === true) {
            if (empty($params['test_password1'])) {
                throw new \InvalidArgumentException('The "testPassword1" parameter is not defined.');
            }

            if (empty($params['test_password2'])) {
                throw new \InvalidArgumentException('The "testPassword2" parameter is not defined.');
            }

            $password1 = $params['test_password1'];
            $password2 = $params['test_password2'];
        } else {
            if (empty($params['password1'])) {
                throw new \InvalidArgumentException('The "password1" parameter is not defined.');
            }

            if (empty($params['password2'])) {
                throw new \InvalidArgumentException('The "password2" parameter is not defined.');
            }

            $password1 = $params['password1'];
            $password2 = $params['password2'];
        }

        return new self($login, $password1, $password2, $isTest, $hashType);
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
