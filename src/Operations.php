<?php

namespace J3dyy\CsvOperationParser;

use Carbon\Carbon;
use J3dyy\CsvOperationParser\Exceptions\ApplicationException;
use J3dyy\CsvOperationParser\Exceptions\CurrencyException;
use J3dyy\CsvOperationParser\Exceptions\HttpProviderException;
use J3dyy\CsvOperationParser\Models\Operation;
use J3dyy\CsvOperationParser\Services\CurrencyService;
use J3dyy\FileParser\Parser\Parser;
use J3dyy\FileParser\ParseWrapper;

class Operations
{
    /**
     * @var ParseWrapper
     */
    protected ParseWrapper $parseWrapper;

    /**
     * @var array
     */
    protected array $operations = [];

    /**
     * @var array
     */
    protected array $userTransactions;

    /**
     * @var CurrencyService
     */
    protected CurrencyService $currencyService;

    /**
     * @var array
     */
    protected array $currencies;

    public function __construct(CurrencyService $currencyService, ?Parser $csvParser = null)
    {
        $this->currencyService = $currencyService;
        $this->parseWrapper = new ParseWrapper($csvParser);
    }

    /**
     * @param string $csv
     * @return void
     * @throws ApplicationException
     */
    public function process(string $csv): void
    {
        try {
            $array = $this->parseWrapper->toObject($csv, Operation::class);

            $previous = $array[0] ?? null;

            $weekCount = 0;

            //map by weeks
            foreach ($array as $operation) {

                //if week not set then initialize
                if (!isset($this->operations[$weekCount])) {
                    $this->operations[$weekCount] = [];
                }

                if (!$this->isSameWeek($operation->date, $previous->date)) {
                    $weekCount++;
                    $previous = $operation;
                }

                $this->operations[$weekCount][] = $operation;
            }
        } catch (\TypeError $e) {
            throw new ApplicationException(sprintf("Invalid csv file: %s was give", $csv));
        }
    }

    /**
     * @return array
     * @throws CurrencyException
     * @throws HttpProviderException
     */
    public function calculateCommissions(): array
    {
        $commissions = [];

        foreach ($this->operations as $operations) {
            $this->userTransactions = [];

            foreach ($operations as $operation) {
                $commissions[] = round($this->calculateWithdrawFee($operation), 2);

            }
        }
        return  $commissions;
    }

    /**
     * @throws CurrencyException
     * @throws HttpProviderException
     */
    private function calculateWithdrawFee(Operation $operation): float
    {
        $amount = $operation->operationAmount;
        $fee = $operation->getFee();

        $amount = $this->detectCurrencyAndConvert($operation->operationCurrency, $amount);

        if ($operation->operationType == 'withdraw' && $operation->userType != 'business') {

            $this->bindCountAndTotal(
                $operation->userId,
                $amount
            );

            $currentUserData = $this->userTransactions[$operation->userId];

            //if user week transaction count not exceeded limit
            if ($currentUserData['count'] <= Configuration::instance()->weeklyLimit()) {

                //if amount not greater than maximum free_of_charge and  user has not used up the weekly limit
                if ($amount > Configuration::instance()->freeOfCharge() && !$currentUserData['full']) {
                    $this->revokeWeeklyLimit($operation->userId);
                    return ($amount - Configuration::instance()->freeOfCharge()) * $fee / 100;
                }

                // if user transaction total not greater than FEE_OF_CHARGE and user has not used up the weekly limit
                if ($currentUserData['total'] > Configuration::instance()->freeOfCharge() && !$currentUserData['full']) {
                    $this->revokeWeeklyLimit($operation->userId);
                    return ($currentUserData['total'] - Configuration::instance()->freeOfCharge()) * $fee / 100;
                }

                // if transaction total not greater than limit
                if ($currentUserData['total'] <= Configuration::instance()->freeOfCharge()) {
                    return 0;
                }
            }
        }

        return $amount * $fee / 100;
    }


    /**
     * @throws CurrencyException
     * @throws CurrencyException|HttpProviderException
     */
    private function detectCurrencyAndConvert(string $currency, float $amount): float
    {

        if ($amount == 0) {
            throw new \DivisionByZeroError("Amount cannot be 0");
        }

        if ($currency != Configuration::instance()->baseCurrency()) {
            //if currency supported
            if (Configuration::instance()->hasCurrency($currency)) {
                $this->fetchCurrency();
            }

            if (!isset($this->currencies['rates'][$currency])) {
                throw new CurrencyException(sprintf("Currency %s not found ", $currency));
            }

            $amount = $amount / $this->currencies['rates'][$currency] ;
        }

        return $amount;
    }

    /**
     * @param int $userId
     * @return void
     */
    private function revokeWeeklyLimit(int $userId): void
    {
        $this->userTransactions[$userId]['full'] = true;
    }

    private function bindCountAndTotal(int $userId, float $amount): void
    {
        if (!isset($this->userTransactions[$userId])) {
            $this->userTransactions[$userId] = [
                'count' => 1,
                'total' => $amount,
                'full' => false
            ];
        } else {
            $this->userTransactions[$userId]['count']++;
            $this->userTransactions[$userId]['total'] += $amount;
        }

    }

    /**
     * @param string $firstDate
     * @param string $secondDate
     * @return bool
     */
    private function isSameWeek(string $firstDate, string $secondDate): bool
    {
        return Carbon::parse($firstDate)->isSameWeek(
            Carbon::parse($secondDate)
        );
    }


    /**
     * @return void
     * @throws Exceptions\HttpProviderException
     */
    private function fetchCurrency(): void
    {
        if (!isset($this->currencies)) {
            $this->currencies = $this->currencyService->getEURRates();
        }
    }


}
