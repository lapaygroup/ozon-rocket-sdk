<?php

namespace LapayGroup\OzonRocketSdk;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\UploadedFile;
use LapayGroup\OzonRocketSdk\Exceptions\FivePostException;
use LapayGroup\OzonRocketSdk\Exceptions\OzonRocketException;
use LapayGroup\OzonRocketSdk\Exceptions\TokenException;
use LapayGroup\OzonRocketSdk\Helpers\JwtSaveInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var string|null */
    private $jwt = null;

    /** @var string|null */
    private $client_id = null;

    /** @var string|null */
    private $client_secret = null;

    /** @var \GuzzleHttp\Client|null */
    private $httpClient = null;

    /** @var JwtSaveInterface|null */
    private $jwtHelper = null;


    const API_URI_TEST = 'https://api-stg.ozonru.me';
    const API_URI_PROD = 'https://xapi.ozon.ru';

    const DATA_JSON   = 'json';
    const DATA_PARAMS = 'form_params';

    /**
     * Client constructor.
     *
     * @param string $client_id - client_id в системе Ozon Rocket
     * @param string $client_secret - client_secret в системе Ozon Rocket
     * @param int $timeout - таймаут ожидания ответа от серверов Ozon Rocket в секундах
     * @param string $api_uri - адрес API (тествоый или продуктовый)
     * @param JwtSaveInterface|null $jwtHelper - помощник для сохранения токена
     */
    public function __construct($client_id, $client_secret, $timeout = 300, $api_uri = self::API_URI_PROD, $jwtHelper = null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->stack = new HandlerStack();
        $this->stack->setHandler(new CurlHandler());
        $this->stack->push($this->handleAuthorizationHeader());

        $this->httpClient = new \GuzzleHttp\Client([
            'handler'  => $this->stack,
            'base_uri' => $api_uri,
            'timeout' => $timeout,
            'exceptions' => false
        ]);

        if ($jwtHelper)
            $this->jwtHelper = $jwtHelper;
    }

    /**
     * Инициализирует вызов к API
     *
     * @param $type
     * @param $method
     * @param array $params
     * @return array|UploadedFile
     * @throws OzonRocketException
     */
    private function callApi($type, $method, $params = [], $data_type = self::DATA_JSON)
    {
        switch ($type) {
            case 'GET':
                $request = http_build_query($params);
                if ($this->logger) {
                    $this->logger->info("OzonRocket {$type} API request {$method}?" . $request);
                }
                $response = $this->httpClient->get($method, ['query' => $params]);
                break;
            case 'DELETE':
                $request = http_build_query($params);
                if ($this->logger) {
                    $this->logger->info("OzonRocket {$type} API request {$method}?" . $request);
                }
                $response = $this->httpClient->delete($method, ['query' => $params]);
                break;
            case 'PUT':
                $request = json_encode($params);
                if ($this->logger) {
                    $this->logger->info("OzonRocket API {$type} request {$method}: " . $request);
                }
                $response = $this->httpClient->put($method, [$data_type => $params]);
                break;
            case 'POST':
                $request = json_encode($params);
                if ($this->logger) {
                    $this->logger->info("OzonRocket API {$type} request {$method}: " . $request);
                }
                $response = $this->httpClient->post($method, [$data_type => $params]);
                break;
        }

        $json = $response->getBody()->getContents();
        $http_status_code = $response->getStatusCode();
        $headers = $response->getHeaders();
        $headers['http_status'] = $http_status_code;
        $content_type = $response->getHeaderLine('Content-Type');

        if (preg_match('~^application/(pdf|zip|octet-stream)~', $content_type, $matches_type)) {
            if ($this->logger) {
                $this->logger->info("OzonRocket API response {$method}: получен файл с расширением ".$matches_type[1], $headers);
            }

            $response->getBody()->rewind();
            preg_match('~=(.+)~', $response->getHeaderLine('Content-Disposition'), $matches_name);
            return new UploadedFile(
                $response->getBody(),
                $response->getBody()->getSize(),
                UPLOAD_ERR_OK,
                "{$matches_name[1]}.{$matches_type[1]}",
                $response->getHeaderLine('Content-Type')
            );
        } else if ($this->logger) {
            $this->logger->info("OzonRocket API response {$method}: " . $json, $headers);
        }

        $respOzon = json_decode($json, true);

        if (empty($respOzon) && $json != '[]')
            throw new OzonRocketException('От сервера OzonRocket при вызове метода ' . $method . ' пришел пустой ответ', $response->getStatusCode(), $json, $request);

        if ($http_status_code != 200)
            throw new OzonRocketException('От сервера OzonRocket при вызове метода ' . $method . ' получена ошибка: ' . $respOzon['message'].' ('.$respOzon['errorCode'].')', $response->getStatusCode(), $json, $request);

        return $respOzon;
    }

    /**
     * @return \Closure
     */
    private function handleAuthorizationHeader()
    {
        return function (callable $handler)
        {
            return function (RequestInterface $request, array $options) use ($handler)
            {
                if ($this->jwt) {
                    $request = $request->withHeader('Authorization', 'Bearer ' . $this->jwt);
                }

                return $handler($request, $options);
            };
        };
    }

    public function getJwt()
    {
        if ($this->jwtHelper)
            $this->jwt = $this->jwtHelper->getToken();

        if ($this->jwt) {
            try {
                Jwt::decode($this->jwt);
            }

            catch (TokenException $e) {
                $this->jwt = $this->generateJwt();
            }
        } else {
            $this->jwt = $this->generateJwt();
        }

        Jwt::decode($this->jwt);

        return $this->jwt;
    }

    /**
     * @param string $jwt - ранее полученный JWT токен
     */
    public function setJwt($jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Получение JWT токена по api-key
     *
     * @return mixed
     * @throws OzonRocketException
     */
    private function generateJwt()
    {
        $response = $this->callApi('POST', '/principal-auth-api/connect/token', 
            [
                'grant_type' => 'client_credentials', 
                'client_id' => $this->client_id, 
                'client_secret' => $this->client_secret
            ], 
            self::DATA_PARAMS);

        if ($this->jwtHelper)
            $this->jwt = $this->jwtHelper->setToken($response['access_token']);

        return $response['access_token'];
    }


    /**
     * Рассчитать стоимости доставки
     *
     * @param int $delivery_id - ID ПВЗ получения заказа
     * @param string $place_id ID места передачи заказа
     * @param int $weight - вес отправления в граммах
     * @param float $valuation - объявленная стоимость
     * @return float
     * @throws OzonRocketException
     * @throws \Exception
     */
    public function calculationTariff($delivery_id, $place_id, $weight, $valuation = 0)
    {
        $params = ['deliveryVariantId' => $delivery_id, 'weight' => $weight, 'fromPlaceId' => $place_id];
        if ($valuation > 0) $params['estimatedPrice'] = $valuation;

        $response = $this->callApi('GET', '/principal-integration-api/v1/delivery/calculate', $params);
        if (empty($response['amount']))
            throw new OzonRocketException('От метода расчета тарифа не пришел обязательный параметр amount');

        return $response['amount'];
    }

    /**
     * Получить информацию о сроках доставки
     *
     * @param int $delivery_id - ID ПВЗ получения заказа
     * @param string $place_id ID места передачи заказа
     * @return int
     * @throws OzonRocketException
     * @throws \Exception
     */
    public function getDeliveryPeriod($delivery_id, $place_id)
    {
        $params = ['deliveryVariantId' => $delivery_id, 'fromPlaceId' => $place_id];
        $response = $this->callApi('GET', '/principal-integration-api/v1/delivery/time', $params);
        if (empty($response['days']))
            throw new OzonRocketException('От метода получения информации о сроках доставки не пришел обязательный параметр days');

        return $response['days'];
    }
}