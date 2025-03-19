<?php

namespace Vhar\Robokassa\Common;

use Carbon\Carbon;
use Vhar\Robokassa\Enums\LanguageEnum;
use Vhar\Robokassa\Common\UserFields;

/**
 * @see https://docs.robokassa.ru/script-pay/
 */
final class Invoice
{
    /**
     * Идентификатор магазина в Robokassa, значение свойства login класса Merchant.
     *
     * @var string
     */
    public readonly string $MerchantLogin;

    /**
     * Номер счета в магазине. Значение этого параметра должно быть уникальным для каждой оплаты.
     * Может принимать значения от 1 до 2147483647 (2 - 1).
     */
    public readonly int $InvoiceID;

    /**
     * Требуемая к получению сумма (буквально — стоимость заказа, сделанного клиентом).
     * Формат представления — число, разделитель — точка, например: 123.45.
     * Сумма должна быть указана в рублях.
     *
     * @var float
     */
    public readonly float $OutSum;

    /**
     * Описание покупки, можно использовать только символы английского или русского алфавита, цифры и знаки препинания. 
     * Максимальная длина — 100 символов.
     * Эта информация отображается в интерфейсе Robokassa на платежной странице и в личном кабинете.
     */
    public readonly string $Description;

    /**
     * Дополнительные свойства
     * 
     * @var array
     */
    public array $additionalFields;

    /**
     * @param array $params
     * @throws \InvalidArgumentException
     */
    private function __construct(array $params)
    {
        if (empty($params['MerchantLogin'])) {
            throw new \InvalidArgumentException('The "MerchantLogin" parameter is not defined.');
        } else {
            $this->MerchantLogin = $params['MerchantLogin'];
        }

        if (empty($params['InvoiceID'])) {
            $this->InvoiceID = 0;
        } elseif (!is_int($params['InvoiceID'])) {
            throw new \InvalidArgumentException('The "InvoiceID" parameter must be integer.');
        } else {
            $this->InvoiceID = $params['InvoiceID'];
        }

        if (empty($params['OutSum'])) {
            throw new \InvalidArgumentException('The "OutSum" parameter is not defined.');
        } elseif (!preg_match('/^[0-9]+([.][0-9]{1,2})?$/', $params['OutSum'])) {
            throw new \InvalidArgumentException('The "OutSum" parameter must be a positive decimal number.');
        } else {
            $this->OutSum = floatval(number_format($params['OutSum'], 2, '.', ''));
        }

        if (empty($params['Description'])) {
            throw new \InvalidArgumentException('The "Description" parameter is not defined.');
        } elseif (mb_strlen($params['Description']) > 100) {
            throw new \InvalidArgumentException('The "Description" parameter must not exceed 100 characters in length.');
        } else {
            $this->Description = $params['Description'];
        }
    }

    /**
     * @param array $params
     * 
     * @throws \InvalidArgumentException
     * 
     * @see https://docs.robokassa.ru/script-parameters/
     */
    public static function from(array $params): self
    {
        $invoice = new self($params);

        /**
         * Предлагаемый способ оплаты. Тот вариант оплаты, который Вы рекомендуете использовать своим покупателям 
         * (если не задано, то по умолчанию открывается оплата Банковской картой). 
         * Если параметр указан, то покупатель при переходе на сайт Robokassa попадёт на страницу оплаты с выбранным способом оплаты.
         */
        if (!empty($params['IncCurrLabel'])) {
            $invoice->additionalFields['IncCurrLabel'] = $params['IncCurrLabel'];
        }

        /**
         * Предлагаемый способ оплаты. В отличии от IncCurrLabel дает возможность передать сразу несколько способов оплаты. 
         * В таком случае нужно передать несколько параметров PaymentMethods с разными значениями.
         */
        if (!empty($params['PaymentMethods'])) {
            $invoice->additionalFields['PaymentMethods'] = $params['PaymentMethods'];
        }

        /**
         * Язык общения с клиентом (в соответствии с ISO 3166-1). Определяет на каком языке будет страница Robokassa, на которую попадёт покупатель. 
         * Если параметр не передан, то используются региональные настройки браузера покупателя. 
         * Для значений отличных от ru или en используется английский язык.
         * 
         */
        if (!empty($params['Culture'])) {
            $culture = LanguageEnum::tryFrom($params['Culture']);

            if (empty($culture)) {
                throw new \InvalidArgumentException('The "Culture" parameter must be one of ' . implode(', ', LanguageEnum::options()));
            }

            $invoice->additionalFields['Culture'] = $culture;
        }

        /**
         * Кодировка, в которой отображается страница Robokassa. По умолчанию: Windows-1251. 
         * Этот же параметр влияет на корректность отображения описания покупки (Description) в интерфейсе Robokassa, 
         * и на правильность передачи Дополнительных пользовательских параметров, если в их значениях присутствует язык отличный от английского.
         */
        if (!empty($params['Encoding'])) {
            $invoice->additionalFields['Encoding'] = $params['Encoding'];
        }

        /**
         * В этом параметре передается информация о перечне товаров/услуг, количестве, цене, налговой ставке и ставке НДС по каждой позиции.
         */
        if (!empty($params['Receipt'])) {
            $invoice->additionalFields['Receipt'] = Receipt::from($params['Receipt']);
        }

        /**
         * Если параметр передан, то email покупателя автоматически подставляется в платёжную форму Robokassa.
         */
        if (!empty($params['Email'])) {
            if (filter_var($params['Email'], FILTER_VALIDATE_EMAIL)) {
                $invoice->additionalFields['Email'] = $params['Email'];
            } else {
                throw new \InvalidArgumentException('The "Email" parameter must be valid e-mail address.');
            }
        }

        /**
         * Срок действия счета. Этот параметр необходим, чтобы запретить пользователю оплату позже указанной магазином даты при выставлении счета.
         */
        if (!empty($params['ExpirationDate'])) {
            $invoice->additionalFields['ExpirationDate'] = Carbon::parse($params['ExpirationDate'])->format("Y-m-d\\TH:i:s.u");
        }

        /**
         * Передача этого параметра (Ip конечного пользователя) желательна для усиления безопасности, 
         * предотвращению фрода и противодействию мошенникам. 
         */
        if (!empty($params['UserIp'])) {
            if (filter_var($params['UserIp'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $invoice->additionalFields['UserIp'] = $params['UserIp'];
            } else {
                throw new \InvalidArgumentException('The "UserIp" parameter must be valid IPv4 address.');
            }
        }

        /**
         * Дополнительное оповещение об успешной оплате позволяет получить уведомление на альтернативный адрес, 
         * отличный от указанного в настройках магазина(Result URL). 
         * Для операций с холдами на этот адрес направляется уведомление об успешной предавторизации, и это единственный способ его получить.
         * По своему усмотрению в каждой новой оплате вы можете использовать новый адрес, так как значение ResultUrl2 не обязательно должно быть статичным.
         */
        if (!empty($params['ResultUrl2'])) {
            if (filter_var($params['ResultUrl2'], FILTER_VALIDATE_URL)) {
                $invoice->additionalFields['ResultUrl2'] = $params['ResultUrl2'];
            } else {
                throw new \InvalidArgumentException('The "ResultUrl2" parameter must be valid URL address.');
            }
        }

        /**
         * Дополнительная возможность переадресации покупателя после успешной оплаты на адрес отличный от Success URL указанный в настройках магазина.
         * Использование данного функционала возможно, например, при необходимости перенаправления покупателя на определенную страницу после успешной оплаты 
         * для возможности посмотреть или скачать купленный товар.
         */
        if (!empty($params['SuccessUrl2'])) {
            if (filter_var($params['SuccessUrl2'], FILTER_VALIDATE_URL)) {
                $invoice->additionalFields['SuccessUrl2'] = $params['SuccessUrl2'];
            } else {
                throw new \InvalidArgumentException('The "SuccessUrl2" parameter must be valid URL address.');
            }
        }

        if (!empty($params['SuccessUrl2Method'])) {
            if (in_array(strtoupper($params['SuccessUrl2Method']), ['GET', 'POST'])) {
                $invoice->additionalFields['SuccessUrl2Method'] = strtoupper($params['SuccessUrl2Method']);
            } else {
                throw new \InvalidArgumentException('The "SuccessUrl2Method" parameter must be GET or POST.');
            }
        } elseif (!empty($invoice->additionalFields['SuccessUrl2'])) {
            $invoice->additionalFields['SuccessUrl2Method'] = 'GET';
        }

        /**
         * Дополнительная возможность переадресации покупателя после неуспешной оплаты на адрес отличный от Fail URL указанный в настройках магазина.
         */
        if (!empty($params['FailUrl2'])) {
            if (filter_var($params['FailUrl2'], FILTER_VALIDATE_URL)) {
                $invoice->additionalFields['FailUrl2'] = $params['FailUrl2'];
            } else {
                throw new \InvalidArgumentException('The "FailUrl2" parameter must be valid URL address.');
            }
        }

        if (!empty($params['FailUrl2Method'])) {
            if (in_array(strtoupper($params['FailUrl2Method']), ['GET', 'POST'])) {
                $invoice->additionalFields['FailUrl2Method'] = strtoupper($params['FailUrl2Method']);
            } else {
                throw new \InvalidArgumentException('The "FailUrl2Method" parameter must be GET or POST.');
            }
        } elseif (!empty($invoice->additionalFields['FailUrl2'])) {
            $invoice->additionalFields['FailUrl2Method'] = 'GET';
        }

        /**
         * Дополнительные пользовательские параметры
         */
        $userFields = UserFields::from($params);

        if (!empty($userFields?->fields)) {
            $invoice->additionalFields['UserFields'] = $userFields->fields;
        }

        /** TODO Recurring
         * 
         * @see https://docs.robokassa.ru/recurring/
         */

        return $invoice;
    }

    public function toArray()
    {
        return json_decode(json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), true);
    }
}
