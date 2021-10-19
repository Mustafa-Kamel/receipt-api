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
        $items = $request->collect('items')->map(function ($obj) {
            $item = Item::find($obj['id']);
            $item->count = (int)$obj['count'];
            return $item;
        });
        return new ItemCollection($items);
    }
}
