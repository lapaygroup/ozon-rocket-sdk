
<a href="https://lapaygroup.ru"><img align="left" width="200" src="https://lapaygroup.ru/img/lapaygroup.svg"></a>
<a href="https://rocket.ozon.ru"><img align="right" width="200" src="https://cdn1.ozone.ru/s3/helppartners/ozon-rocket-main.svg"></a>    

<br /><br /><br />

[![Latest Stable Version](https://poser.pugx.org/lapaygroup/ozon-rocket-sdk/v/stable)](https://packagist.org/packages/lapaygroup/ozon-rocket-sdk)
[![Total Downloads](https://poser.pugx.org/lapaygroup/ozon-rocket-sdk/downloads)](https://packagist.org/packages/lapaygroup/ozon-rocket-sdk)
[![License](https://poser.pugx.org/lapaygroup/ozon-rocket-sdk/license)](https://packagist.org/packages/lapaygroup/ozon-rocket-sdk)
[![Telegram Chat](https://img.shields.io/badge/telegram-chat-blue.svg?logo=telegram)](https://t.me/phpboxberrysdk)

# SDK для [интеграции с программным комплексом Ozon Rocket](https://rocket.ozon.ru).  

Посмотреть все проекты или подарить автору кофе можно [тут](https://lapaygroup.ru/opensource).    

[Документация к API](https://docs.ozon.ru/api/rocket) Ozon Rocket.    

# Содержание    
- [Changelog](#changelog)    
- [Конфигурация](#configuration)  
- [Отладка](#debugging)  
- [Расчет тарифа](#tariffs)  
- [Получить информацию о сроках доставки](#delivery-period)  


<a name="links"><h1>Changelog</h1></a>
 - 0.1.1 - Добавлен метод получения сроков доставки;
 - 0.1.0 - Первая Alfa-версия SDK.  

# Установка  
Для установки можно использовать менеджер пакетов Composer

    composer require lapaygroup/ozon-rocket-sdk
    

<a name="configuration"><h1>Конфигурация</h1></a>  

Для работы с API необходимо получить api-key у персонального менеджера при заключении договора.    
По api-key необходимо получить токен в формате JWT и сохранить его. Токен живет 1 час с момента издания.     

SDK позволяет сохранять JWT, для этого необходимо использовать Helper, который должен реализовывать [JwtSaveInterface](https://github.com/lapaygroup/ozon-rocket-sdk/blob/master/src/Helpers/JwtSaveInterface.php).    
В SDK встроен Helper для сохранения токена в временный файл [JwtSaveFileHelper](https://github.com/lapaygroup/ozon-rocket-sdk/blob/master/src/Helpers/JwtSaveFileHelper.php).    

```php
try {
    // Инициализация API клиента с таймаутом ожидания ответа 60 секунд
    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST);
    $jwt = $Client->getJwt(); // $jwt = eyJhbGciOiJSUzI1NiIsImtpZCI6IjQyMmNhZDNiLTc2MjMtNGZhYy1hMWEwLTIwZTQxMGQxNDRjMCIsInR5cCI6ImF0K2p3dCJ9.eyJuYmYiOjE2NTUxMzAxNjQsImV4cCI6MTY1NTIxNjU2NCwiaXNzIjoiaHR0cHM6Ly9hcGktc3RnLm96b25ydS5tZS9wcmluY2lwYWwtYXV0aC1hcGkiLCJjbGllbnRfaWQiOiJBcGlUZXN0XzExMTExMTExLTExMTEtMTExMS0xMTExLTExMTExMTExMTExMSIsIkxvem9uVXNlck5hbWUiOiJBcGlVc2VyVGVzdDIiLCJMb3pvbkNvbnRyYWN0SWQiOiIyMjYwNDI1OTU3NjAwMCIsIkxvem9uUHJpbmNpcGFsSWQiOiIzNDUiLCJqdGkiOiJCOUFCNTUyMTVCMzlEQjBFMDM2OEI5NTk3QzE4QjFENiIsImlhdCI6MTY1NTEzMDE2NCwic2NvcGUiOlsiZGVsaXZlcnkucGFyYW1zLmFwaS5yZWFkIiwicHJpbmNpcGFsLmludGVncmF0aW9uLmFwaS5mdWxsIl19.IN6UV3rArlGSQD_fOFYT2c9FUPNx_LW_BMI7RqO7-rpT0_hbmh_PhxZedAdJs3ZRdc-kki5t2nR9p-GoQYWeVt30s5n-qeqpSigZvunf-TYmNMjl6Un0zSI0XY_9SMl-xTzUJ7DnwAGdWu9jWvusMoFI-vGUJB-wZIQzhCN1MeOq1gIgvc5Hd729fXe3hvlc683dsF-leoXgiIb3CV-kkSx6ASERZy7rw7ugs4LWhwphVCM2dvhMt8Ue1f35MkllgJaic9x6OU3JMIKlRdGFFdcPy9ZpWqmH34XpDoZCrHWnbndE-tFLc0fuXSIf0kNoJhaUrW5VJ7Gliu0_Rtv3sQ
    $result = \LapayGroup\OzonRocketSdk\Jwt::decode($jwt); // Получения информации из токена (payload)

    // Ранее полученный токен можно добавить в клиент специльным методом
    $Client->setJwt($jwt);

    // Токен можно сохранять в файл используя Helper
    $jwtHelper = new \LapayGroup\OzonRocketSdk\Helpers\JwtSaveFileHelper();
    // Можно задать путь до временного файла отличный от заданного по умолчанию
    $jwtHelper->setTmpFile('/tmp/saved_jwt.txt');

    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST, $jwtHelper);
    $jwt = $Client->getJwt(); // $jwt = eyJhbGciOiJSUzI1NiIsImtpZCI6IjQyMmNhZDNiLTc2MjMtNGZhYy1hMWEwLTIwZTQxMGQxNDRjMCIsInR5cCI6ImF0K2p3dCJ9.eyJuYmYiOjE2NTUxMzAxNjQsImV4cCI6MTY1NTIxNjU2NCwiaXNzIjoiaHR0cHM6Ly9hcGktc3RnLm96b25ydS5tZS9wcmluY2lwYWwtYXV0aC1hcGkiLCJjbGllbnRfaWQiOiJBcGlUZXN0XzExMTExMTExLTExMTEtMTExMS0xMTExLTExMTExMTExMTExMSIsIkxvem9uVXNlck5hbWUiOiJBcGlVc2VyVGVzdDIiLCJMb3pvbkNvbnRyYWN0SWQiOiIyMjYwNDI1OTU3NjAwMCIsIkxvem9uUHJpbmNpcGFsSWQiOiIzNDUiLCJqdGkiOiJCOUFCNTUyMTVCMzlEQjBFMDM2OEI5NTk3QzE4QjFENiIsImlhdCI6MTY1NTEzMDE2NCwic2NvcGUiOlsiZGVsaXZlcnkucGFyYW1zLmFwaS5yZWFkIiwicHJpbmNpcGFsLmludGVncmF0aW9uLmFwaS5mdWxsIl19.IN6UV3rArlGSQD_fOFYT2c9FUPNx_LW_BMI7RqO7-rpT0_hbmh_PhxZedAdJs3ZRdc-kki5t2nR9p-GoQYWeVt30s5n-qeqpSigZvunf-TYmNMjl6Un0zSI0XY_9SMl-xTzUJ7DnwAGdWu9jWvusMoFI-vGUJB-wZIQzhCN1MeOq1gIgvc5Hd729fXe3hvlc683dsF-leoXgiIb3CV-kkSx6ASERZy7rw7ugs4LWhwphVCM2dvhMt8Ue1f35MkllgJaic9x6OU3JMIKlRdGFFdcPy9ZpWqmH34XpDoZCrHWnbndE-tFLc0fuXSIf0kNoJhaUrW5VJ7Gliu0_Rtv3sQ
        
}

catch (\LapayGroup\FivePostSdk\Exceptions\FivePostException $e) {
    // Обработка ошибки вызова API 5post
    // $e->getMessage(); текст ошибки 
    // $e->getCode(); http код ответа сервиса 5post
    // $e->getRawResponse(); // ответ сервера 5post как есть (http request body)
}

catch (\Exception $e) {
    // Обработка исключения
}
```


<a name="debugging"><h1>Отладка</h1></a>  
Для логирования запросов и ответов используется [стандартный PSR-3 логгер](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md). 
Ниже приведен пример логирования используя [Monolog](https://github.com/Seldaek/monolog).  

```php
<?php
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler('log.txt', Logger::INFO));

    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST);
    $Client->setLogger($log);
    $jwt = $Client->getJwt(); // $jwt = eyJhbGciOiJSUzI1NiIsImtpZCI6IjQyMmNhZDNiLTc2MjMtNGZhYy1hMWEwLTIwZTQxMGQxNDRjMCIsInR5cCI6ImF0K2p3dCJ9.eyJuYmYiOjE2NTUxMzAxNjQsImV4cCI6MTY1NTIxNjU2NCwiaXNzIjoiaHR0cHM6Ly9hcGktc3RnLm96b25ydS5tZS9wcmluY2lwYWwtYXV0aC1hcGkiLCJjbGllbnRfaWQiOiJBcGlUZXN0XzExMTExMTExLTExMTEtMTExMS0xMTExLTExMTExMTExMTExMSIsIkxvem9uVXNlck5hbWUiOiJBcGlVc2VyVGVzdDIiLCJMb3pvbkNvbnRyYWN0SWQiOiIyMjYwNDI1OTU3NjAwMCIsIkxvem9uUHJpbmNpcGFsSWQiOiIzNDUiLCJqdGkiOiJCOUFCNTUyMTVCMzlEQjBFMDM2OEI5NTk3QzE4QjFENiIsImlhdCI6MTY1NTEzMDE2NCwic2NvcGUiOlsiZGVsaXZlcnkucGFyYW1zLmFwaS5yZWFkIiwicHJpbmNpcGFsLmludGVncmF0aW9uLmFwaS5mdWxsIl19.IN6UV3rArlGSQD_fOFYT2c9FUPNx_LW_BMI7RqO7-rpT0_hbmh_PhxZedAdJs3ZRdc-kki5t2nR9p-GoQYWeVt30s5n-qeqpSigZvunf-TYmNMjl6Un0zSI0XY_9SMl-xTzUJ7DnwAGdWu9jWvusMoFI-vGUJB-wZIQzhCN1MeOq1gIgvc5Hd729fXe3hvlc683dsF-leoXgiIb3CV-kkSx6ASERZy7rw7ugs4LWhwphVCM2dvhMt8Ue1f35MkllgJaic9x6OU3JMIKlRdGFFdcPy9ZpWqmH34XpDoZCrHWnbndE-tFLc0fuXSIf0kNoJhaUrW5VJ7Gliu0_Rtv3sQ
    $result = \LapayGroup\OzonRocketSdk\Jwt::decode($jwt);
```

В log.txt будут логи в виде:
```
[2022-06-13T14:15:21.219153+00:00] ozon-rocket-api.INFO: OzonRocket GET API request /principal-integration-api/v1/delivery/calculate?deliveryVariantId=1011000000001578&weight=500&fromPlaceId=18842715502000&estimatedPrice=1000 [] []
[2022-06-13T14:15:21.319264+00:00] ozon-rocket-api.INFO: OzonRocket API response /principal-integration-api/v1/delivery/calculate: {"amount":146.4} {"Server":["nginx"],"Date":["Mon, 13 Jun 2022 14:15:21 GMT"],"Content-Type":["application/json; charset=utf-8"],"Transfer-Encoding":["chunked"],"Connection":["keep-alive"],"Vary":["Accept-Encoding"],"Access-Control-Allow-Origin":["*"],"X-O3-Trace-Id":["ac9d2e1144903e5f"],"X-O3-App-Name":["principal-integration-api"],"X-O3-App-Version":["2022.06.09.8221"],"X-O3-App-Handler":["HTTP GET /v{version:apiVersion}/delivery/calculate"],"X-O3-App-Endpoint":["HTTP GET /v{version:apiVersion}/delivery/calculate"],"api-supported-versions":["1"],"Strict-Transport-Security":["max-age=36000"],"http_status":200} []
```


<a name="tariffs"><h1>Расчет тарифа</h1></a>  

Для расчета стоимости и сроков доставки используйте метод **calculationTariff**.   
 
**Входные параметры:**
- *$delivery_id (int)* - ID ПВЗ получения заказа;  
- *$place_id (int)* - ID места передачи заказа;  
- *$weight (int)* - вес отправления в граммах;
- *$valuation (float)* - объявленная стоимость.

**Выходные параметры:**
- *float* - стоимость доставки    

**Примеры вызова:**
```php
<?php
    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST);
    $price = $Client->calculationTariff(1011000000001578, 18842715502000, 500, 1000); // 146.4
```

<a name="tariffs"><h1>Расчет тарифа</h1></a>  

Для расчета стоимости и сроков доставки используйте метод **calculationTariff**.   
 
**Входные параметры:**
- *$delivery_id (int)* - ID ПВЗ получения заказа;  
- *$place_id (int)* - ID места передачи заказа;  
- *$weight (int)* - вес отправления в граммах;
- *$valuation (float)* - объявленная стоимость.

**Выходные параметры:**
- *float* - стоимость доставки    

**Примеры вызова:**
```php
<?php
    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST);
    $price = $Client->calculationTariff(1011000000001578, 18842715502000, 500, 1000); // 146.4
```

<a name="delivery-period"><h1>Получить информацию о сроках доставки</h1></a>  

Для получить информацию о сроках доставки используйте метод **getDeliveryPeriod**.   
 
**Входные параметры:**
- *$delivery_id (int)* - ID ПВЗ получения заказа;  
- *$place_id (int)* - ID места передачи заказа;  

**Выходные параметры:**
- *int* - срок доставки в днях    

**Примеры вызова:**
```php
<?php
    $Client = new LapayGroup\OzonRocketSdk\Client('ApiTest_11111111-1111-1111-1111-111111111111', 'SRYksX3PBPUYj73A6cNqbQYRSaYNpjSodIMeWoSCQ8U=', 60, \LapayGroup\OzonRocketSdk\Client::API_URI_TEST);
    $days = $Client->getDeliveryPeriod(1011000000001578, 18842715502000); // 9
```