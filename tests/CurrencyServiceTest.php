<?php

use J3dyy\CsvOperationParser\Exceptions\ApplicationException;

class CurrencyServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testWithoutConfiguration()
    {
        $this->expectException(ApplicationException::class);

        $currencyService = new \J3dyy\CsvOperationParser\Services\CurrencyService();

        $array = $currencyService->getEURRates();

    }


    public function testFetchCurrency()
    {

        \J3dyy\CsvOperationParser\Configuration::instance()->loadConfiguration();

        $currencyService = new \J3dyy\CsvOperationParser\Services\CurrencyService();

        $array = $currencyService->getEURRates();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('base', $array);
        $this->assertArrayHasKey('rates', $array);
    }
}
