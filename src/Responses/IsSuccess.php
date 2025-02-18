<?php

namespace Vhar\Robokassa\Responses;

readonly final class IsSuccess
{
    /** 
     * @param bool        $isSuccess Статус операции
     * @param string|null $message   Сообщение
     */
    private function __construct(
        public bool    $isSuccess,
        public ?string $message,

    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['isSuccess'],
            $params['message'] ?? null,
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
