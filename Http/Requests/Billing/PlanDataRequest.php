<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class PlanDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'integer'],
            'is_trial' => ['sometimes', 'boolean'],
            'promocode' => ['nullable', 'string', 'max:255'],
        ];
    }
}
