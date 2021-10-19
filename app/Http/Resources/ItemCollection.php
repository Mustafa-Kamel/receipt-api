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
        $this->run_calculations();
        return [
            'items' => $this->collection,
            'receipt' => [
                "Subtotal" => "$" . $this->subtotal,
                "Shipping" => "$" . $this->shipping,
                "VAT" => "$" . $this->vat,
                "Discounts" => $this->when($this->discounts_sum, $this->discounts),
                "Total" => "$" . $this->total()
            ]
        ];
    }

    private function run_calculations()
    {
        $this->subtotal = $this->shipping = $this->discounts_sum = 0;
        $this->discounts = [];
        foreach ($this->collection as $item) {
            $this->subtotal += $item->price * $item->count;
            $this->shipping += ($item->weight / $item->country->ship_weight) * $item->country->ship_rate;
            $this->add_discounts($item);
        }
        $this->vat = $this->subtotal * env('VAT', 0.14);
    }

    private function total()
    {
        return array_sum(
            [
                $this->subtotal,
                $this->shipping,
                $this->vat,
                -$this->discounts_sum
            ]
        );
    }

    private function add_discounts($item)
    {
        return [["Discounts list"], 0];
    }
}
