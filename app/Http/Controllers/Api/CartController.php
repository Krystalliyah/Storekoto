<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::where('user_id', auth()->id())
            ->get()
            ->groupBy('store_id')
            ->map(function ($items) {
                $storeId = $items->first()->store_id;
                
                // Get tenant and fetch products from tenant database
                $tenant = \App\Models\Tenant::find($storeId);
                
                $cartItems = $items->map(function ($item) use ($tenant) {
                    // Fetch product from tenant database
                    $product = null;
                    if ($tenant) {
                        $product = $tenant->run(function () use ($item) {
                            return \App\Models\Product::find($item->product_id);
                        });
                    }
                    
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'product' => $product ? [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'image_path' => $product->image_path,
                        ] : null
                    ];
                })->values();

                return [
                    'store_id' => $storeId,
                    'items' => $cartItems
                ];
            })->values();

        return response()->json(['data' => $carts]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|string',
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'store_id' => $validated['store_id'],
                'product_id' => $validated['product_id'],
            ],
            [
                'quantity' => $validated['quantity'],
            ]
        );

        return back()->with('success', 'Product added to cart');
    }

    public function update(Request $request, Cart $cart)
    {
        $this->authorize('update', $cart);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update($validated);

        return response()->json([
            'message' => 'Cart updated',
            'data' => $cart
        ]);
    }

    public function destroy(Cart $cart)
    {
        $this->authorize('delete', $cart);
        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Cart cleared']);
    }
}
