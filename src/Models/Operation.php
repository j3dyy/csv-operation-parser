<?php

namespace J3dyy\CsvOperationParser\Models;

use J3dyy\FileParser\Mapping\Attribute\Column;

class Operation implements CommissionContract
{
    #[Column(0)]
    public string $date;

    #[Column(1)]
    public int $userId;

    #[Column(2)]
    public string $userType;

    #[Column(3)]
    public string $operationType;

    #[Column(4)]
    public float $operationAmount;

    #[Column(5)]
    public string $operationCurrency;


    public function getFee(): float
    {
        if ($this->operationType == "deposit") {
            return  0.03;
        }

        if ($this->userType == 'business') {
            return 0.5;
        }

        return 0.3;
    }
}
