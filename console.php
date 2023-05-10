<?php

use Carbon\Carbon;
use J3dyy\CsvOperationParser\Bootstrap;
use J3dyy\CsvOperationParser\Exceptions\ApplicationException;
use J3dyy\CsvOperationParser\Exceptions\CurrencyException;
use J3dyy\CsvOperationParser\Exceptions\HttpProviderException;
use J3dyy\CsvOperationParser\Operations;
use J3dyy\CsvOperationParser\Services\CurrencyService;

require __DIR__.'/vendor/autoload.php';

Bootstrap::boot();


if (isset($argv[1])) {
    try {
        $operation = new Operations(
            new CurrencyService()
        );
        $operation->process($argv[1]);

        $commissions = $operation->calculateCommissions();
        foreach ($commissions as $commission) {
            echo "$commission \n";
        }
    } catch (ApplicationException|CurrencyException|HttpProviderException $e) {
        dump($e->getMessage());
    }

} else {
    echo "please provide csv path";
}
