<?php

namespace App\Http\Resources\Billing;

use App\Enums\Billing\TransactionType;
use App\Models\Billing\Transaction;

class TransactionResource
{
    public static function collection(iterable $transactions): array
    {
        $items = [];

        foreach ($transactions as $transaction) {
            $items[] = self::make($transaction);
        }

        return $items;
    }

    public static function make(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'description' => $transaction->description,
            'direction' => $transaction->direction,
            'type' => $transaction->type,
            'type_label' => TransactionType::from($transaction->type)->label(),
            'created_at' => $transaction->created_at,
        ];
    }
}
