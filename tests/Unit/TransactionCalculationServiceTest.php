<?php

namespace Tests\Unit;

use App\Services\Transactions\ExchangeRateService;
use App\Services\Transactions\TransactionCalculationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TransactionCalculationServiceTest extends TestCase
{
    public function test_it_calculates_stripe_fee_and_pro_commission(): void
    {
        $service = new TransactionCalculationService(new ExchangeRateService());

        $result = $service->calculate(
            grossAmount: 10000,
            currency: 'USD',
            paymentProvider: 'stripe',
            sellerTier: 'pro',
        );

        $this->assertSame(10000, $result->grossAmount);
        $this->assertSame(320, $result->providerFeeAmount);
        $this->assertSame('0.0700', $result->commissionRate);
        $this->assertSame(700, $result->commissionAmount);
        $this->assertSame(8980, $result->netAmount);
        $this->assertSame('USD', $result->currency);
    }

    public function test_it_calculates_ideal_fee_as_zero(): void
    {
        $service = new TransactionCalculationService(new ExchangeRateService());

        $result = $service->calculate(
            grossAmount: 5000,
            currency: 'EUR',
            paymentProvider: 'ideal',
            sellerTier: 'starter',
        );

        $this->assertSame(0, $result->providerFeeAmount);
        $this->assertSame('0.1000', $result->commissionRate);
        $this->assertSame(500, $result->commissionAmount);
        $this->assertSame(4500, $result->netAmount);
    }

    public function test_it_calculates_paypal_fee_and_enterprise_commission(): void
    {
        $service = new TransactionCalculationService(new ExchangeRateService());

        $result = $service->calculate(
            grossAmount: 10000,
            currency: 'GBP',
            paymentProvider: 'paypal',
            sellerTier: 'enterprise',
        );

        $this->assertSame(375, $result->providerFeeAmount);
        $this->assertSame('0.0500', $result->commissionRate);
        $this->assertSame(500, $result->commissionAmount);
        $this->assertSame(9125, $result->netAmount);
    }

    public function test_it_throws_for_unsupported_provider(): void
    {
        $service = new TransactionCalculationService(new ExchangeRateService());

        $this->expectException(InvalidArgumentException::class);
        $service->calculate(10000, 'USD', 'adyen', 'pro');
    }
}
