<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Item;
use App\Http\Resources\ItemCollection;

class CartController extends Controller
{
    /**
     * Create the invoice's receipt for the items sent in cart.
     *
     * @param  App\Http\Requests\CartRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function new(CartRequest $request)
    {
        $cartItems = $request->collect('items');
        if (count($cartItems->duplicates('id')))
            abort(400, "Duplicate entries for the same item are not allowed!");

        $cartItems = $cartItems->keyBy('id')->toArray();
        $items = Item::whereIn('id', array_keys($cartItems))->get();
        $items = $items->map(function ($item) use ($cartItems) {
            $item->count = (int)$cartItems[$item->id]['count'];
            return $item;
        });
        return new ItemCollection($items);
    }
}
