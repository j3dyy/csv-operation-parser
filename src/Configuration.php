<?php

namespace J3dyy\CsvOperationParser;

use J3dyy\CsvOperationParser\Exceptions\ApplicationException;
use J3dyy\CsvOperationParser\Services\Http\HttpProvider;
use J3dyy\CsvOperationParser\Traits\SingletonTrait;

class Configuration
{
    use SingletonTrait;

    private bool $isBooted = false;

    public const REQUEST_PROVIDER = 'currencyProvider';

    private array $configuration = [];

    private function __construct()
    {
    }

    /**
     * @throws ApplicationException
     */
    public function add(string $key, mixed $value): void
    {
        $this->checkIfBooted();

        //if value exists overwrite new one
        $this->configuration[$key] = $value;
    }

    /**
     * @throws ApplicationException
     */
    public function get(string $key): mixed
    {
        $this->checkIfBooted();

        return $this->configuration[$key] ?? null;
    }

    /**
     * @throws ApplicationException
     */
    public function weeklyLimit(): int
    {
        $this->checkIfBooted();

        return $this->configuration['weeklyLimit'];
    }

    /**
     * @throws ApplicationException
     */
    public function freeOfCharge(): float
    {
        $this->checkIfBooted();

        return $this->configuration['freeOfCharge'];
    }

    /**
     * @throws ApplicationException
     */
    public function hasCurrency(string $currency): bool
    {
        $this->checkIfBooted();

        return in_array($currency, $this->get('currency')['currencies']);
    }

    public function baseCurrency(): string
    {
        return 'EUR';
    }

    public function loadConfiguration(): void
    {
        //load only first time
        if (!$this->isBooted) {
            //default currencies
            $this->configuration['currency'] = [
                'currencies' => [
                    'USD',
                    'JPY',
                    'EUR'
                ],
                'currency_provider' => 'https://developers.paysera.com/tasks/api/currency-exchange-rates'
            ];

            // Inject default request provider
            $this->configuration[self::REQUEST_PROVIDER] =  HttpProvider::class;

            $this->configuration['freeOfCharge'] = 1000;
            $this->configuration['weeklyLimit'] = 3;
            $this->isBooted = true;
        }
    }

    private function checkIfBooted()
    {
        if (!$this->isBooted) {
            throw new ApplicationException("Configuration not booted");
        }
    }

}
