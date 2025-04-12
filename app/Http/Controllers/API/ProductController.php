<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Фильтрация по категории
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Поиск по имени
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Фильтрация по активности
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Сортировка
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $query->orderBy($sort, $direction);

        // Пагинация
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . Str::random(5)),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active ?? true,
            'image' => $request->image,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $product->name = $request->name;
            // Обновляем slug только при изменении имени
            $product->slug = Str::slug($request->name . '-' . Str::random(5));
        }
        
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        
        if ($request->has('price')) {
            $product->price = $request->price;
        }
        
        if ($request->has('stock')) {
            $product->stock = $request->stock;
        }
        
        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }
        
        if ($request->has('is_active')) {
            $product->is_active = $request->is_active;
        }
        
        if ($request->has('image')) {
            $product->image = $request->image;
        }

        $product->save();

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Проверяем, есть ли элементы заказа, связанные с этим продуктом
        if ($product->orderItems()->count() > 0) {
            return response()->json(['error' => 'Нельзя удалить продукт, который есть в заказах'], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Продукт успешно удален']);
    }
}
