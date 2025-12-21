<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsProduct>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = str_replace('.', '', $this->faker->text(25));
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . rand(111, 999) . time(),
            'thumbnail_url' => 'https://picsum.photos/id/' . rand(5, 105) . '/367/267',
            'price' => rand(25000, 50000),
            'description' => $this->faker->text(1500),
            'stock' => rand(24, 109),
        ];
    }
}
