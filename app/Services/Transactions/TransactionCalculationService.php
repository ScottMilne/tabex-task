<?php

namespace App\Services\Transactions;

use App\ValueObjects\CalculationResult;
use InvalidArgumentException;

class TransactionCalculationService
{
    private const STRIPE_RATE_NUMERATOR = 29;
    private const PAYPAL_RATE_NUMERATOR = 34;
    private const RATE_DENOMINATOR = 1000;
    private const STRIPE_FIXED_FEE = 30;
    private const PAYPAL_FIXED_FEE = 35;

    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {}

    public function calculate(int $grossAmount, string $currency, string $paymentProvider, string $sellerTier): CalculationResult
    {
        $currency = strtoupper($currency);
        $paymentProvider = strtolower($paymentProvider);
        $sellerTier = strtolower($sellerTier);

        if (! in_array($currency, $this->exchangeRateService->supportedCurrencies(), true)) {
            throw new InvalidArgumentException('Unsupported currency.');
        }

        $providerFeeAmount = $this->calculateProviderFee($grossAmount, $paymentProvider);
        $commissionBasisPoints = $this->commissionBasisPointsForTier($sellerTier);
        $commissionRate = number_format($commissionBasisPoints / 10000, 4, '.', '');
        $commissionAmount = $this->calculateRateAmount($grossAmount, $commissionBasisPoints, 10000);
        $netAmount = $grossAmount - $providerFeeAmount - $commissionAmount;

        return new CalculationResult(
            grossAmount: $grossAmount,
            providerFeeAmount: $providerFeeAmount,
            commissionRate: $commissionRate,
            commissionAmount: $commissionAmount,
            netAmount: $netAmount,
            currency: $currency,
        );
    }

    private function calculateProviderFee(int $grossAmount, string $paymentProvider): int
    {
        return match ($paymentProvider) {
            'stripe' => $this->calculateRateAmount($grossAmount, self::STRIPE_RATE_NUMERATOR, self::RATE_DENOMINATOR) + self::STRIPE_FIXED_FEE,
            'paypal' => $this->calculateRateAmount($grossAmount, self::PAYPAL_RATE_NUMERATOR, self::RATE_DENOMINATOR) + self::PAYPAL_FIXED_FEE,
            'ideal' => 0,
            default => throw new InvalidArgumentException('Unsupported payment provider.'),
        };
    }

    private function commissionBasisPointsForTier(string $sellerTier): int
    {
        return match ($sellerTier) {
            'starter' => 1000,
            'pro' => 700,
            'enterprise' => 500,
            default => throw new InvalidArgumentException('Unsupported seller tier.'),
        };
    }

    private function calculateRateAmount(int $grossAmount, int $numerator, int $denominator): int
    {
        return intdiv(($grossAmount * $numerator) + intdiv($denominator, 2), $denominator);
    }
}
