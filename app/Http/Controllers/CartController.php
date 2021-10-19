<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Item;

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
        // 
    }
}
