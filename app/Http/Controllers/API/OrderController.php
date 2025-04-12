<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Получаем заказы текущего пользователя
        $query = Order::with('items.product')
            ->where('user_id', $request->user()->id);
            
        // Фильтрация по статусу
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Сортировка
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);
        
        $orders = $query->paginate(10);
        
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Начинаем транзакцию
        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderItems = [];
            $invalidItems = [];

            // Проверяем наличие товаров и рассчитываем общую сумму
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                // Проверка наличия товара
                if (!$product->is_active || $product->stock < $item['quantity']) {
                    $invalidItems[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'available_stock' => $product->stock,
                        'requested_quantity' => $item['quantity'],
                        'is_active' => $product->is_active,
                    ];
                    continue;
                }

                // Добавляем в список товаров заказа
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                // Увеличиваем общую сумму
                $totalAmount += $product->price * $item['quantity'];

                // Уменьшаем остаток товара
                $product->stock -= $item['quantity'];
                $product->save();
            }

            // Если есть недоступные товары, возвращаем ошибку
            if (!empty($invalidItems)) {
                // Откатываем транзакцию
                DB::rollBack();
                
                return response()->json([
                    'error' => 'Некоторые товары недоступны или закончились на складе',
                    'invalid_items' => $invalidItems,
                ], 422);
            }

            // Создаем заказ
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'payment_method' => $request->payment_method,
                'is_paid' => false,
            ]);

            // Создаем элементы заказа
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return response()->json([
                'message' => 'Заказ успешно создан',
                'order' => $order->load('items.product'),
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        // Проверяем, принадлежит ли заказ текущему пользователю
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        return response()->json($order->load('items.product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
