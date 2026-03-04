<?php

namespace App\ValueObjects;

readonly class CalculationResult
{
    public function __construct(
        public int $grossAmount,
        public int $providerFeeAmount,
        public string $commissionRate,
        public int $commissionAmount,
        public int $netAmount,
        public string $currency,
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'gross_amount' => $this->grossAmount,
            'payment_provider_fee' => $this->providerFeeAmount,
            'commission_rate' => $this->commissionRate,
            'commission_amount' => $this->commissionAmount,
            'net_amount' => $this->netAmount,
            'currency' => $this->currency,
        ];
    }
}
