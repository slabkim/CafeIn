<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();
        $role = $user?->role?->name;

        $order = null;
        $pendingOrders = collect();
        $subtotal = null;
        $serviceFee = 0;
        $latestPayment = null;

        if ($role === 'Customer') {
            $order = Order::with(['orderItems.menu', 'payments'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->orderByDesc('created_at')
                ->first();
        } elseif (in_array($role, ['Kasir', 'Admin'], true)) {
            // Allow kasir/admin to pick a specific order id to process; do not mix different users implicitly
            $orderId = (int) $request->query('order_id', 0);
            if ($orderId > 0) {
                $order = Order::with(['orderItems.menu', 'payments', 'user'])
                    ->where('id', $orderId)
                    ->whereIn('status', ['pending', 'processing'])
                    ->first();
            }
            // Provide a short list to select from
            $pendingOrders = Order::with('user')
                ->whereIn('status', ['pending', 'processing'])
                ->orderBy('created_at')
                ->limit(25)
                ->get();
        }

        if ($order) {
            $subtotal = $order->orderItems->sum('subtotal');
            $serviceFee = (float) ($order->metadata['service_fee'] ?? 0);
            $latestPayment = $order->payments->sortByDesc('created_at')->first();
        }

        return view('payments', [
            'order' => $order,
            'pendingOrders' => $pendingOrders,
            'paymentMethods' => $this->paymentMethods($role),
            'subtotal' => $subtotal,
            'serviceFee' => $serviceFee,
            'latestPayment' => $latestPayment,
        ]);
    }

    private function initMidtrans(): void
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production', false);
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = config('midtrans.is_3ds', true);
    }

    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();
        $methods = collect($this->paymentMethods($user->role?->name))->pluck('key')->all();

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'method' => ['required', Rule::in($methods)],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'transaction_id' => ['nullable', 'string', 'max:191'],
            'payment_type' => ['nullable', 'string', 'max:100'],
            'transaction_status' => ['nullable', 'string', 'max:100'],
            'gross_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = Order::with(['payments', 'user'])->findOrFail($validated['order_id']);

        if ($user->role?->name !== 'Admin' && $user->role?->name !== 'Kasir' && $order->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($order->status, ['pending', 'processing', 'paid'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat diproses untuk pembayaran.',
            ], 422);
        }

        DB::transaction(function () use ($order, $validated, $user) {
            $role = $user->role?->name;

            // Update order metadata (notes for all, customer_name for kasir/admin)
            $meta = is_array($order->metadata) ? $order->metadata : [];
            $notes = array_key_exists('notes', $validated)
                ? trim((string) $validated['notes'])
                : null;

            if (in_array($role, ['Admin', 'Kasir'], true) && !empty($validated['customer_name'])) {
                $meta['customer_name'] = $validated['customer_name'];
            }
            if (array_key_exists('notes', $validated)) {
                // prefer 'notes' key; keep backwards compat with 'note'
                if ($notes === '' || $notes === null) {
                    unset($meta['notes'], $meta['note']);
                } else {
                    $meta['notes'] = $notes;
                    $meta['note'] = $notes;
                }
            }
            if ($meta !== $order->metadata) {
                $order->update(['metadata' => $meta]);
            }

            $transactionId = $validated['transaction_id'] ?? null;
            $paymentType = $validated['payment_type'] ?? null;
            $normalizedMethod = $this->normalizeMethod($validated['method'], $paymentType);

            $payload = [
                'method' => $normalizedMethod,
                'transaction_id' => $transactionId,
                'amount' => $order->total_price,
                'fee' => 0,
                'currency' => $order->currency ?? 'IDR',
                'status' => 'success',
                'paid_at' => now(),
                'provider' => strtoupper($paymentType ?? $validated['method']),
                'gateway_payload' => array_filter([
                    'transaction_status' => $validated['transaction_status'] ?? null,
                    'payment_type' => $paymentType,
                    'gross_amount' => $validated['gross_amount'] ?? null,
                ]),
            ];

            if ($transactionId) {
                $order->payments()->updateOrCreate(
                    ['transaction_id' => $transactionId],
                    $payload,
                );
            } else {
                $order->payments()->create($payload);
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat.',
        ]);
    }

    public function midtransWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $orderNumber = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signature = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;

        if (! $orderNumber || ! $statusCode || ! $grossAmount || ! $signature) {
            return response()->json(['message' => 'Payload tidak lengkap.'], 400);
        }

        $expectedSignature = hash('sha512', $orderNumber . $statusCode . $grossAmount . config('midtrans.server_key'));
        if (! hash_equals($expectedSignature, $signature)) {
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $order = Order::with('payments')->where('order_number', $orderNumber)->first();
        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        $isCancelled = in_array($transactionStatus, ['cancel', 'expire', 'failure', 'deny'], true);
        $isPaid = in_array($transactionStatus, ['capture', 'settlement'], true);

        DB::transaction(function () use ($order, $payload, $isCancelled, $isPaid, $transactionStatus, $paymentType, $transactionId) {
            $meta = is_array($order->metadata) ? $order->metadata : [];
            $meta['midtrans_status'] = $transactionStatus;
            $meta['midtrans_payment_type'] = $paymentType;

            $payment = $transactionId
                ? Payment::firstWhere('transaction_id', $transactionId)
                : $order->payments()->latest()->first();
            $normalizedMethod = $this->normalizeMethod('midtrans', $paymentType);

            $paymentPayload = [
                'order_id' => $order->id,
                'method' => $normalizedMethod,
                'transaction_id' => $transactionId,
                'amount' => $order->total_price,
                'fee' => $order->payments()->latest()->value('fee') ?? 0,
                'currency' => $order->currency ?? 'IDR',
                'status' => $isPaid ? 'success' : ($isCancelled ? 'failed' : 'pending'),
                'paid_at' => $isPaid ? ($order->paid_at ?? now()) : null,
                'provider' => $paymentType ? strtoupper($paymentType) : $payment?->provider,
                'gateway_payload' => Arr::only($payload, [
                    'transaction_status',
                    'transaction_id',
                    'order_id',
                    'status_code',
                    'payment_type',
                    'gross_amount',
                    'fraud_status',
                    'signature_key',
                ]),
            ];

            if ($payment) {
                $payment->update($paymentPayload);
            } else {
                Payment::create($paymentPayload);
            }

            if ($isPaid && in_array($order->status, ['pending', 'processing'], true)) {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => $order->paid_at ?? now(),
                    'metadata' => $meta,
                ]);
            } elseif ($isCancelled && ! in_array($order->status, ['completed', 'cancelled'], true)) {
                foreach ($order->payments as $p) {
                    if ($p->status === 'pending') {
                        $p->update(['status' => 'failed']);
                    }
                }
                $order->update([
                    'status' => 'cancelled',
                    'metadata' => $meta,
                ]);
            } else {
                // update meta only
                if ($meta !== $order->metadata) {
                    $order->update(['metadata' => $meta]);
                }
            }
        });

        return response()->json(['message' => 'Notification processed.']);
    }

    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);

        if ($user->role?->name !== 'Admin' && $user->role?->name !== 'Kasir' && $order->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena sudah diproses.',
            ], 422);
        }

        DB::transaction(function () use ($order, $validated) {
            $meta = is_array($order->metadata) ? $order->metadata : [];
            $reason = array_key_exists('reason', $validated)
                ? trim((string) $validated['reason'])
                : null;

            if ($reason !== '' && $reason !== null) {
                $meta['cancel_reason'] = $reason;
            }

            foreach ($order->payments as $payment) {
                if ($payment->status === 'pending') {
                    $payment->update(['status' => 'failed']);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'metadata' => $meta,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran dibatalkan dan pesanan ditandai batal.',
        ]);
    }

    private function normalizeMethod(string $selectedMethod, ?string $paymentType = null): string
    {
        if ($selectedMethod !== 'midtrans') {
            return $selectedMethod;
        }

        return match ($paymentType) {
            'qris' => 'qris',
            'gopay' => 'gopay',
            'ovo' => 'ovo',
            'dana' => 'dana',
            default => 'qris',
        };
    }

    public function snapToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->name ?? null;

        $serverKey = config('midtrans.server_key');
        $clientKey = config('midtrans.client_key');
        if (empty($serverKey) || empty($clientKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi Midtrans belum lengkap.',
            ], 422);
        }

        $orderId = (int) $request->query('order_id');
        if ($orderId <= 0) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        $query = Order::with(['orderItems.menu', 'user'])
            ->where('id', $orderId)
            ->whereIn('status', ['pending', 'processing']);

        if ($role === 'Customer') {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();
        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak dapat diproses untuk Midtrans.',
            ], 422);
        }

        $this->initMidtrans();

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->metadata['customer_name'] ?? ($order->user?->name ?? 'Guest'),
                'email' => $order->user?->email ?? null,
            ],
        ];

        try {
            $token = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap token error', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai pembayaran Midtrans.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function saveInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->name;

        // Customer may update only their own order notes
        if ($role === 'Customer') {
            $validated = $request->validate([
                'order_id' => ['required', 'integer', 'exists:orders,id'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);

            $order = Order::where('id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $meta = is_array($order->metadata) ? $order->metadata : [];
            if (array_key_exists('notes', $validated)) {
                $meta['notes'] = $validated['notes'];
            }

            $order->update(['metadata' => $meta]);

            return response()->json([
                'success' => true,
                'message' => 'Catatan pesanan berhasil disimpan.',
            ]);
        }

        if (!in_array($role, ['Admin', 'Kasir'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);

        $meta = is_array($order->metadata) ? $order->metadata : [];
        $notes = array_key_exists('notes', $validated)
            ? trim((string) $validated['notes'])
            : null;

        if (array_key_exists('customer_name', $validated)) {
            $meta['customer_name'] = $validated['customer_name'];
        }
        if (array_key_exists('notes', $validated)) {
            if ($notes === '' || $notes === null) {
                unset($meta['notes'], $meta['note']);
            } else {
                $meta['notes'] = $notes;
                $meta['note'] = $notes;
            }
        }

        $order->update(['metadata' => $meta]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi order berhasil disimpan.',
        ]);
    }

    private function paymentMethods(?string $role = null): array
    {
        $midtransMethod = [
            'key' => 'midtrans',
            'label' => 'Midtrans',
            'description' => 'Bayar online (Snap: kartu, e-wallet, transfer)',
        ];

        // Untuk customer, sediakan satu opsi pembayaran online via Midtrans (Snap)
        if ($role === 'Customer') {
            return [$midtransMethod];
        }

        // Untuk Kasir/Admin, sediakan Midtrans + metode manual internal
        $methods = [
            $midtransMethod,
            [
                'key' => 'qris',
                'label' => 'QRIS',
                'description' => 'Scan QR untuk semua e-wallet',
            ],
            [
                'key' => 'gopay',
                'label' => 'GoPay',
                'description' => 'Pembayaran lewat GoPay',
            ],
            [
                'key' => 'ovo',
                'label' => 'OVO',
                'description' => 'Pembayaran lewat OVO',
            ],
            [
                'key' => 'dana',
                'label' => 'DANA',
                'description' => 'Pembayaran lewat DANA',
            ],
        ];

        // Hanya role Kasir yang boleh menggunakan metode cash
        if ($role === 'Kasir') {
            $methods[] = [
                'key' => 'cash',
                'label' => 'Cash',
                'description' => 'Bayar tunai di kasir',
            ];
        }

        return $methods;
    }
}
