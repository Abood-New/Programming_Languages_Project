<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->store()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product)
    {
        // Check if the user has a store and if the product belongs to that store
        $store = $user->store;

        if (!$store) {
            return false; // User has no store, deny access
        }

        // Check if the product belongs to the user's store
        return $store->products()->where('products.id', $product->id)->exists();
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product)
    {
        // Check if the user owns the store associated with the product
        $store = $user->store;

        if (!$store) {
            return false; // No store, deny access
        }

        // Check if the product belongs to the user's store
        return $store->products()->where('products.id', $product->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        //
    }
}
