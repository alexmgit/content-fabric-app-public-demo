<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizePolicy(string $policyClass, string $ability, mixed ...$arguments): void
    {
        $policy = app($policyClass);

        if (! method_exists($policy, $ability) || ! $policy->{$ability}(...$arguments)) {
            throw new AuthorizationException();
        }
    }
}
