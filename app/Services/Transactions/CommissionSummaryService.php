<?php

namespace App\Services\Transactions;

use App\Models\Seller;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CommissionSummaryService
{
    /**
     * @return Collection<int, object>
     */
    public function monthlyForSeller(Seller $seller, CarbonImmutable $startAt, CarbonImmutable $endAt): Collection
    {
        return Transaction::query()
            ->select([
                'currency',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(amount) as total_gross_amount'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('SUM(net_amount) as total_net_amount'),
            ])
            ->where('seller_id', $seller->getKey())
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startAt, $endAt])
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function periodWindow(string $period): array
    {
        if ($period !== 'monthly') {
            throw new InvalidArgumentException('Unsupported period.');
        }

        return $this->resolveMonthlyWindow();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveMonthlyWindow(): array
    {
        $now = CarbonImmutable::now();

        return [
            $now->startOfMonth(),
            $now->endOfMonth(),
        ];
    }
}
