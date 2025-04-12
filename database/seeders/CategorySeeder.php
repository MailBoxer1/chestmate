<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем основные категории
        $mainCategories = [
            'Электроника',
            'Одежда',
            'Дом и сад',
            'Спорт и отдых',
            'Книги и медиа',
            'Красота и здоровье',
            'Игрушки и игры',
            'Продукты питания',
        ];

        foreach ($mainCategories as $categoryName) {
            $category = Category::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => fake()->paragraph(),
                'parent_id' => null,
            ]);

            // Для каждой основной категории создаем подкатегории
            $subCategories = [];
            switch ($categoryName) {
                case 'Электроника':
                    $subCategories = ['Смартфоны', 'Ноутбуки', 'Телевизоры', 'Аудио', 'Фотоаппараты'];
                    break;
                case 'Одежда':
                    $subCategories = ['Мужская одежда', 'Женская одежда', 'Детская одежда', 'Обувь', 'Аксессуары'];
                    break;
                case 'Дом и сад':
                    $subCategories = ['Мебель', 'Кухонные приборы', 'Садовые инструменты', 'Декор', 'Постельное белье'];
                    break;
                case 'Спорт и отдых':
                    $subCategories = ['Фитнес', 'Туризм', 'Велосипеды', 'Зимние виды спорта', 'Водные виды спорта'];
                    break;
                case 'Книги и медиа':
                    $subCategories = ['Художественная литература', 'Учебная литература', 'Комиксы', 'Музыка', 'Видеоигры'];
                    break;
                case 'Красота и здоровье':
                    $subCategories = ['Косметика', 'Парфюмерия', 'Средства гигиены', 'Витамины', 'Лекарства'];
                    break;
                case 'Игрушки и игры':
                    $subCategories = ['Конструкторы', 'Настольные игры', 'Мягкие игрушки', 'Куклы', 'Радиоуправляемые игрушки'];
                    break;
                case 'Продукты питания':
                    $subCategories = ['Мясо и рыба', 'Фрукты и овощи', 'Молочные продукты', 'Хлебобулочные изделия', 'Напитки'];
                    break;
            }

            // Создаем подкатегории
            foreach ($subCategories as $subCategoryName) {
                Category::create([
                    'name' => $subCategoryName,
                    'slug' => Str::slug($subCategoryName),
                    'description' => fake()->paragraph(),
                    'parent_id' => $category->id,
                ]);
            }
        }
    }
}
