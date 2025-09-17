@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Orders</h2>

            @php
                $orders = [
                    [
                        'id' => 1,
                        'user' => 'John Doe',
                        'total_price' => 55000,
                        'status' => 'pending',
                        'items' => [
                            ['menu_name' => 'Espresso', 'quantity' => 2, 'subtotal' => 30000],
                            ['menu_name' => 'Croissant', 'quantity' => 1, 'subtotal' => 10000],
                            ['menu_name' => 'Green Tea', 'quantity' => 1, 'subtotal' => 15000],
                        ],
                        'created_at' => '2023-09-15 10:30',
                    ],
                    [
                        'id' => 2,
                        'user' => 'Jane Smith',
                        'total_price' => 20000,
                        'status' => 'completed',
                        'items' => [['menu_name' => 'Cappuccino', 'quantity' => 1, 'subtotal' => 20000]],
                        'created_at' => '2023-09-14 15:45',
                    ],
                ];
            @endphp

            @foreach ($orders as $order)
                <div class="mb-8 border rounded-lg shadow p-4">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">Order #{{ $order['id'] }} by {{ $order['user'] }}</h3>
                            <p class="text-sm text-gray-500">Placed on {{ $order['created_at'] }}</p>
                        </div>
                        <div>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold
                        @if ($order['status'] === 'pending') bg-yellow-200 text-yellow-800
                        @elseif($order['status'] === 'completed') bg-green-200 text-green-800
                        @elseif($order['status'] === 'paid') bg-blue-200 text-blue-800
                        @elseif($order['status'] === 'cancelled') bg-red-200 text-red-800
                        @else bg-gray-200 text-gray-800 @endif">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b p-2">Menu Item</th>
                                <th class="border-b p-2">Quantity</th>
                                <th class="border-b p-2">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order['items'] as $item)
                                <tr>
                                    <td class="border-b p-2">{{ $item['menu_name'] }}</td>
                                    <td class="border-b p-2">{{ $item['quantity'] }}</td>
                                    <td class="border-b p-2">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="text-right font-semibold p-2">Total</td>
                                <td class="font-semibold p-2">{{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
