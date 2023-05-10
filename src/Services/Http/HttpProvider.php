<?php

namespace J3dyy\CsvOperationParser\Services\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class HttpProvider implements RequestProvider
{
    protected Client $client;

    protected ?Response $response = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     */
    public function execute(string $method, string $uri, array $options): void
    {
        $this->response = $this->client->request('GET', $uri, $options);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
