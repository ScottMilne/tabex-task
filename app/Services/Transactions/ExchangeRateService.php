<?php

namespace App\Services\Transactions;

use InvalidArgumentException;

class ExchangeRateService
{
    /**
     * @var array<string, array<string, float>>
     */
    private const RATES = [
        'USD' => ['USD' => 1.0, 'EUR' => 0.92, 'GBP' => 0.79],
        'EUR' => ['EUR' => 1.0, 'USD' => 1.09, 'GBP' => 0.86],
        'GBP' => ['GBP' => 1.0, 'USD' => 1.27, 'EUR' => 1.16],
    ];

    /**
     * @return array<int, string>
     */
    public function supportedCurrencies(): array
    {
        return array_keys(self::RATES);
    }

    public function getRate(string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        $rate = self::RATES[$fromCurrency][$toCurrency] ?? null;

        if ($rate === null) {
            throw new InvalidArgumentException('Unsupported currency pair.');
        }

        return $rate;
    }

    public function convert(int $amount, string $fromCurrency, string $toCurrency): int
    {
        $rate = $this->getRate($fromCurrency, $toCurrency);

        return (int) round($amount * $rate);
    }
}
