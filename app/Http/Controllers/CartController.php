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
        $items = $request->collect('items')->map(function ($obj) {
            $item = Item::find($obj['id']);
            $item->count = (int)$obj['count'];
            return $item;
        });
        // foreach ($items as $item) {
        // dd(\App\Models\Offer::find(3));
        // $item->offers;
        // dd($item->itemtype->offers->first()->discount_on);
        //     $item->itemtype->offers;
        // }
        $this->generate_invoice($items);
        return response()->json([
            "Subtotal: $" . $this->subtotal,
            "Shipping: $" . $this->shipping_fees,
            "VAT: $" . $this->vat,
            "Discounts: $" . $this->discounts,
            "Total: $" . $this->total
        ]);
    }

    private function generate_invoice($items)
    {
        $this->subtotal = $this->get_subtotal($items);
        $this->shipping_fees = $this->get_shipping_fees($items);
        $this->vat = $this->subtotal * .14;
        list($this->discounts, $this->discounts_sum) = $this->get_discounts($items);
        $this->total = $this->get_total();
    }

    private function get_subtotal($items)
    {
        $subtotal = 0;
        foreach ($items as $item)
            $subtotal += $item->price * $item->count;
        return $subtotal;
    }

    private function get_shipping_fees($items)
    {
        $shipping = 0;
        foreach ($items as $item) {
            $shipping += ($item->weight / $item->country->ship_weight) * $item->country->ship_rate;
        }
        return $shipping;
    }

    private function get_discounts($items)
    {
        return ["Discounts list", 0];
    }

    private function get_total()
    {
        return array_sum(
            [
                $this->subtotal,
                $this->shipping_fees,
                $this->vat,
                -$this->discounts_sum
            ]
        );
    }
}
