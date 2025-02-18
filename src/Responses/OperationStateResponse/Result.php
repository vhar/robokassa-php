<?php

namespace Vhar\Robokassa\Responses\OperationStateResponse;

readonly final class Result
{
    /** 
     * Информация о результате выполнения запроса.
     * 
     * @param string      $code        Результат выполнения запроса.
     * @param string|null $description Описание, если возникла ошибка
     */
    private function __construct(
        public string  $code,
        public ?string $description,
    ) {
        //
    }

    public static function from(array $params): self
    {
        return new self(
            $params['Code'],
            $params['Description'] ?? null,

        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
