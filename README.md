# Робокасса-PHP

Библиотека для взаимодействия с платежной системой [Робокасса](https://docs.robokassa.ru/) в PHP.  
Позволяет отправлять платежные запросы, создавать счета, получать статус оплаты и список доступных методов оплаты.
>Программа для ЭВМ «Сервис Робокасса-PHP» внесена в Реестр программ для ЭВМ, регистрационный № [2025619755](https://fips.ru/registers-doc-view/fips_servlet?DB=EVM&rn=7886&DocNumber=2025619755&TypeFile=html) от 17.04.2025.

## Установка

```bash
$ composer require vhar/robokassa
```

## Доступные методы
| Метод | Описание |
|--------|----------|
| `createPaymentLink(Invoice $invoice): string` | Создает ссылку на оплату |
| `createInvoice(InvoiceJWT $invoice): CreatedInvoice\|IsSuccess` | Создает счет на оплату в личном кабинете |
| `deactivateInvoice(int $invoiceID): IsSuccess` | Аннулирует созданный в личном кабинете счет  по `InvoiceID` |
| `opStateExt(int $invoiceID): OperationStateResponse\|null` | Получает статус оплаты по `InvoiceID` |
| `getCurrencies(string $lang = 'ru'): CurrenciesList\|null` | Получает доступный список валют |
| `getPaymentMethods(string $lang = 'ru'): PaymentMethodsList\|null` | Получает доступные методы оплаты |
| `checkResult(array $params): bool` | Валидация ответа на `ResultURL` |
| `checkSuccess(array $params): bool` | Валидация ответа на `SuccessURL` |

> Если счет был создан методом `createInvoice`, то объект `OperationStateResponse` всегда будет содержать `UserFields->Field` **shp_interface** со значением **InvoiceService.WebApi**.
> Если счет был создан в личном кабинете, то объект `OperationStateResponse` всегда будет содержать `UserFields->Field` **shp_interface** со значением **invoice**.

## Примеры использования
Примеры кода находятся в папке **`Examples/`**.

### Создание счета на оплату
```php
$merchant = [
    'login'     => 'merchant_login',
    'password1' => 'password1',
    'password2' => 'password2',
    'hashType'  => 'md5',
];

$invoiceItems = [
    [
        'Cost'             => 1,
        'Name'             => 'Сервис 1',
        'Quantity'         => 1,
        'PaymentMethod'    => 'full_payment',
        'PaymentObject'    => 'service',
        'Tax'              => 'none',
        'NomenclatureCode' => 'IYVITCUR%XE^$X%C^T&VITC^RX&%ERC^TIRX%&ERCUITRXE&ZX%R^CTIR^XUE%ZN1m9E+1¦?5O?6¦?168'
    ],
];

$orderData = [
    'MerchantLogin'    => 'merchant_login',
    'InvoiceType'      => 'OneTime',
    'OutSum'           => 1,
    'Description'      => 'Счет № 1',
    'ExpirationDate'   => Carbon::now()->timezone('Europe/Moscow')->addMinutes(5)->format("Y-m-d\\TH:i:s.u"),
    'MerchantComments' => 'Оплатить до ' . Carbon::now()->timezone('Europe/Moscow')->addMinutes(5)->format("Y-m-d H:i:s"),
    'Culture'          => 'ru',
    'InvoiceItems'     => $invoiceItems
];

$merchant  = Merchant::from($merchant);
$robokassa = new Robokassa($merchant);

$invoice  = InvoiceJWT::from($orderData);
$response = $robokassa->createInvoice($invoice);

print_r($response->toArray());
```

### Проверка статуса оплаты
```php
$merchant = [
    'login'     => 'merchant_login',
    'password1' => 'password1',
    'password2' => 'password2',
    'hashType'  => 'md5',
];
$merchant  = Merchant::from($merchant);
$robokassa = new Robokassa($merchant);

$status = $robokassa->opStateExt(2024021501);

print_r($status->toArray());
```

## Документация
- Официальная документация Robokassa: [https://docs.robokassa.ru/](https://docs.robokassa.ru/).
