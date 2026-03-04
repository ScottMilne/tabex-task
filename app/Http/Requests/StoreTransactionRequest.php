<?php

namespace App\Http\Requests;

use App\Models\Seller;
use App\Services\Transactions\ExchangeRateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ExchangeRateService $exchangeRateService */
        $exchangeRateService = app(ExchangeRateService::class);

        return [
            'seller_id' => ['required', 'string', Rule::exists(Seller::class, 'id')],
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3', Rule::in($exchangeRateService->supportedCurrencies())],
            'payment_provider' => ['required', 'string', Rule::in(['stripe', 'paypal', 'ideal'])],
            'customer_id' => ['required', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $currency = $this->input('currency');
        $paymentProvider = $this->input('payment_provider');

        if (is_string($currency)) {
            $this->merge([
                'currency' => strtoupper($currency),
            ]);
        }

        if (is_string($paymentProvider)) {
            $this->merge([
                'payment_provider' => strtolower($paymentProvider),
            ]);
        }
    }
}
