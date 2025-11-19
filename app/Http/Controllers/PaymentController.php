<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
            'paymentMethods' => $this->paymentMethods(),
            'subtotal' => $subtotal,
            'serviceFee' => $serviceFee,
            'latestPayment' => $latestPayment,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $methods = collect($this->paymentMethods())->pluck('key')->all();

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'method' => ['required', Rule::in($methods)],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::with(['payments', 'user'])->findOrFail($validated['order_id']);
        $user = $request->user();

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
            // For Kasir/Admin, allow updating customer name and notes on order metadata (does not change user_id)
            if (in_array($user->role?->name, ['Admin', 'Kasir'], true)) {
                $meta = is_array($order->metadata) ? $order->metadata : [];
                if (!empty($validated['customer_name'])) {
                    $meta['customer_name'] = $validated['customer_name'];
                }
                if (array_key_exists('notes', $validated)) {
                    // prefer 'notes' key; keep backwards compat with 'note'
                    $meta['notes'] = $validated['notes'];
                }
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

    public function saveInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role?->name, ['Admin', 'Kasir'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::with('payments')->findOrFail($validated['order_id']);

        $meta = is_array($order->metadata) ? $order->metadata : [];
        if (array_key_exists('customer_name', $validated)) {
            $meta['customer_name'] = $validated['customer_name'];
        }
        if (array_key_exists('notes', $validated)) {
            $meta['notes'] = $validated['notes'];
        }

        $order->update(['metadata' => $meta]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi order berhasil disimpan.',
        ]);
    }

    private function paymentMethods(): array
    {
        return [
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
            [
                'key' => 'cash',
                'label' => 'Cash',
                'description' => 'Bayar tunai di kasir',
            ],
        ];
    }
}

