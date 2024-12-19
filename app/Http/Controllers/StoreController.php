<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\UnauthorizedException;

class StoreController extends Controller
{
    public function index()
    {
        if (auth()->user()->role == 'admin') {
            $stores = auth()->user()->store()->with(['products'])->paginate(10);
        } else {
            $stores = Store::with('products')->paginate(10);
        }

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
    public function show($store_id)
    {
        try {
            $store = Store::with('products')->findOrFail($store_id);

            Gate::authorize('view', $store);

            $store->store_image_url = asset('storage/' . $store->store_image);

            return response()->json([
                'status' => 1,
                'data' => ['store' => $store],
                'message' => 'store retrieved successfully'
            ]);
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
    public function store(Request $request)
    {
        try {
            Gate::authorize('create', Store::class);
            $validated = $request->validate([
                'name' => 'required|string',
                'store_image' => 'nullable|file|mimes:png,jpg|max:2048'
            ]);

            $storeImage = '';
            if ($request->hasFile('store_image')) {
                $filePath = $request->file('store_image')->store('store_images/' . auth()->id(), 'public');
                $storeImage = $filePath;
            }
            $store = Store::create([
                'name' => $validated['name'],
                'store_image' => $storeImage,
                'owner_id' => auth()->id()
            ]);

            $store->store_image_url = asset('storage/' . $store->store_image);
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
    public function update(Request $request, $store_id)
    {
        try {
            $store = Store::findOrFail($store_id);

            Gate::authorize('update', $store);

            $validated = $request->validate([
                'name' => 'sometimes|string',
                'store_image' => 'nullable|file|mimes:png,jpg|max:2048'
            ]);

            if ($request->hasFile('store_image')) {
                if ($store->store_image) {
                    Storage::disk('public')->delete($store->store_image);
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
}
