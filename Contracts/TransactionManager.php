<?php

namespace App\Contracts;

interface TransactionManager
{
    public function begin(): void;

    public function commit(): void;

    public function rollBack(): void;

    public function transaction(callable $callback): void;
}
