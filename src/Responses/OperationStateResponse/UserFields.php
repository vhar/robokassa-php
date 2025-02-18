<?php

namespace Vhar\Robokassa\Responses\OperationStateResponse;

use Vhar\Robokassa\Responses\OperationStateResponse\Field;

readonly final class UserFields
{
    /** 
     * @param Field[] $fields Пользовательские параметры, которые были переданы при старте платежа.
     */
    private function __construct(
        public array $fields,
    ) {
        //
    }

    public static function from(array $params): self
    {
        $fields = [];

        if (!empty($params['Field']['Name'])) {
            $params['Field'] = [$params['Field']];
        }

        foreach ($params['Field'] as $field) {
            $fields[] = Field::from($field);
        }

        return new self(
            $fields
        );
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
