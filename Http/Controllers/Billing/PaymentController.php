<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreatePaymentRequest;
use App\Http\Resources\Billing\PaymentMethodResource;
use App\Http\Resources\Billing\PaymentResource;
use App\Policies\Billing\PaymentMethodPolicy;
use App\Policies\Billing\PaymentPolicy;
use App\Repositories\Billing\PaymentMethodRepository;
use App\Repositories\Billing\PaymentRepository;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use YooKassa\Client;

class PaymentController extends Controller
{
    public function index(Request $request, PaymentRepository $paymentRepository): Response
    {
        $user = $request->user();
        $this->authorizePolicy(PaymentPolicy::class, 'viewAny', $user);
        $payments = $paymentRepository->getAllForUser($user->id);

        return Inertia::render('Billing/Payments/Index', [
            'payments' => PaymentResource::collection($payments),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Billing/Payments/Create/Create', [
            'amount' => max((int) $request->query('amount', 100), 100),
        ]);
    }

    public function createConfirmationToken(
        CreatePaymentRequest $request,
        Client $client,
    ): RedirectResponse {
        $this->authorizePolicy(PaymentPolicy::class, 'create', $request->user());
        $paymentManager = new PaymentManager($client);
        $validated = $request->validated();
        $payment = $paymentManager->createPayment($request->user(), [
            'amount' => $validated['amount'],
        ], (bool) ($validated['remember_payment_method'] ?? false));

        return redirect()->route('billing.payments.create-form', $payment->uuid);
    }

    public function createForm(
        Request $request,
        string $uuid,
        PaymentRepository $paymentRepository,
    ): Response
    {
        $payment = $paymentRepository->findByUuidForUserOrFail($uuid, $request->user()->id);
        $this->authorizePolicy(PaymentPolicy::class, 'view', $request->user(), $payment);

        return Inertia::render('Billing/Payments/Create/Form', [
            'confirmation_token' => $payment->confirmation_token,
            'return_url' => route('billing.payments.return-url', $payment->uuid),
        ]);
    }

    public function returnUrl(
        Request $request,
        string $uuid,
        PaymentRepository $paymentRepository,
        PaymentStatusService $paymentStatusService,
    ): Response
    {
        $payment = $paymentRepository->findByUuidForUserOrFail($uuid, $request->user()->id);
        $this->authorizePolicy(PaymentPolicy::class, 'view', $request->user(), $payment);
        $payment = $paymentStatusService->markAsProcessingIfWaiting($payment);
        
        return Inertia::render('Billing/Payments/Create/Return', [
            'payment' => PaymentResource::make($payment),
        ]);
    }

    public function returnUrlCallback(
        Request $request,
        string $uuid,
        PaymentRepository $paymentRepository,
    ): JsonResponse
    {
        $payment = $paymentRepository->findByUuidForUserOrFail($uuid, $request->user()->id);
        $this->authorizePolicy(PaymentPolicy::class, 'view', $request->user(), $payment);

        return response()->json([
            'status' => $payment->status,
        ]);
    }

    public function methods(Request $request, PaymentMethodRepository $paymentMethodRepository): Response
    {
        $this->authorizePolicy(PaymentMethodPolicy::class, 'viewAny', $request->user());
        $methods = $paymentMethodRepository->getAllForUser($request->user()->id);

        return Inertia::render('Billing/Payments/Methods/Index', [
            'methods' => PaymentMethodResource::collection($methods),
        ]);
    }

    public function methodsDestroy(
        Request $request,
        int $id,
        PaymentMethodRepository $paymentMethodRepository,
    ): JsonResponse
    {
        $method = $paymentMethodRepository->findForUserOrFail($id, $request->user()->id);
        $this->authorizePolicy(PaymentMethodPolicy::class, 'delete', $request->user(), $method);

        $method->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
