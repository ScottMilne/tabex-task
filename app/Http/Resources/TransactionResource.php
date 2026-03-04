<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Transaction */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_id' => $this->id,
            'seller_id' => $this->seller_id,
            'gross_amount' => $this->amount,
            'currency' => $this->currency,
            'payment_provider' => $this->payment_provider,
            'payment_provider_fee' => $this->payment_provider_fee,
            'commission_rate' => (string) $this->commission_rate,
            'commission_amount' => $this->commission_amount,
            'net_amount' => $this->net_amount,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
