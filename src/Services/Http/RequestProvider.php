<?php

namespace J3dyy\CsvOperationParser\Services\Http;

use Psr\Http\Message\ResponseInterface;

interface RequestProvider
{
    public function getResponse(): ResponseInterface;
    public function execute(string $method, string $uri, array $options);
}
