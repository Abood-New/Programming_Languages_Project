<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\UnauthorizedException;

class StoreController extends Controller
{
    public function getAllStores()
    {
        $stores = Store::all();

        if ($stores->isEmpty()) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'There are no stores available'
            ], 200);
        }

        $data = [];
        $stores->transform(function ($store) {
            $store->store_image_url = asset('storage/' . $store->store_image);
            return $store;
        });
        $data['stores'] = $stores;
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'Stores retrieved successfully'
        ], 200);
    }
    public function getMyStore()
    {
        // Get the authenticated user's ID
        $userId = Auth::user()->id;

        // Retrieve the stores for the authenticated user
        $stores = Store::where('owner_id', $userId)->get();

        // Check if the user has any stores
        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores found for this user.'], 404);
        }

        // Return the stores
        return response()->json([
            'status' => 1,
            'stores' => $stores,
            'message' => 'Store retrieved successfully'
        ], 200);
    }
    public function getProductsByStore($storeId)
    {
        try {// Find the store by ID
            $store = Store::findOrFail($storeId);

            // Check if the store exists
            if (!$store) {
                return response()->json(['message' => 'Store not found.'], 404);
            }

            // Get all products for the specified store
            $products = Product::where('store_id', $store->id)->get();
            return response()->json(['store' => $store->store_name, 'products' => $products], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'store cannot be found'
            ], 404);
        }
    }
    public function createStore(Request $request)
    {
        try {
            Gate::authorize('create', Store::class);
            $validated = $request->validate([
                'store_name' => 'required|string',
                'description' => 'required|string',
                'store_image' => 'nullable|file|mimes:png,jpg|max:2048'
            ]);

            $storeImage = '';
            if ($request->hasFile('store_image')) {
                $request->file('store_image')->store('store_images/' . auth()->id(), 'public');
                $storeImage = $request->file('store_image')->hashName();
            }
            $store = Store::create([
                'store_name' => $validated['store_name'],
                'description' => $validated['description'],
                'store_image' => $storeImage,
                'owner_id' => auth()->id()
            ]);

            $store->store_image_url = asset('storage/store_images/' . auth()->id() . '/' . $storeImage);
            return response()->json([
                'status' => 1,
                'data' => [
                    'store' => $store,
                ],
                'message' => 'Store created successfully'
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'You can only have one store'
            ], 403);
        }
    }
    public function updateStore(Request $request, $store_id)
    {
        try {
            $store = Store::findOrFail($store_id);

            Gate::authorize('update', $store);

            $validated = $request->validate([
                'store_name' => 'sometimes|string',
                'description' => 'sometimes|string',
                'store_image' => 'nullable|file|mimes:png,jpg|max:2048'
            ]);

            if ($request->hasFile('store_image')) {
                if ($store->store_image) {
                    Storage::disk('public')->delete('store_images/' . auth()->id() . '/' . $store->store_image);
                }
                $filePath = $request->file('store_image')->store('store_images/' . auth()->id(), 'public');
                $validated['store_image'] = $filePath;
            }

            $store->update($validated);
            $store->store_image_url = asset('storage/' . $store->store_image);

            return response()->json([
                'status' => 1,
                'data' => [
                    'store' => $store,
                ],
                'message' => 'Store updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'store cannot be found'
            ], 404);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'You are not authorized to view this store'
            ], 403);
        }
    }
    public function destroy($store_id)
    {
        try {
            $store = Store::findOrFail($store_id);

            Gate::authorize('delete', $store);

            $orderItem = OrderItem::where('store_id', '=', $store_id)->get();
            foreach ($orderItem as $item) {
                if ($item->order_status == OrderStatus::PENDING->value) {
                    return response()->json([
                        'status' => 0,
                        'data' => [],
                        'message' => 'cannot delete store while order is still pending'
                    ], 400);
                }
            }

            $store->delete();

            return response()->json([
                'status' => 1,
                'data' => [],
                'message' => 'Store deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'store cannot be found'
            ], 404);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'You are not authorized to delete this store'
            ], 403);
        }
    }
    public function filterStoreByName(Request $request)
    {
        // Get the store_name from the query parameters
        $storeName = $request->query('store_name');

        // Validate the input
        if (!$storeName) {
            return response()->json(['message' => 'The store_name query parameter is required.'], 400);
        }

        // Retrieve the store(s) that match the name
        $stores = Store::where('store_name', 'LIKE', "%{$storeName}%")->get();

        // Check if any stores were found
        if ($stores->isEmpty()) {
            return response()->json(['message' => 'No stores found matching the given name.'], 404);
        }

        return response()->json(['stores' => $stores], 200);
    }
}

