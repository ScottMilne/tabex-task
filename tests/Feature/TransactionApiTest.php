<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_transaction(): void
    {
        $seller = Seller::factory()->create(['tier' => 'pro']);

        $payload = [
            'seller_id' => $seller->id,
            'amount' => 10000,
            'currency' => 'USD',
            'payment_provider' => 'stripe',
            'customer_id' => 'cust_456',
            'idempotency_key' => 'unique-key-123',
        ];

        $response = $this->postJson('/api/v1/transactions', $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'seller_id' => $seller->id,
                'gross_amount' => 10000,
                'currency' => 'USD',
                'payment_provider' => 'stripe',
                'payment_provider_fee' => 320,
                'commission_rate' => '0.0700',
                'commission_amount' => 700,
                'net_amount' => 8980,
                'status' => 'completed',
            ]);

        $this->assertDatabaseHas('transactions', [
            'seller_id' => $seller->id,
            'idempotency_key' => 'unique-key-123',
            'amount' => 10000,
            'payment_provider_fee' => 320,
            'commission_amount' => 700,
            'net_amount' => 8980,
        ]);
    }

    public function test_it_returns_original_transaction_for_same_idempotency_key(): void
    {
        $seller = Seller::factory()->create(['tier' => 'starter']);

        $payload = [
            'seller_id' => $seller->id,
            'amount' => 10000,
            'currency' => 'USD',
            'payment_provider' => 'paypal',
            'customer_id' => 'cust_abc',
            'idempotency_key' => 'dup-key-1',
        ];

        $first = $this->postJson('/api/v1/transactions', $payload)->assertCreated();
        $second = $this->postJson('/api/v1/transactions', $payload)->assertOk();

        $this->assertSame(
            $first->json('data.transaction_id'),
            $second->json('data.transaction_id'),
        );
        $this->assertSame(1, Transaction::query()->where('idempotency_key', 'dup-key-1')->count());
    }

    public function test_it_fetches_a_transaction_by_id(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->getJson('/api/v1/transactions/'.$transaction->id);

        $response->assertOk()
            ->assertJsonFragment([
                'transaction_id' => $transaction->id,
                'seller_id' => $transaction->seller_id,
            ]);
    }

    public function test_it_returns_monthly_commission_summary_grouped_by_currency(): void
    {
        $seller = Seller::factory()->create(['id' => 'seller_123']);

        Transaction::factory()->create([
            'seller_id' => $seller->id,
            'currency' => 'USD',
            'amount' => 10000,
            'commission_amount' => 700,
            'net_amount' => 8980,
        ]);

        Transaction::factory()->create([
            'seller_id' => $seller->id,
            'currency' => 'USD',
            'amount' => 20000,
            'commission_amount' => 1400,
            'net_amount' => 17960,
        ]);

        Transaction::factory()->create([
            'seller_id' => $seller->id,
            'currency' => 'EUR',
            'amount' => 9000,
            'commission_amount' => 630,
            'net_amount' => 8070,
        ]);

        Transaction::factory()->create([
            'seller_id' => $seller->id,
            'currency' => 'USD',
            'amount' => 5000,
            'commission_amount' => 350,
            'net_amount' => 4650,
            'status' => 'failed',
        ]);

        $response = $this->getJson('/api/v1/sellers/'.$seller->id.'/commission-summary?period=monthly');

        $response->assertOk()
            ->assertJsonPath('seller_id', $seller->id)
            ->assertJsonPath('period', 'monthly')
            ->assertJsonCount(2, 'summaries')
            ->assertJsonFragment([
                'currency' => 'USD',
                'total_transactions' => 2,
                'total_gross_amount' => 30000,
                'total_commission' => 2100,
                'total_net_amount' => 26940,
            ])
            ->assertJsonFragment([
                'currency' => 'EUR',
                'total_transactions' => 1,
                'total_gross_amount' => 9000,
                'total_commission' => 630,
                'total_net_amount' => 8070,
            ]);
    }

    public function test_it_validates_transaction_create_payload(): void
    {
        $response = $this->postJson('/api/v1/transactions', [
            'seller_id' => 'missing_seller',
            'amount' => 0,
            'currency' => 'JPY',
            'payment_provider' => 'unknown',
            'customer_id' => '',
            'idempotency_key' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'seller_id',
                'amount',
                'currency',
                'payment_provider',
                'customer_id',
                'idempotency_key',
            ]);
    }

    public function test_it_returns_not_found_for_unknown_transaction(): void
    {
        $this->getJson('/api/v1/transactions/txn_missing')->assertNotFound();
    }

    public function test_it_returns_not_found_for_unknown_seller_summary(): void
    {
        $this->getJson('/api/v1/sellers/seller_missing/commission-summary?period=monthly')
            ->assertNotFound();
    }
}
