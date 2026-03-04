<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Transactions\TransactionProcessor;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionProcessor $transactionProcessor,
    ) {}

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionProcessor->process($request->validated());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode($transaction->wasRecentlyCreated ? 201 : 200);
    }

    public function show(string $id): TransactionResource
    {
        $transaction = Transaction::query()->findOrFail($id);

        return new TransactionResource($transaction);
    }
}
