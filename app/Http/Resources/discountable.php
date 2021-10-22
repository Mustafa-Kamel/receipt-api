<?php

namespace App\Http\Resources;

use App\Models\Offer;

trait discountable
{


    private function run_calculations()
    {
        $this->offers = Offer::whereNull('applied_on_id')->get();
        foreach ($this->collection as $item) {
            $this->subtotal += $item->price * $item->count;
            $this->shipping += ($item->weight / $item->country->ship_weight) * $item->country->ship_rate;
            $item_offers = $item->offers()->where('count_range_min', '<=', $item->count);
            $this->offers = $this->offers->merge($item->itemtype->offers()->union($item_offers)->get());
        }
        $this->vat = $this->subtotal * env('VAT', 0.14);
        $this->apply_discounts();
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

    private function apply_discounts()
    {
        foreach ($this->offers as $offer) {
            if ($this->is_offer_applicable($offer)) {
                $value = $this->get_discount_value(
                    $offer->discount_type,
                    $offer->discount_value,
                    $this->get_discountable($offer)
                );
                $this->discounts[$offer->title] = '-$' . $value;
                $this->discounts_sum += $value;
            }
        }
    }

    private function is_offer_applicable($offer)
    {
        if (is_null($offer->applied_on_id))
            return $this->is_on_all_order_items_applicable_for_discount($offer);

        return (
            (app($offer->applied_on_type) instanceof \App\Models\ItemType
                && $this->is_itemtype_items_applicable_for_discount($offer))
            or
            (app($offer->applied_on_type) instanceof \App\Models\Item
                && $this->is_items_applicable_for_discount($offer)));
    }

    private function is_on_all_order_items_applicable_for_discount($offer)
    {
        if ($this->total_items_count >= $offer->count_range_min)
            return true;
        return false;
    }

    private function is_itemtype_items_applicable_for_discount($offer)
    {
        if ($this->collection->where('type_id', $offer->applied_on->id)->sum('count') >= $offer->count_range_min)
            return true;
        return false;
    }

    private function is_items_applicable_for_discount($offer)
    {
        if ($this->collection->where('id', $offer->applied_on->id)->sum('count') >= $offer->count_range_min)
            return true;
        return false;
    }

    private function get_discountable($offer)
    {
        return ($offer->is_shipping_discount()) ?
            $this->shipping : ($offer->discount_on->price) ??
            $this->collection->where('type_id', $offer->discount_on->id)->first()->price;
    }

    private function get_discount_value($type = 'FIXED', $value, $discountable)
    {
        if ($type == 'PERCENT')
            return $value * $discountable;
        return $value;
    }
}
