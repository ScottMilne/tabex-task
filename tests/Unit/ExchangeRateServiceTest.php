<?php

namespace Tests\Unit;

use App\Services\Transactions\ExchangeRateService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    public function test_it_returns_supported_currencies(): void
    {
        $service = new ExchangeRateService();

        $this->assertSame(['USD', 'EUR', 'GBP'], $service->supportedCurrencies());
    }

    public function test_it_converts_amount_using_mock_rates(): void
    {
        $service = new ExchangeRateService();

        $this->assertSame(9200, $service->convert(10000, 'USD', 'EUR'));
    }

    public function test_it_throws_for_unsupported_pair(): void
    {
        $service = new ExchangeRateService();

        $this->expectException(InvalidArgumentException::class);
        $service->getRate('USD', 'JPY');
    }
}
