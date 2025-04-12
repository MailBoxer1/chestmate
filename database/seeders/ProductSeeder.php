<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все подкатегории (категории, у которых есть parent_id)
        $subCategories = Category::whereNotNull('parent_id')->get();

        // Для каждой подкатегории создаем от 5 до 15 товаров
        foreach ($subCategories as $category) {
            $numProducts = rand(5, 15);
            
            for ($i = 0; $i < $numProducts; $i++) {
                // Генерируем название продукта, которое включает название категории
                $productName = fake()->randomElement([
                    $category->name . ' ' . fake()->word(),
                    fake()->word() . ' ' . $category->name,
                    fake()->word() . ' для ' . $category->name,
                    'Премиум ' . $category->name,
                    'Новый ' . $category->name,
                ]);
                
                Product::create([
                    'name' => $productName,
                    'slug' => Str::slug($productName . '-' . Str::random(5)),
                    'description' => fake()->paragraphs(rand(2, 5), true),
                    'price' => fake()->randomFloat(2, 10, 5000),
                    'stock' => fake()->numberBetween(0, 100),
                    'is_active' => fake()->boolean(90),
                    'category_id' => $category->id,
                    'image' => 'products/' . fake()->numberBetween(1, 10) . '.jpg',
                ]);
            }
        }
    }
}
