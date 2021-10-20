<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    use discountable;
    public static $wrap = 'items';
    private $subtotal = 0;
    private $shipping = 0;
    private $discounts_sum = 0;
    private $discounts = [];

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->total_items_count = $this->collection->sum('count');
        $this->run_calculations();
        return [
            'items' => $this->collection,
            'total_items_count' => $this->total_items_count,
            'receipt' => [
                "Subtotal" => "$" . $this->subtotal,
                "Shipping" => "$" . $this->shipping,
                "VAT" => "$" . $this->vat,
                "Discounts" => $this->when($this->discounts_sum, $this->discounts),
                "Total" => "$" . $this->total()
            ]
        ];
    }
}
