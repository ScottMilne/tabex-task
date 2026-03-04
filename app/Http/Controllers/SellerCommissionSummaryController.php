<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommissionSummaryResource;
use App\Models\Seller;
use App\Services\Transactions\CommissionSummaryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerCommissionSummaryController extends Controller
{
    public function __construct(
        private readonly CommissionSummaryService $commissionSummaryService,
    ) {}

    public function show(Request $request, string $id)
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(['monthly'])],
        ]);

        $seller = Seller::query()->findOrFail($id);
        $period = $validated['period'] ?? 'monthly';
        [$startAt, $endAt] = $this->commissionSummaryService->periodWindow($period);
        $rows = $this->commissionSummaryService->monthlyForSeller($seller, $startAt, $endAt);

        return response()->json([
            'seller_id' => $seller->getKey(),
            'period' => $period,
            'start_at' => $startAt->toISOString(),
            'end_at' => $endAt->toISOString(),
            'summaries' => CommissionSummaryResource::collection($rows)->resolve(),
        ]);
    }
}
