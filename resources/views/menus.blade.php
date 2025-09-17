@extends('layouts.app')

@section('title', 'Menus')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900">Our Menus</h2>
            </div>

            @php
                $categories = [
                    [
                        'id' => 1,
                        'name' => 'Kopi',
                        'menus' => [
                            [
                                'id' => 1,
                                'name' => 'Espresso',
                                'description' => 'Strong coffee shot',
                                'price' => 15000,
                                'stock' => 50,
                                'image' => 'https://www.beach-garden.com/wp-content/uploads/2021/07/espresso.jpeg',
                            ],
                            [
                                'id' => 2,
                                'name' => 'Cappuccino',
                                'description' => 'Coffee with milk foam',
                                'price' => 20000,
                                'stock' => 30,
                                'image' =>
                                    'data:image/jpeg;base64,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',
                            ],
                        ],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Non-Kopi',
                        'menus' => [
                            [
                                'id' => 3,
                                'name' => 'Green Tea',
                                'description' => 'Refreshing green tea',
                                'price' => 12000,
                                'stock' => 40,
                                'image' => 'https://via.placeholder.com/150',
                            ],
                            [
                                'id' => 4,
                                'name' => 'Hot Chocolate',
                                'description' => 'Warm chocolate drink',
                                'price' => 18000,
                                'stock' => 25,
                                'image' => 'https://via.placeholder.com/150',
                            ],
                        ],
                    ],
                    [
                        'id' => 3,
                        'name' => 'Makanan',
                        'menus' => [
                            [
                                'id' => 5,
                                'name' => 'Croissant',
                                'description' => 'Buttery pastry',
                                'price' => 10000,
                                'stock' => 20,
                                'image' => 'https://via.placeholder.com/150',
                            ],
                            [
                                'id' => 6,
                                'name' => 'Sandwich',
                                'description' => 'Ham and cheese sandwich',
                                'price' => 25000,
                                'stock' => 15,
                                'image' => 'https://via.placeholder.com/150',
                            ],
                        ],
                    ],
                ];
            @endphp

            @foreach ($categories as $category)
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ $category['name'] }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($category['menus'] as $menu)
                            <div class="bg-gray-50 rounded-lg p-4 shadow">
                                <img src="{{ $menu['image'] }}" alt="{{ $menu['name'] }}"
                                    class="w-48 h-48 object-cover rounded mb-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $menu['name'] }}</h4>
                                <p class="text-gray-600 text-sm mb-2">{{ $menu['description'] }}</p>
                                <p class="text-gray-800 font-semibold">Rp {{ number_format($menu['price'], 0, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-500">Stock: {{ $menu['stock'] }}</p>
                                <button class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Add to Order
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
