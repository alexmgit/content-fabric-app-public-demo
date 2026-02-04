<?php

namespace App\Services\Adapters;

use App\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManager
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }

    public function transaction(callable $callback): void
    {
        DB::transaction($callback);
    }
}
