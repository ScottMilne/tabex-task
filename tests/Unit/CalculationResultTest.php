<?php

namespace Tests\Unit;

use App\ValueObjects\CalculationResult;
use PHPUnit\Framework\TestCase;

class CalculationResultTest extends TestCase
{
    public function test_it_keeps_values_consistent_and_serializable(): void
    {
        $result = new CalculationResult(
            grossAmount: 10000,
            providerFeeAmount: 320,
            commissionRate: '0.0700',
            commissionAmount: 700,
            netAmount: 8980,
            currency: 'USD',
        );

        $this->assertSame(
            $result->grossAmount - $result->providerFeeAmount - $result->commissionAmount,
            $result->netAmount,
        );

        $this->assertSame([
            'gross_amount' => 10000,
            'payment_provider_fee' => 320,
            'commission_rate' => '0.0700',
            'commission_amount' => 700,
            'net_amount' => 8980,
            'currency' => 'USD',
        ], $result->toArray());
    }
}
