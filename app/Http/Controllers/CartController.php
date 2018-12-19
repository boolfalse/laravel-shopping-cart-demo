<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Boolfalse\LaravelShoppingCart\Facades\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    public function index()
    {
        return view('cart');
    }

    public function store(Request $request)
    {
        $duplicates = Cart::search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->id;
        });

        if (!$duplicates->isEmpty()) {
            return redirect('cart')->withSuccessMessage('Item is already in your cart!');
        }

        Cart::add($request->id, $request->name, 1, $request->price)->associate(Product::class);
        return redirect('cart')->withSuccessMessage('Item was added to your cart!');
    }

    public function update(Request $request, $id)
    {
        // Validation on max quantity
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|between:1,5'
        ]);

         if ($validator->fails()) {
            session()->flash('error_message', 'Quantity must be between 1 and 5.');
            return response()->json(['success' => false]);
         }

        Cart::update($id, $request->quantity);
        session()->flash('success_message', 'Quantity was updated successfully!');

        return response()->json(['success' => true]);

    }

    public function destroy($id)
    {
        Cart::remove($id);
        return redirect('cart')->withSuccessMessage('Item has been removed!');
    }

    public function emptyCart()
    {
        Cart::destroy();
        return redirect('cart')->withSuccessMessage('Your cart has been cleared!');
    }

    public function switchToWishlist($id)
    {
        $item = Cart::get($id);

        Cart::remove($id);

        $duplicates = Cart::instance('wishlist')->search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->id === $id;
        });

        if (!$duplicates->isEmpty()) {
            return redirect('cart')->withSuccessMessage('Item is already in your Wishlist!');
        }

        Cart::instance('wishlist')->add($item->id, $item->name, 1, $item->price)
                                  ->associate(Product::class);

        return redirect('cart')->withSuccessMessage('Item has been moved to your Wishlist!');
    }
}
