<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currency' => $this->currency,
            'total_transactions' => (int) $this->total_transactions,
            'total_gross_amount' => (int) $this->total_gross_amount,
            'total_commission' => (int) $this->total_commission,
            'total_net_amount' => (int) $this->total_net_amount,
        ];
    }
}
