<?

namespace Vhar\Robokassa\Enums;

/**
 * Язык общения с клиентом (в соответствии с ISO 3166-1).
 * 
 * @var string EN Английский
 * @var string RU Русский
 * 
 * @see https://docs.robokassa.ru/script-parameters/#optional
 */
enum LanguageEnum: string
{
    case EN = 'en';
    case RU = 'ru';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
