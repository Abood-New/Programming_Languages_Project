<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(20);

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
        // products related to store
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
            'message' => 'Products retrieved successfully'
        ], 200);
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
            $product = Product::with('category')->findOrFail($product_id);

            return response()->json([
                'status' => 1,
                'data' => ['product' => $product],
                'message' => 'Product retrieved successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'The requested product does not exist or has been removed.'
            ], 404);
        }
    }
    public function store(CreateProductRequest $request)
    {
        try {
            $store = auth()->user()->store;

            Gate::authorize('create', Product::class);

            $category = Category::where('name', $request->category)->first();

            if (!$category) {
                Category::create([
                    'name' => $request->category
                ]);
            }

            $productImage = null;
            if ($request->hasFile('product_image')) {
                $productImage = $request->file('product_image')->store('product_images/' . $store->id, 'public');
            }

            // Create the product
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $category->id,
                'product_image' => $productImage,
            ]);

            ProductStore::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'available_quantity' => $request->available_quantity,
                'price' => $request->price
            ]);

            $product->product_image_url = $productImage ? asset('storage/' . $productImage) : null;

            return response()->json([
                'status' => 1,
                'data' => ['product' => $product],
                'message' => 'Product created successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Store cannot be found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Please create your store first'
            ], 403);
        }
    }
    public function update(UpdateProductRequest $request, $product_id)
    {
        try {
            // Get the authenticated user's store
            $store = auth()->user()->store;

            $product = Product::findOrFail($product_id);

            Gate::authorize('update', $product);

            // Find the ProductStore record for the given product and store
            $productStore = ProductStore::where('store_id', $store->id)
                ->where('product_id', $product_id)
                ->firstOrFail();

            // Update the product store's details
            $productStore->update($request->only(['available_quantity', 'price']));

            return response()->json([
                'status' => 1,
                'data' => ['product_store' => $productStore],
                'message' => 'Product updated successfully'
            ], 200);

        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'You are not authorized to update this product.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Product not found or does not belong to your store.'
            ], 404);
        }
    }

    public function destroy($product_id)
    {
        try {
            $product = Product::findOrFail($product_id);

            Gate::authorize('delete', $product);

            $store = auth()->user()->store;

            $product_store = ProductStore::where('product_id', $product_id)
                ->where('store_id', auth()->user()->store->id)
                ->first();

            $product_store->delete();

            return response()->json([
                'status' => 1,
                'data' => [],
                'message' => 'Product deleted successfully.'
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'You are not authorized to delete this product.'
            ], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Product not found in your store.'
            ], 404);
        }
    }
    public function search(Request $request)
    {
        // validate the request
        // TODO
        // $query = Product::query();
        $products = Product::query()
            ->when($request->product_name, function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->product_name . '%');
            })
            ->when($request->store_name, function ($query) use ($request) {
                $query->orWhereHas('stores', function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->input('store_name') . '%');
                });
            })
            ->when($request->category_name, function ($query) use ($request) {
                $category = Category::where('name', $request->input('category_name'))->first();
                if ($category) {
                    $query->orWhere('category_id', $category->id);
                }
            })
            // sorting
            ->when($request->sortBy && $request->sortOrder, function ($query) use ($request) {
                $query->orderBy($request->sortBy, $request->sortOrder);
            })
            ->paginate(20);
        // Apply filters only if they exist in the request
        // if ($request->filled('product_name')) {
        //     $query->where('name', 'LIKE', '%' . $request->input('product_name') . '%');
        // }

        // if ($request->filled('store_name')) {
        //     $query->orWhereHas('stores', function ($q) use ($request) {
        //         $q->where('name', 'LIKE', '%' . $request->input('store_name') . '%');
        //     });
        // }

        // if ($request->filled('category_name')) {
        //     $category = Category::where('name', $request->input('category_name'))->first();
        //     if ($category) {
        //         $query->orWhere('category_id', $category->id);
        //     }
        // }

        // // Paginate the results
        // $products = $query->paginate(20);

        // Return a structured response
        return response()->json([
            'status' => $products->isNotEmpty() ? 1 : 0,
            'data' => $products->isNotEmpty() ? ['products' => $products] : [],
            'message' => $products->isNotEmpty()
                ? 'Products retrieved successfully'
                : 'No products found matching the search criteria'
        ], 200);
    }
}


