<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(20);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'No products available'
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'data' => ['products' => $products],
            'message' => 'Products retrieved successfully'
        ], 200);
    }
    public function productInStore($store_id)
    {
        try {
            // Fetch products related to the store
            $products = Product::whereHas('stores', function ($query) use ($store_id) {
                $query->where('id', $store_id);
            })->paginate(20);

            $products->transform(function ($product) {
                $product->product_image_url = asset('storage/' . $product->product_image);
                $product->category_name = $product->category->name ?? 'Uncategorized';
                return $product;
            });

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'data' => [],
                    'message' => 'No products found in this store'
                ], 200);
            }

            return response()->json([
                'status' => 1,
                'data' => ['products' => $products],
                'message' => 'Store and products retrieved successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Store not found'
            ], 404);
        }
    }
    public function myProducts()
    {
        $products = Product::with([
            'category',
            'stores' => function ($query) {
                $query->where('owner_id', auth()->id());
            }
        ])->whereHas('stores', function ($query) {
            $query->where('owner_id', auth()->id());
        })->paginate(20);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'No products available for your store'
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'data' => ['products' => $products],
            'message' => 'Products retrieved successfully'
        ], 200);
    }
    public function show($product_id)
    {
        try {
            $product = Product::findOrFail($product_id);

            return response()->json([
                'status' => 1,
                'data' => ['product' => $product],
                'message' => 'Product retrieved successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function store(CreateProductRequest $request, $store_id)
    {
        try {
            $store = Store::findOrFail($store_id);

            $category = Category::firstOrCreate(['name' => $request->category]);

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $category->id,
                'product_image' => $request->product_image
            ]);

            ProductStore::create([
                'product_id' => $product->id,
                'store_id' => $store_id,
                'available_quantity' => $request->available_quantity,
                'price' => $request->price
            ]);

            return response()->json([
                'status' => 1,
                'data' => ['product' => $product],
                'message' => 'Product created successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Store not found'
            ], 404);
        }
    }

    public function update(UpdateProductRequest $request, $product_id)
    {
        try {
            $store_id = auth()->user()->store->id;

            $product_store = ProductStore::where('store_id', $store_id)
                ->where('product_id', $product_id)
                ->firstOrFail();

            $product_store->update([
                'available_quantity' => $request->available_quantity,
                'price' => $request->price
            ]);

            return response()->json([
                'status' => 1,
                'data' => ['product_store' => $product_store],
                'message' => 'Product updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function destroy($product_id)
    {
        try {
            $product_store = ProductStore::where('product_id', $product_id)
                ->where('store_id', auth()->user()->store->id)
                ->firstOrFail();

            $product_store->delete();

            return response()->json([
                'status' => 1,
                'data' => [],
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function search(Request $request)
    {
        $query = Product::query();

        if ($request->has('product_name')) {
            $query->where('name', 'LIKE', '%' . $request->input('product_name') . '%');
        }

        if ($request->has('store_name')) {
            $query->orWhereHas('stores', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->input('store_name') . '%');
            });
        }

        if ($request->has('category_name')) {
            $category = Category::where('name', $request->input('category_name'))->first();
            if ($category) {
                $query->orWhere('category_id', $category->id);
            }
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'No products found matching the search criteria'
            ], 200);
        }

        return response()->json([
            'status' => 1,
            'data' => ['products' => $products],
            'message' => 'Products retrieved successfully'
        ], 200);
    }
}
