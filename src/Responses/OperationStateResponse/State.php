<?php

namespace Vhar\Robokassa\Responses\OperationStateResponse;

use Carbon\Carbon;

readonly final class State
{
    /** 
     * Текущее состояние оплаты.
     * 
     * @param string $code        Код текущего состояния операции оплаты счета.
     * @param Carbon $requestDate Дата/время ответа на запрос
     * @param Carbon $stateDate   Дата/время последнего изменения состояния операции.
     */
    private function __construct(
        public string $code,
        public string $requestDate,
        public string $stateDate,
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['Code'],
            Carbon::parse($params['RequestDate']),
            Carbon::parse($params['StateDate']),
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
