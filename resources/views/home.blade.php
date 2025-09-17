@extends('layouts.app')

@section('title', 'Home - CafeIn')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                <div class="p-8 text-center">
                    <h1 class="text-4xl font-bold mb-4">Welcome to CafeIn</h1>
                    <p class="text-xl mb-6">Your favorite place for coffee, drinks, and delicious food</p>
                    <a href="{{ route('menus') }}"
                        class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                        View Our Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Featured Categories -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-6xl mb-4">☕</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Kopi</h3>
                <p class="text-gray-600">Enjoy our premium coffee selections</p>
                <a href="{{ route('menus') }}"
                    class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Explore</a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-6xl mb-4">🧋</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Non-Kopi</h3>
                <p class="text-gray-600">Refreshing drinks for every taste</p>
                <a href="{{ route('menus') }}"
                    class="mt-4 inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Explore</a>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Makanan</h3>
                <p class="text-gray-600">Delicious food to complement your drinks</p>
                <a href="{{ route('menus') }}"
                    class="mt-4 inline-block bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Explore</a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-blue-600">50+</div>
                <p class="text-gray-600">Menu Items</p>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-green-600">100+</div>
                <p class="text-gray-600">Happy Customers</p>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-purple-600">24/7</div>
                <p class="text-gray-600">Service</p>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                <div class="text-3xl font-bold text-red-600">5★</div>
                <p class="text-gray-600">Rating</p>
            </div>
        </div>
    </div>
@endsection
