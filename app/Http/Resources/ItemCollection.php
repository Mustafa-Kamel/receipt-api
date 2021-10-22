<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    public static $wrap = 'items';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $receipt = new Receipt($this->collection);
        return [
            'items' => $this->collection,
            'total_items_count' => $receipt->totalItemsCount,
            'receipt' => [
                "Subtotal" => "$" . $receipt->subtotal,
                "Shipping" => "$" . $receipt->shipping,
                "VAT" => "$" . $receipt->vat,
                "Discounts" => $this->when($receipt->discountsSum, $receipt->discounts),
                "Total" => "$" . $receipt->total
            ]
        ];
    }
}
