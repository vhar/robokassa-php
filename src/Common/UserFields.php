<?php

namespace Vhar\Robokassa\Common;

/** 
 * Дополнительные пользовательские параметры
 * 
 * @see https://docs.robokassa.ru/script-parameters/#extra
 */

readonly final class UserFields
{
    /** 
     * @param array $fields
     */
    private function __construct(
        public array $fields,
    ) {
        //
    }

    public static function from(array $params): self
    {
        $fields = [];

        foreach ($params as $key => $value) {
            if (!preg_match('~^Shp_~iu', $key)) {
                continue;
            }

            $fields[$key] = $value;
        }

        return new self($fields);
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
