<?php

namespace App\Services\Payment;

use YooKassa\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Billing\Payment;
use App\Enums\Billing\PaymentStatus;
use App\Models\User;

class PaymentManager
{
    public function __construct(
        public readonly Client $client,
    ) {
    }

    public function createPayment(User $user, array $data, bool $remember_payment_method = false): Payment
    {
        return $this->createRemotePayment($user, $data, [
            'confirmation' => [
                'type' => 'embedded',
            ],
            'save_payment_method' => $remember_payment_method,
        ], true);
    }

    public function createPaymentWithToken(User $user, array $data, string $token): Payment
    {
        return $this->createRemotePayment($user, $data, [
            'payment_method_id' => $token,
        ], false);
    }

    private function createRemotePayment(User $user, array $data, array $extraPayload, bool $storeConfirmationToken): Payment
    {
        return DB::transaction(function () use ($user, $data, $extraPayload, $storeConfirmationToken) {
            $payment = $this->createLocalPayment($user, $data);
            $response = $this->client->createPayment(
                $this->buildPaymentPayload($payment, $user, $extraPayload),
                $payment->uuid
            );

            $this->updatePaymentFromResponse($payment, $response, $storeConfirmationToken);

            return $payment->fresh();
        });
    }

    private function createLocalPayment(User $user, array $data): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id,
            'amount' => $data['amount'],
            'currency' => 'RUB',
            'description' => 'Пополнение лицевого счета №' . $user->id,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    private function updatePaymentFromResponse(Payment $payment, mixed $response, bool $storeConfirmationToken): void
    {
        $payload = [
            'payment_id' => $response->getId(),
            'status' => PaymentStatus::WAITING->value,
            'extra_data' => $response->toArray(),
        ];

        if ($storeConfirmationToken && $response->getConfirmation()) {
            $payload['confirmation_token'] = $response->getConfirmation()->getConfirmationToken();
        }

        $payment->update($payload);
    }

    private function buildPaymentPayload(Payment $payment, User $user, array $extra = []): array
    {
        return array_merge([
            'amount' => [
                'value' => $payment->amount,
                'currency' => $payment->currency,
            ],
            'capture' => true,
            'description' => $payment->description,
            'receipt' => [
                'customer' => [
                    'full_name' => $user->name,
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'description' => $payment->description,
                        'quantity' => '1.00',
                        'amount' => [
                            'value' => $payment->amount,
                            'currency' => $payment->currency,
                        ],
                        'vat_code' => '2',
                        'payment_mode' => 'full_payment',
                        'payment_subject' => 'service',
                    ],
                ],
            ],
        ], $extra);
    }
}
