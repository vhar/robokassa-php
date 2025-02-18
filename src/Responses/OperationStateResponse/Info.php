<?php

namespace Vhar\Robokassa\Responses\OperationStateResponse;

use Vhar\Robokassa\Responses\PaymentMethodsList\PaymentMethod;

readonly final class Info
{
    /** 
     * Информация о результате выполнения запроса.
     * 
     * @param string        $incCurrLabel  Валюта, которой платил клиент.
     * @param string        $incSum        Сумма, оплаченная клиентом, в единицах валюты IncCurrLabel.
     * @param string        $incAccount    Номер счёта (кошелёк, номер банковской карты) клиента в платежной системе, 
     *                                     через которую производилась оплата.
     * @param PaymentMethod $paymentMethod Описание, если возникла ошибка
     * @param string        $outCurrLabel  Валюта, в которой получает средства магазин.
     * @param string        $outSum        Сумма, зачисленная на счет магазина, в единицах валюты OutCurrLabel.
     * @param string        $opKey         Идентификатор операции.
     * @param string|null   $bankCardRRN   Уникальный идентификатор банковской транзакции.
     * 
     */
    private function __construct(
        public string        $incCurrLabel,
        public string        $incSum,
        public string        $incAccount,
        public PaymentMethod $description,
        public string        $outCurrLabel,
        public string        $outSum,
        public string        $opKey,
        public ?string       $bankCardRRN
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['IncCurrLabel'],
            $params['IncSum'],
            $params['IncAccount'],
            PaymentMethod::from($params['PaymentMethod']),
            $params['OutCurrLabel'],
            $params['OutSum'],
            $params['OpKey'],
            $params['BankCardRRN'] ?? null,
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
