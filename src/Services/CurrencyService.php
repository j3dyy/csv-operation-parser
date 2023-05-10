<?php

namespace J3dyy\CsvOperationParser\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use J3dyy\CsvOperationParser\Configuration;
use J3dyy\CsvOperationParser\Exceptions\HttpProviderException;
use J3dyy\CsvOperationParser\Services\Http\RequestProvider;
use Psr\Http\Message\ResponseInterface;

class CurrencyService
{
    /**
     * @var RequestProvider|mixed
     */
    public RequestProvider $provider;

    /**
     * @var string
     */
    public string $uri = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';


    public function __construct()
    {
        $this->provider = new (Configuration::instance()->get(Configuration::REQUEST_PROVIDER))();
    }

    /**
     * @return array
     * @throws HttpProviderException
     */
    public function getEURRates(): array
    {
        $res = $this->requestCurrency();

        return $this->decode($res->getBody()->getContents());
    }

    /**
     * @return ResponseInterface
     * @throws HttpProviderException
     */
    private function requestCurrency(): ResponseInterface
    {
        try {
            $this->provider->execute('GET', $this->uri, []);

        } catch (ConnectException | ClientException $exception) {
            throw new HttpProviderException($exception->getMessage());
        }

        return $this->provider->getResponse();
    }

    /**
     * @param string $jsonBody
     * @return array
     */
    protected function decode(string $jsonBody): array
    {
        return json_decode($jsonBody, true);
    }
}
