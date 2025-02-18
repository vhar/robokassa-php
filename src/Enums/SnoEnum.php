<?

namespace Vhar\Robokassa\Enums;

/**
 * Система налогообложения.
 * @var string OSN                Общая СН
 * @var string USN_INCOME         Упрощенная СН (доходы)
 * @var string USN_INCOME_OUTCOME Упрощенная СН (доходы минус расходы)
 * @var string ESN                Единый сельскохозяйственный налог
 * @var string PATENT             Патентная СН
 * 
 * @see 
 */
enum SnoEnum: string
{
    case OSN = 'osn';
    case USN_INCOME = 'usn_income';
    case USN_INCOME_OUTCOME = 'usn_income_outcome';
    case ESN = 'esn';
    case PATENT = 'patent';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
