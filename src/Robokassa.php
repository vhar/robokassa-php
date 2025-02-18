<?php

namespace Vhar\Robokassa;

use GuzzleHttp\Client;
use BadMethodCallException;
use InvalidArgumentException;
use Vhar\Robokassa\Common\Invoice;
use Vhar\Robokassa\Common\Merchant;
use Vhar\Robokassa\Common\InvoiceJWT;
use Vhar\Robokassa\Enums\LanguageEnum;
use Vhar\Robokassa\Responses\IsSuccess;
use Vhar\Robokassa\Responses\CreatedInvoice;
use Vhar\Robokassa\Responses\CurrenciesList;
use Vhar\Robokassa\Responses\PaymentMethodsList;
use Vhar\Robokassa\Responses\OperationStateResponse;

class Robokassa
{

    /**
     * @var Client $client
     */
    private Client $client;

    /**
     * @var string
     */
    private string $paymentUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    /**
     * @var string
     */
    private string $webServiceUrl = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx';

    /**
     * @var string
     */
    private string $invoiceServiceWebApiUrl = 'https://services.robokassa.ru/InvoiceServiceWebApi/api';

    /**
     * Robokassa constructor.
     * @param Merchant $merchant
     */
    public function __construct(private Merchant $merchant)
    {
        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 5
        ]);
    }

    /**
     * Ссылка для оплаты
     * 
     * @param \Vhar\Robokassa\Common\Invoice $invoice
     * @return string
     */
    public function createPaymentLink(Invoice $invoice): string
    {
        $params = $invoice->toArray();

        if ($this->merchant->isTest === true) {
            $params['IsTest'] = '1';
        }

        if (!empty($params['additionalFields'])) {
            $additionalFields = $params['additionalFields'];
            $params = array_merge($params, $additionalFields);
        }
        unset($params['additionalFields']);

        $signatureParams = [
            $this->merchant->login,
            $params['OutSum'],
            $params['InvoiceID']
        ];

        if (!empty($params['UserIp'])) {
            $signatureParams[] = $params['UserIp'];
        }

        if (!empty($params['Receipt'])) {
            $json = urlencode(json_encode($params['Receipt'], JSON_UNESCAPED_UNICODE));
            $params['Receipt'] = $json;
            $signatureParams[] = $json;
        }

        if (!empty($params['ResultUrl2'])) {
            $signatureParams[] = urlencode($params['ResultUrl2']);
        }

        if (!empty($params['SuccessUrl2'])) {
            $signatureParams[] = urlencode($params['SuccessUrl2']);
            $signatureParams[] = $params['SuccessUrl2Method'];
        }

        if (!empty($params['FailUrl2'])) {
            $signatureParams[] = urlencode($params['FailUrl2']);
            $signatureParams[] = $params['FailUrl2Method'];
        }

        $signatureParams[] = $this->merchant->password1;

        if (!empty($params['UserFields'])) {
            $userFields = [];

            foreach ($params['UserFields'] as $name => $value) {
                $userFields[$name] = urlencode($value);
            };

            ksort($userFields);

            $additionalParams = array_map(
                function ($key, $value) {
                    return $key . '=' . $value;
                },
                array_keys($userFields),
                array_values($userFields)
            );

            $signatureParams = array_merge($signatureParams, $additionalParams);

            $params = array_merge($params, $userFields);
        }
        unset($params['UserFields']);

        $params['SignatureValue'] = $this->generateSignature($signatureParams);

        return $this->paymentUrl . '?' . http_build_query($params);
    }

    /**
     * Создание счета на оплату
     * 
     * @param \Vhar\Robokassa\Common\InvoiceJWT $invoice
     * @throws \BadMethodCallException
     * @return CreatedInvoice|IsSuccess
     */
    public function createInvoice(InvoiceJWT $invoice)
    {
        if ($this->merchant->isTest === true) {
            throw new BadMethodCallException('Method not available on test mode.');
        }

        $params = $invoice->toArray();

        if (!empty($params['additionalFields'])) {
            $params = array_merge($params, $params['additionalFields']);
        }
        unset($params['additionalFields']);

        $response = $this->getJSONResponse('/CreateInvoice', $params);

        if ($response['isSuccess'] === true) {
            return CreatedInvoice::from($response);
        } else {
            return IsSuccess::from($response);
        }
    }

    /**
     * Деактивация (аннулирование) счета
     * 
     * @param int $invoiceID
     * @throws \BadMethodCallException
     * @return IsSuccess
     */
    public function deactivateInvoice(int $invoiceID)
    {
        if ($this->merchant->isTest === true) {
            throw new BadMethodCallException('Method not available on test mode.');
        }

        $body = [
            'MerchantLogin' => $this->merchant->login,
            'InvId' => $invoiceID
        ];

        $response = $this->getJSONResponse('/DeactivateInvoice', $body);

        return IsSuccess::from($response);
    }

    /**
     * Получение состояния оплаты счета
     *
     * Возвращает детальную информацию о текущем состоянии и реквизитах оплаты.
     * Необходимо помнить, что операция инициируется не в момент ухода пользователя на оплату,
     * а позже – после подтверждения его платежных реквизитов,
     * т.е. Вы вполне можете не находить операцию, которая по Вашему мнению уже должна начаться.
     * @param int $invoiceID
     * @return OperationStateResponse|null
     * @throws BadMethodCallException
     * 
     * @see https://docs.robokassa.ru/xml-interfaces/#account
     */
    public function opStateExt(int $invoiceID): OperationStateResponse|null
    {
        if ($this->merchant->isTest === true) {
            throw new BadMethodCallException('Method not available on test mode.');
        }

        $body = [
            'MerchantLogin' => $this->merchant->login,
            'InvoiceID' => $invoiceID,
            'Signature' => $this->signatureState($invoiceID)
        ];

        $response = $this->getXmlResponse('/OpStateExt', $body);

        if (!empty($response['OperationStateResponse'])) {
            return OperationStateResponse::from($response['OperationStateResponse']);
        }

        return null;
    }

    /**
     * Получение списка валют
     *
     * Используется для указания значений параметра IncCurrLabel
     * также используется для отображения доступных вариантов оплаты непосредственно на Вашем сайте
     * если Вы желаете дать больше информации своим клиентам.
     * @param string|null $lang
     * @return CurrenciesList|null
     * @throws InvalidArgumentException
     * 
     * @see https://docs.robokassa.ru/xml-interfaces/#currency
     */
    public function getCurrencies(string $lang = 'ru'): CurrenciesList|null
    {
        if (empty(LanguageEnum::tryFrom($lang))) {
            throw new InvalidArgumentException('The "lang" must be one of ' . implode(', ', LanguageEnum::options()));
        }

        $body = [
            'MerchantLogin' => $this->merchant->login,
            'Language' => $lang,
        ];

        $response = $this->getXmlResponse('/GetCurrencies', $body);

        if (!empty($response['CurrenciesList']['Groups'])) {
            return CurrenciesList::from($response['CurrenciesList']);
        }

        return null;
    }

    /**
     * Получение списка доступных способов оплаты
     *
     * Возвращает список способов оплаты, доступных для оплаты заказов указанного магазина/сайта.
     * @param string|null $lang
     * @return PaymentMethodsList|null
     * @throws InvalidArgumentException
     */
    public function getPaymentMethods(string $lang = 'ru'): PaymentMethodsList|null
    {
        if (empty(LanguageEnum::tryFrom($lang))) {
            throw new InvalidArgumentException('The "lang" must be one of ' . implode(', ', LanguageEnum::options()));
        }

        $body = [
            'MerchantLogin' => $this->merchant->login,
            'Language' => $lang,
        ];

        $response = $this->getXmlResponse('/GetPaymentMethods', $body);

        if (!empty($response['PaymentMethodsList']['Methods'])) {
            return PaymentMethodsList::from($response['PaymentMethodsList']);
        }

        return null;
    }

    /**
     * Проверка платежа на ResultURL
     *
     * @param $params
     * @return bool
     */
    public function checkResult(array $params): bool
    {
        return $this->checkHash($params, $this->merchant->password2);
    }

    /**
     * Проверка платежа на SuccessURL
     *
     * @param $params
     * @return bool
     */
    public function checkSuccess($params): bool
    {
        return $this->checkHash($params, $this->merchant->password1);
    }

    /**
     * Создание JWT токена
     * 
     * @param array $body
     * @return string
     */
    private function createJWT(array $body)
    {
        $header = base64_encode(
            json_encode(
                [
                    'typ' => 'JWT',
                    'alg' => strtoupper($this->merchant->hashType)
                ]
            )
        );

        $payload = base64_encode(
            json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $jwt = sprintf('%s.%s', $header, $payload);

        $signature = hash_hmac($this->merchant->hashType, $jwt, sprintf('%s:%s', $this->merchant->login, $this->merchant->password1));
        $signature = hex2bin($signature);
        $signature = base64_encode($signature);

        return sprintf('%s.%s.%s', $header, $payload, $signature);
    }

    /**
     * @param $params
     * @param $required
     * @return string
     */
    private function getHashFields($params, $required): string
    {
        $fields = [];

        foreach ($params as $key => $value) {
            if (!preg_match('~^Shp_~iu', $key)) {
                continue;
            }

            $fields[] = $key . '=' . $value;
        }

        $hash = implode(':', $required);

        if (!empty($fields)) {
            $hash .= ':' . implode(':', $fields);
        }

        return $hash;
    }

    /**
     * Подпись для запроса проверки статуса счета
     *
     * @param $invoiceID
     * @return string
     */
    private function signatureState($invoiceID): string
    {
        return hash($this->merchant->hashType, "{$this->merchant->login}:$invoiceID:{$this->merchant->password2}");
    }

    /**
     * @param $params
     * @param $password
     * @return bool
     */
    private function checkHash($params, $password): bool
    {
        $required = [
            $params['OutSum'],
            $params['InvId'],
            $password
        ];

        $hash = $this->getHashFields($params, $required);

        $crc = strtoupper($params['SignatureValue']);
        $my_crc = strtoupper(hash($this->merchant->hashType, $hash));

        return $my_crc === $crc;
    }

    /**
     * Подпись для запроса оплаты
     *
     * @param $params
     * @return string
     */
    private function generateSignature($fields): string
    {
        $hash = implode(':', $fields);

        return hash($this->merchant->hashType, $hash);
    }

    private function getXmlResponse(string $sector, array $body)
    {
        $response = $this->client->post(
            $this->webServiceUrl . $sector,
            [
                'body' => json_encode($body)
            ]
        );

        if ($response->getStatusCode() === 200) {
            $xml = $response->getBody()->getContents();

            return $this->getXmlInArray($xml);
        }

        return [];
    }

    private function getJSONResponse(string $sector, array $body): array
    {
        $jwt = $this->createJWT($body);

        $response = $this->client->post(
            $this->invoiceServiceWebApiUrl . $sector,
            [
                'body' => json_encode($jwt)
            ]
        );

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return [];
    }

    private function getXmlInArray($response): array
    {
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode([$xml->getName() => $xml]);
        $array = json_decode($json, true);

        $array = $this->convertXMLAttributes($array);

        return $array;
    }
    private function convertXMLAttributes(array $array): mixed
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (isset($value['@attributes'])) {
                    foreach ($value['@attributes'] as $key2 => $value2) {
                        $array[$key][$key2] = $value2;
                    }
                    unset($array[$key]['@attributes']);
                }

                if (is_array($array[$key])) {
                    $array[$key] = $this->convertXMLAttributes($array[$key]);
                }
            }
        }

        return $array;
    }
}
