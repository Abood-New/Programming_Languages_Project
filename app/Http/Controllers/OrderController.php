<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function my_store_orders()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Please create your store first'
            ]);
        }
        $orders = Order::whereHas("items.productStore", function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })
            ->with([
                'items' => function ($query) {
                    $query->select('id', 'order_id');
                },
                'items.productStore' => function ($query) {
                    $query->select('id', 'store_id', 'product_id');
                }
            ])
            ->select('id', 'user_id', 'created_at')
            ->paginate(10);

        return response()->json([
            'status' => 1,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ], 200);
    }
    public function ship($order_id)
    {
        try {
            $order = Order::findOrFail($order_id);

            if ($order->order_status !== OrderStatus::PENDING) {
                return response()->json([
                    'status' => 0,
                    'data' => [],
                    'message' => 'Order is not pending'
                ], 400);
            }

            $order->update([
                'order_status' => OrderStatus::SHIPPED
            ]);

            return response()->json([
                'status' => 1,
                'data' => ['order' => $order],
                'message' => 'Order has been shipped'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => [],
                'message' => 'Order cannot be found'
            ], 404);
        }
    }
}
