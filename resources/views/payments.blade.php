@extends('layouts.app')

@section('title', 'Payments')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Payments</h2>

            @php
                $payments = [
                    [
                        'id' => 1,
                        'order_id' => 1,
                        'method' => 'qris',
                        'transaction_id' => 'TX123456789',
                        'amount' => 55000,
                        'status' => 'pending',
                        'created_at' => '2023-09-15 11:00',
                    ],
                    [
                        'id' => 2,
                        'order_id' => 2,
                        'method' => 'gopay',
                        'transaction_id' => 'TX987654321',
                        'amount' => 20000,
                        'status' => 'success',
                        'created_at' => '2023-09-14 16:00',
                    ],
                ];
            @endphp

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b p-2">Payment ID</th>
                        <th class="border-b p-2">Order ID</th>
                        <th class="border-b p-2">Method</th>
                        <th class="border-b p-2">Transaction ID</th>
                        <th class="border-b p-2">Amount (Rp)</th>
                        <th class="border-b p-2">Status</th>
                        <th class="border-b p-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="border-b p-2">{{ $payment['id'] }}</td>
                            <td class="border-b p-2">{{ $payment['order_id'] }}</td>
                            <td class="border-b p-2 capitalize">{{ $payment['method'] }}</td>
                            <td class="border-b p-2">{{ $payment['transaction_id'] }}</td>
                            <td class="border-b p-2">{{ number_format($payment['amount'], 0, ',', '.') }}</td>
                            <td class="border-b p-2">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-semibold
                            @if ($payment['status'] === 'pending') bg-yellow-200 text-yellow-800
                            @elseif($payment['status'] === 'success') bg-green-200 text-green-800
                            @elseif($payment['status'] === 'failed') bg-red-200 text-red-800
                            @else bg-gray-200 text-gray-800 @endif">
                                    {{ ucfirst($payment['status']) }}
                                </span>
                            </td>
                            <td class="border-b p-2">{{ $payment['created_at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
