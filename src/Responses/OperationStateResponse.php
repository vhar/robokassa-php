<?php

namespace Vhar\Robokassa\Responses;

use Vhar\Robokassa\Responses\OperationStateResponse\Info;
use Vhar\Robokassa\Responses\OperationStateResponse\State;
use Vhar\Robokassa\Responses\OperationStateResponse\Result;
use Vhar\Robokassa\Responses\OperationStateResponse\UserFields;


readonly final class OperationStateResponse
{
    /** 
     * Расширенная информация об операции и ее текущего состояния
     * 
     * @param Result          $result     Информация о результате выполнения запроса.
     * @param State|null      $state      Текущее состояние оплаты.
     * @param Info|null       $info       Информация об операции оплаты счета.
     * @param UserFields|null $userFields Пользовательские параметры, которые были переданы при старте платежа.
     * 
     * @see https://docs.robokassa.ru/xml-interfaces/#account
     */
    private function __construct(
        public Result $result,
        public ?State $state,
        public ?Info  $info,
        public ?UserFields $userFields

    ) {
        //
    }

    public static function from(array $params): self
    {
        if (!empty($params['State'])) {
            $state = State::from($params['State']);
        }

        if (!empty($params['Info'])) {
            $info = Info::from($params['Info']);
        }
        if (!empty($params['UserFields'])) {
            $userFields = UserFields::from($params['UserFields']);
        }

        return new self(
            Result::from($params['Result']),
            $state ?? null,
            $info ?? null,
            $userFields ?? null
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
