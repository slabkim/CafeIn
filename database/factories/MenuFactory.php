<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $menus = [
            'Coffee' => ['Espresso', 'Cappuccino', 'Latte', 'Americano', 'Mocha'],
            'Tea' => ['Green Tea', 'Black Tea', 'Milk Tea', 'Fruit Tea'],
            'Dessert' => ['Chocolate Cake', 'Cheesecake', 'Brownies', 'Cookies'],
            'Snack' => ['French Fries', 'Chicken Nuggets', 'Sandwich', 'Burger'],
            'Beverage' => ['Orange Juice', 'Apple Juice', 'Mineral Water']
        ];

        $categoryName = $this->faker->randomElement(array_keys($menus));
        $name = $this->faker->randomElement($menus[$categoryName]);

        $category = \App\Models\Category::where('name', $categoryName)->first();

        $hasImage = $this->faker->boolean(70);
        $meta = [
            'prep_time' => $this->faker->numberBetween(2, 10) . ' min',
            'serving_size' => $this->faker->randomElement(['Small', 'Regular', 'Large']),
            'calories' => $this->faker->numberBetween(50, 450) . ' kcal',
        ];

        return [
            'name' => $name,
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(5000, 50000),
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => $category->id ?? 1,
            'image' => $hasImage ? 'menus/placeholder.svg' : null,
            'metadata' => $meta,
        ];
    }
}
