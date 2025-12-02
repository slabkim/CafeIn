<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

            $order->payments()->create([
                'method' => $validated['method'],
                'amount' => $order->total_price,
                'currency' => $order->currency ?? 'IDR',
                'status' => 'success',
                'paid_at' => now(),
                'provider' => strtoupper($validated['method']),
            ]);

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

    public function snapToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->role?->name ?? null;

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

        $order = $query->firstOrFail();

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

        $token = Snap::getSnapToken($params);

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
        // Untuk customer, sediakan satu opsi pembayaran online via Midtrans (Snap)
        if ($role === 'Customer') {
            return [
                [
                    'key' => 'midtrans',
                    'label' => 'Pembayaran Online',
                    'description' => 'Bayar dengan Midtrans (kartu, e-wallet, dll)',
                ],
            ];
        }

        // Untuk Kasir/Admin, gunakan metode manual internal
        $methods = [
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

