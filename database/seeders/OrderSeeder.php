<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем пользователей и продукты
        $users = User::all();
        $products = Product::where('is_active', true)->where('stock', '>', 0)->get();

        // Для каждого пользователя создаем от 0 до 5 заказов
        foreach ($users as $user) {
            $numOrders = rand(0, 5);
            
            for ($i = 0; $i < $numOrders; $i++) {
                $orderDate = fake()->dateTimeBetween('-1 year', 'now');
                $isPaid = fake()->boolean(70);
                $status = $isPaid 
                    ? fake()->randomElement(['processing', 'completed']) 
                    : fake()->randomElement(['pending', 'cancelled']);
                
                // Создаем заказ
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(fake()->bothify('??###')),
                    'status' => $status,
                    'total_amount' => 0, // Пока 0, обновим после добавления товаров
                    'shipping_address' => fake()->address(),
                    'billing_address' => fake()->address(),
                    'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                    'is_paid' => $isPaid,
                    'paid_at' => $isPaid ? fake()->dateTimeBetween($orderDate, 'now') : null,
                    'created_at' => $orderDate,
                    'updated_at' => fake()->dateTimeBetween($orderDate, 'now'),
                ]);
                
                // Добавляем от 1 до 5 товаров в заказ
                $numItems = rand(1, 5);
                $orderTotal = 0;
                
                // Выбираем случайные товары
                $orderProducts = $products->random(min($numItems, $products->count()));
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    
                    // Создаем элемент заказа
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                    
                    // Увеличиваем общую сумму заказа
                    $orderTotal += $price * $quantity;
                    
                    // Уменьшаем количество товара на складе
                    $product->stock -= $quantity;
                    $product->save();
                }
                
                // Обновляем общую сумму заказа
                $order->update(['total_amount' => $orderTotal]);
            }
        }
    }
}
