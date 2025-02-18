<?

namespace Vhar\Robokassa\Enums;

/**
 * Признак предмета расчёта.
 * 
 * @var string COMMODITY             Товар. О реализуемом товаре, за исключением подакцизного товара (наименование и иные сведения, описывающие товар)
 * @var string EXCISE                Подакцизный товар (наименование и иные сведения, описывающие товар)
 * @var string JOB                   Работа. О выполняемой работе (наименование и иные сведения, описывающие работу)
 * @var string SERVICE               Услуга. Об оказываемой услуге (наименование и иные сведения, описывающие услугу)
 * @var string GAMBLING_BET          Ставка азартной игры. О приеме ставок при осуществлении деятельности по проведению азартных игр
 * @var string GAMBLING_PRIZE        Выигрыш азартной игры. О выплате денежных средств в виде выигрыша при осуществлении деятельности по проведению азартных игр
 * @var string LOTTERY               Лотерейный билет. О приеме денежных средств при реализации лотерейных билетов, электронных лотерейных билетов, 
 *                                   приеме лотерейных ставок при осуществлении деятельности по проведению лотерей
 * @var string LOTTERY_PRIZE         Выигрыш лотереи. О выплате денежных средств в виде выигрыша при осуществлении деятельности по проведению лотерей
 * @var string INTELLECTUAL_ACTIVITY Предоставление результатов интеллектуальной деятельности. О предоставлении прав на использование результатов 
 *                                   интеллектуальной деятельности или средств индивидуализации
 * @var string PAYMENT               Платеж. Об авансе, задатке, предоплате, кредите, взносе в счет оплаты, пени, штрафе, вознаграждении, 
 *                                   бонусе и ином аналогичном предмете расчета
 * @var string AGENT_COMMISSION      Агентское вознаграждение. О вознаграждении пользователя, являющегося платежным агентом (субагентом), 
 *                                   банковским платежным агентом (субагентом), комиссионером, поверенным или иным агентом
 * @var string COMPOSITE             Составной предмет расчета. О предмете расчета, состоящем из предметов, 
 *                                   каждому из которых может быть присвоено значение выше перечисленных признаков
 * @var string RESORT_FEE            Курортный сбор
 * @var string ANOTHER               Иной предмет расчета. О предмете расчета, не относящемуся к выше перечисленным предметам расчета
 * @var string PROPERTY_RIGHT        Имущественное право
 * @var string NON_OPERATING_GAIN    Внереализационный доход
 * @var string INSURANCE_PREMIUM     Страховые взносы
 * @var string SALES_TAX             Торговый сбор
 * 
 * @see https://docs.robokassa.ru/fiscalization/#example
 */
enum PaymentObjectEnum: string
{
    case COMMODITY = 'commodity';
    case EXCISE = 'excise';
    case JOB = 'job';
    case SERVICE = 'service';
    case GAMBLING_BET = 'gambling_bet';
    case GAMBLING_PRIZE = 'gambling_prize';
    case LOTTERY = 'lottery';
    case LOTTERY_PRIZE = 'lottery_prize';
    case INTELLECTUAL_ACTIVITY = 'intellectual_activity';
    case PAYMENT = 'payment';
    case AGENT_COMMISSION = 'agent_commission';
    case COMPOSITE = 'composite';
    case RESORT_FEE = 'resort_fee';
    case ANOTHER = 'another';
    case PROPERTY_RIGHT = 'property_right';
    case NON_OPERATING_GAIN = 'non-operating_gain';
    case INSURANCE_PREMIUM = 'insurance_premium';
    case SALES_TAX = 'sales_tax';

    public static function options()
    {
        return array_column(self::cases(), 'value');
    }
}
