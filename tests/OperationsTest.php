<?php

use J3dyy\CsvOperationParser\Exceptions\ApplicationException;
use J3dyy\CsvOperationParser\Operations;
use J3dyy\CsvOperationParser\Services\CurrencyService;

class OperationsTest extends \PHPUnit\Framework\TestCase
{
    public function testWrongCsv()
    {
        $this->expectException(ApplicationException::class);
        \J3dyy\CsvOperationParser\Configuration::instance()->loadConfiguration();

        $operations = new Operations($this->currencyServiceMock());

        $operations->process('notfound.csv');
    }

    public function testCorrectCsv()
    {
        \J3dyy\CsvOperationParser\Configuration::instance()->loadConfiguration();

        $operations = new Operations($this->currencyServiceMock());

        $operations->process(__DIR__.'/files/fake.csv');

        $commissions = $operations->calculateCommissions();

        $this->assertIsArray($commissions);
        $this->assertIsFloat($commissions[0]);
    }

    private function currencyServiceMock(): CurrencyService
    {
        return new CurrencyService();
    }
}
