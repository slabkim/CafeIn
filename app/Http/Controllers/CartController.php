<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private float $serviceFeeRate = 0.05;

    private function calculateCartTotal(Cart $cart): float
    {
        return round((float) $cart->cartItems()
            ->with('menu')
            ->get()
            ->sum(function (CartItem $item) {
                return $item->menu->price * $item->quantity;
            }), 2);
    }

    public function __invoke(Request $request): View
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $cartItems = $cart->cartItems()->with('menu')->get();

        $total = 0;
        $formattedCartItems = [];
        foreach ($cartItems as $item) {
            $subtotal = $item->menu->price * $item->quantity;
            $total += $subtotal;
            $formattedCartItems[] = [
                'menu' => $item->menu,
                'cart_item_id' => $item->id,
                'quantity' => $item->quantity,
                'subtotal' => round($subtotal, 2),
            ];
        }

        $total = round($total, 2);
        $serviceFee = $this->calculateServiceFee($total);
        $grandTotal = round($total + $serviceFee, 2);

        return view('cart', [
            'cartItems' => $formattedCartItems,
            'total' => $total,
            'serviceFee' => $serviceFee,
            'grandTotal' => $grandTotal,
            'serviceFeeRate' => $this->serviceFeeRate,
        ]);
    }

    public function add(Request $request, Menu $menu)
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('menu_id', $menu->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'menu_id' => $menu->id,
                'quantity' => $quantity,
            ]);
        }

        $count = $cart->cartItems()->sum('quantity');
        $cartTotal = $this->calculateCartTotal($cart);

        return response()->json([
            'success' => true,
            'count' => $count,
            'cartTotal' => $cartTotal,
        ]);
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }
        $user = Auth::user();
        // ensure the cart item belongs to this user's cart
        $cart = Cart::find($cartItem->cart_id);
        if (!$cart || $cart->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();
        $cartItem->load('menu');

        $count = $cart->cartItems()->sum('quantity');
        $cartTotal = $this->calculateCartTotal($cart);
        $itemSubtotal = round($cartItem->menu->price * $cartItem->quantity, 2);

        return response()->json([
            'success' => true,
            'count' => $count,
            'itemSubtotal' => $itemSubtotal,
            'cartTotal' => $cartTotal,
        ]);
    }

    public function remove(CartItem $cartItem)
    {
        $user = Auth::user();
        $cart = Cart::find($cartItem->cart_id);
        if (!$cart || $cart->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        $count = $cart->cartItems()->sum('quantity');
        $cartTotal = $this->calculateCartTotal($cart);

        return response()->json([
            'success' => true,
            'count' => $count,
            'cartTotal' => $cartTotal,
        ]);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::with('cartItems.menu')->firstWhere('user_id', $user->id);

        if (! $cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang masih kosong.',
            ], 422);
        }

        $note = trim((string) $request->input('note', ''));
        $note = $note !== '' ? $note : null;

        $subtotal = round($cart->cartItems->sum(function (CartItem $item) {
            return $item->menu->price * $item->quantity;
        }), 2);

        $serviceFee = $this->calculateServiceFee($subtotal);
        $grandTotal = round($subtotal + $serviceFee, 2);

        $order = DB::transaction(function () use ($cart, $user, $note, $subtotal, $serviceFee, $grandTotal) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'total_price' => $grandTotal,
                'currency' => 'IDR',
                'status' => 'pending',
                'metadata' => array_filter([
                    'subtotal' => $subtotal,
                    'service_fee' => $serviceFee,
                    'service_fee_rate' => $this->serviceFeeRate,
                    'note' => $note,
                ], fn ($value) => ! is_null($value)),
            ]);

            foreach ($cart->cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item->menu_id,
                    'quantity' => $item->quantity,
                    'subtotal' => round($item->menu->price * $item->quantity, 2),
                ]);
            }

            $cart->cartItems()->delete();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.',
            'redirect' => route('payments'),
            'orderId' => $order->id,
        ]);
    }

    private function calculateServiceFee(float $amount): float
    {
        return round($amount * $this->serviceFeeRate, 2);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'CFN-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}



