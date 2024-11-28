<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return $products = Product::paginate(20);

    }
    public function productInStore($store_id)
    {
        $store = Store::findOrFail($store_id);

        $store->load(['products']);

        return $store;
    }
    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);

        return $product;
    }
    public function myProducts()
    {
        $store = Store::with('products')->where('owner_id', auth()->id())->paginate(20);

        return $store;
    }
    public function store(CreateProductRequest $request, $store_id)
    {
        $store = Store::findOrFail($store_id);

        $category = Category::firstOrCreate([
            'name' => $request->category
        ]);

        $product = Product::firstOrCreate([
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

        return $product;
    }
    public function update(UpdateProductRequest $request, $product_id)
    {
        $store_id = auth()->user()->store->id;

        $product = Product::findOrFail($product_id);

        $product_store = ProductStore::where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->firstOrFail();

        $product_store->update([
            'available_quantity' => $request->available_quantity,
            'price' => $request->price
        ]);

        return $product_store;
    }
    public function destroy($product_id)
    {
        $product_store = ProductStore::where('product_id', $product_id)
            ->where('store_id', auth()->user()->store->id)
            ->first();

        $product_store->delete();

        return "deleted Successfully";
    }
    public function search(Request $request)
    {
        $query = Product::query();
        // search by product name or store name
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
            $query->orWhere('category_id', 'LIKE', '%' . $category->id . '%');
        }

        $products = $query->get();

        return $products;
    }
}


