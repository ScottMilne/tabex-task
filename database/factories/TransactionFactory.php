<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Transaction>
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 100000);
        $provider = $this->faker->randomElement(['stripe', 'paypal', 'ideal']);
        $commissionRate = (float) $this->faker->randomElement([0.1000, 0.0700, 0.0500]);
        $providerFee = match ($provider) {
            'stripe' => (int) round(($amount * 0.029) + 30),
            'paypal' => (int) round(($amount * 0.034) + 35),
            'ideal' => 0,
        };
        $commissionAmount = (int) round($amount * $commissionRate);
        $netAmount = $amount - $providerFee - $commissionAmount;

        return [
            'id' => 'txn_'.Str::lower(Str::random(14)),
            'seller_id' => Seller::factory(),
            'customer_id' => (string) Str::uuid(),
            'idempotency_key' => Str::uuid()->toString(),
            'amount' => $amount,
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'payment_provider' => $provider,
            'payment_provider_fee' => $providerFee,
            'commission_rate' => number_format($commissionRate, 4, '.', ''),
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'status' => 'completed',
        ];
    }
}
