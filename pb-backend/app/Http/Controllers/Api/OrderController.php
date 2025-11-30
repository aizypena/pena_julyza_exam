<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders (admin).
     */
    public function index()
    {
        $orders = Order::with('user')->latest()->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Check stock availability and update stock for each item
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Insufficient stock for {$product->name}. Available: {$product->stock}",
                    ], 422);
                }

                // Decrement the stock
                $product->decrement('stock', $item['quantity']);
            }

            // Create the order using Eloquent relationship
            $order = Auth::user()->orders()->create([
                'items' => $request->items,
                'total' => $request->total,
                'status' => 'pending',
            ]);

            DB::commit();

            // Log the activity
            ActivityLogService::logCreate('Order', $order->id, "Order #{$order->id}", [
                'user_id' => $order->user_id,
                'total' => $order->total,
                'items_count' => count($request->items),
            ]);

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('user'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to place order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        return response()->json($order->load('user'));
    }

    /**
     * Update the specified order status.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,for delivery,delivered,canceled',
        ]);

        $oldStatus = $order->status;

        $order->update([
            'status' => $request->status,
        ]);

        // Log the activity
        ActivityLogService::logUpdate('Order', $order->id, "Order #{$order->id}", 
            ['status' => $oldStatus], 
            ['status' => $request->status]
        );

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('user'),
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        try {
            DB::beginTransaction();

            // Restore stock for each item in the order (only if not delivered)
            if ($order->status !== 'delivered') {
                foreach ($order->items as $item) {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->increment('stock', $item['quantity']);
                    }
                }
            }

            $oldValues = $order->toArray();
            $order->delete();

            DB::commit();

            // Log the activity
            ActivityLogService::logDelete('Order', $oldValues['id'], "Order #{$oldValues['id']}", $oldValues);

            return response()->json([
                'message' => 'Order deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders for the authenticated user.
     */
    public function myOrders()
    {
        $orders = Auth::user()->orders()->latest()->get();
        return response()->json($orders);
    }
}
