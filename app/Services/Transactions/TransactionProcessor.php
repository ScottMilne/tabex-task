<?php

namespace App\Services\Transactions;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TransactionProcessor
{
    public function __construct(
        private readonly TransactionCalculationService $transactionCalculationService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function process(array $payload): Transaction
    {
        return DB::transaction(function () use ($payload): Transaction {
            $existing = Transaction::query()
                ->where('idempotency_key', $payload['idempotency_key'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $seller = Seller::query()->findOrFail($payload['seller_id']);
            $calculationResult = $this->transactionCalculationService->calculate(
                grossAmount: (int) $payload['amount'],
                currency: (string) $payload['currency'],
                paymentProvider: (string) $payload['payment_provider'],
                sellerTier: (string) $seller->tier,
            );

            try {
                return Transaction::query()->create([
                    'seller_id' => $seller->getKey(),
                    'customer_id' => $payload['customer_id'],
                    'idempotency_key' => $payload['idempotency_key'],
                    'amount' => $calculationResult->grossAmount,
                    'currency' => $calculationResult->currency,
                    'payment_provider' => $payload['payment_provider'],
                    'payment_provider_fee' => $calculationResult->providerFeeAmount,
                    'commission_rate' => $calculationResult->commissionRate,
                    'commission_amount' => $calculationResult->commissionAmount,
                    'net_amount' => $calculationResult->netAmount,
                    'status' => 'completed',
                ]);
            } catch (QueryException $exception) {
                $duplicateKey = $exception->getCode() === '23000';

                if (! $duplicateKey) {
                    throw $exception;
                }

                return Transaction::query()
                    ->where('idempotency_key', $payload['idempotency_key'])
                    ->firstOrFail();
            }
        });
    }
}
