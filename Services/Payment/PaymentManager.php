<?php

namespace App\Services\Payment;

use YooKassa\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Billing\Payment;
use App\Enums\Billing\PaymentStatus;
use App\Models\User;
use App\Enums\Billing\Currency;
use App\Enums\Billing\YooKassaConfirmationType;
use App\Enums\Billing\YooKassaPaymentMode;
use App\Enums\Billing\YooKassaPaymentSubject;
use App\Enums\Billing\YooKassaVatCode;

class PaymentManager
{
    public function __construct(
        public Client $client,
    ) {
    }

    public function createPayment(User $user, array $data, bool $remember_payment_method = false): Payment
    {
        try {
            DB::beginTransaction();

            $payment = $this->createPaymentRecord($user, $data);
            $payload = $this->buildPayload($payment, $user, null, $remember_payment_method);
            $response = $this->client->createPayment($payload, $payment->uuid);

            $paymentId = $response->getId();
            $confirmationToken= $response->getConfirmation()->getConfirmationToken();

            $payment->update([
                'payment_id' => $paymentId,
                'confirmation_token' => $confirmationToken,
                'status' => PaymentStatus::WAITING->value,
                'extra_data' => json_encode($response->toArray()),
            ]);

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function createPaymentWithToken(User $user, array $data, string $token): Payment
    {
        try {
            DB::beginTransaction();

            $payment = $this->createPaymentRecord($user, $data);
            $payload = $this->buildPayload($payment, $user, $token, false);
            $response = $this->client->createPayment($payload, $payment->uuid);

            $paymentId = $response->getId();

            $payment->update([
                'payment_id' => $paymentId,
                'status' => PaymentStatus::WAITING->value,
                'extra_data' => json_encode($response->toArray()),
            ]);

            DB::commit();

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function createPaymentRecord(User $user, array $data): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id,
            'amount' => $data['amount'],
            'currency' => Currency::RUB->value,
            'description' => 'Пополнение лицевого счета №' . $user->id,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    private function buildPayload(Payment $payment, User $user, ?string $token, bool $rememberPaymentMethod): array
    {
        $payload = [
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
                        'vat_code' => YooKassaVatCode::VAT_2->value,
                        'payment_mode' => YooKassaPaymentMode::FULL_PAYMENT->value,
                        'payment_subject' => YooKassaPaymentSubject::SERVICE->value,
                    ],
                ],
            ],
        ];

        if ($token !== null) {
            $payload['payment_method_id'] = $token;
        } else {
            $payload['confirmation'] = [
                'type' => YooKassaConfirmationType::EMBEDDED->value,
            ];
            $payload['save_payment_method'] = $rememberPaymentMethod;
        }

        return $payload;
    }
}
