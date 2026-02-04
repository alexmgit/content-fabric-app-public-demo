<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\Billing\PaymentStatus;
use Illuminate\Support\Facades\Auth;
use App\Services\Payment\PaymentManager;
use YooKassa\Client;
use App\Models\Billing\Payment;
use App\Models\Billing\PaymentMethod;

class PaymentController extends Controller
{
    public function index() {
        $payments = Payment::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $payments->transform(function ($payment) {
            $payment->status_label = PaymentStatus::from($payment->status)->label();
            $payment->status_color = PaymentStatus::from($payment->status)->color();
            return $payment;
        });

        return Inertia::render('Billing/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function create() {
        return Inertia::render('Billing/Payments/Create/Create', [
            'amount' => request('amount', 100),
        ]);
    }

    public function createConfirmationToken(Client $client) {
        $amount = request('amount');
        $remember_payment_method = (bool)request('remember_payment_method');

        if ($amount < 100) {
            return redirect()->route('billing.payments.create')
                ->dangerBanner('Минимальная сумма пополнения: 100 ₽');
        }

        $paymentManager = new PaymentManager($client);

        $payment = $paymentManager->createPayment(Auth::user(), [
            'amount' => $amount,
        ], $remember_payment_method);

        return redirect()->route('billing.payments.create-form', $payment->uuid);
    }

    public function createForm($uuid) {
        $payment = Payment::where('uuid', $uuid)->first();

        return Inertia::render('Billing/Payments/Create/Form', [
            'confirmation_token' => $payment->confirmation_token,
            'return_url' => route('billing.payments.return-url', $payment->uuid),
        ]);
    }

    public function returnUrl($uuid) {
        $payment = Payment::where('uuid', $uuid)->first();

        if ($payment->status === PaymentStatus::WAITING->value) {
            $payment->update([
                'status' => PaymentStatus::PROCESSING->value,
            ]);
        }
        
        return Inertia::render('Billing/Payments/Create/Return', [
            'payment' => [
                'uuid' => $payment->uuid,
                'amount' => $payment->amount,
                'status' => $payment->status,
            ],
        ]);
    }

    public function returnUrlCallback($uuid) {
        $payment = Payment::where('uuid', $uuid)->first();

        return response()->json([
            'status' => $payment->status,
        ]);
    }

    public function methods() {
        $methods = PaymentMethod::where('user_id', Auth::user()->id)->get();

        return Inertia::render('Billing/Payments/Methods/Index', [
            'methods' => $methods,
        ]);
    }

    public function methodsDestroy($id) {
        $method = PaymentMethod::find($id);

        //@TODO проверка на принадлежность пользователя через policies
        if ($method->user_id !== Auth::user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Вы не можете удалить этот способ оплаты',
            ]);
        }

        $method->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }    
}
