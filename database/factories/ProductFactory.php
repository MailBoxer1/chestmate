<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $name = $this->faker->unique()->words(mt_rand(1, 3), true);
        $slug = Str::slug($name);
        
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80), // 80% шанс, что товар активен
            'category_id' => Category::factory(),
            'image' => 'products/' . $this->faker->numberBetween(1, 10) . '.jpg', // Заглушка для изображений
        ];
    }
}
