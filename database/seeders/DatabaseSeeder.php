<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $starter = Seller::create([
            'id' => 'seller_starter',
            'tier' => 'starter',
        ]);

        $pro = Seller::create([
            'id' => 'seller_pro',
            'tier' => 'pro',
        ]);

        $enterprise = Seller::create([
            'id' => 'seller_enterprise',
            'tier' => 'enterprise',
        ]);

        $transactions = [
            ['seller' => $pro, 'amount' => 10000, 'currency' => 'USD', 'provider' => 'stripe', 'fee' => 320, 'rate' => '0.0700', 'commission' => 700],
            ['seller' => $pro, 'amount' => 20000, 'currency' => 'USD', 'provider' => 'stripe', 'fee' => 610, 'rate' => '0.0700', 'commission' => 1400],
            ['seller' => $pro, 'amount' => 5000, 'currency' => 'EUR', 'provider' => 'paypal', 'fee' => 205, 'rate' => '0.0700', 'commission' => 350],
            ['seller' => $starter, 'amount' => 15000, 'currency' => 'USD', 'provider' => 'ideal', 'fee' => 0, 'rate' => '0.1000', 'commission' => 1500],
            ['seller' => $starter, 'amount' => 8000, 'currency' => 'GBP', 'provider' => 'stripe', 'fee' => 262, 'rate' => '0.1000', 'commission' => 800],
            ['seller' => $enterprise, 'amount' => 50000, 'currency' => 'USD', 'provider' => 'stripe', 'fee' => 1480, 'rate' => '0.0500', 'commission' => 2500],
            ['seller' => $enterprise, 'amount' => 12000, 'currency' => 'EUR', 'provider' => 'ideal', 'fee' => 0, 'rate' => '0.0500', 'commission' => 600],
            ['seller' => $pro, 'amount' => 3333, 'currency' => 'USD', 'provider' => 'paypal', 'fee' => 148, 'rate' => '0.0700', 'commission' => 233],
            ['seller' => $starter, 'amount' => 25000, 'currency' => 'USD', 'provider' => 'paypal', 'fee' => 885, 'rate' => '0.1000', 'commission' => 2500],
            ['seller' => $pro, 'amount' => 9000, 'currency' => 'EUR', 'provider' => 'ideal', 'fee' => 0, 'rate' => '0.0700', 'commission' => 630],
        ];

        foreach ($transactions as $i => $t) {
            $net = $t['amount'] - $t['fee'] - $t['commission'];
            Transaction::create([
                'id' => 'txn_demo_'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'seller_id' => $t['seller']->id,
                'customer_id' => 'cust_demo_'.($i + 1),
                'idempotency_key' => 'idem_demo_'.($i + 1),
                'amount' => $t['amount'],
                'currency' => $t['currency'],
                'payment_provider' => $t['provider'],
                'payment_provider_fee' => $t['fee'],
                'commission_rate' => $t['rate'],
                'commission_amount' => $t['commission'],
                'net_amount' => $net,
                'status' => 'completed',
            ]);
        }

        Model::reguard();
    }
}
