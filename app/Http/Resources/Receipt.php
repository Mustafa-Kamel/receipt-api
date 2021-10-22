<?php

namespace App\Http\Resources;

use App\Models\Offer;

class Receipt
{
    public $subtotal = 0;
    public $shipping = 0;
    public $discounts_sum = 0;
    public $discounts = [];
    public $total = 0;

    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->calculate();
    }

    /**
     * Calculate the parameters of the receipt.
     *
     * @void
     */
    public function calculate()
    {
        foreach ($this->collection as $item) {
            $this->subtotal += $item->price * $item->count;
            $this->shipping += ($item->weight / $item->country->ship_weight) * $item->country->ship_rate;
        }
        $this->vat = $this->subtotal * env('VAT', 0.14);
        $this->total_items_count = $this->collection->sum('count');
        $this->applyDiscounts();
        $this->total = $this->getTotal();
    }

    /**
     * Calculate the item's total price.
     *
     * @return Array
     */
    private function getTotal()
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

    /**
     * Calculate the discounts that can apply on items on cart.
     *
     * @void
     */
    private function applyDiscounts()
    {
        foreach ($this->getOffers() as $offer) {
            if ($this->isOfferApplicable($offer)) {
                $value = $this->getDiscountValue(
                    $offer->discount_type,
                    $offer->discount_value,
                    $this->getDiscountable($offer)
                );
                $this->discounts[$offer->title] = '-$' . $value;
                $this->discounts_sum += $value;
            }
        }
    }

    private function getOffers()
    {
        $itemIds = $this->collection->pluck('id');
        $itemTypeIds = $this->collection->pluck('type_id');
        return Offer::whereNull('applied_on_id')
            ->where('count_range_min', '<=', $this->total_items_count)
            ->orWhere(function ($query) use ($itemIds) {
                $query->where('applied_on_type', \App\Models\Item::class)
                    ->whereIn('applied_on_id', $itemIds);
            })->orWhere(function ($query) use ($itemTypeIds) {
                $query->where('applied_on_type', \App\Models\ItemType::class)
                    ->whereIn('applied_on_id', $itemTypeIds);
            })->get();
    }

    /**
     * Check if the specified offer can be applied on some or all items in cart.
     *
     * @param  \App\Models\Offer  $offer
     * @return Boolean
     */
    private function isOfferApplicable($offer)
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

    /**
     * Check if the count of all the items in cart validates the offer's rule.
     *
     * @param  \App\Models\Offer  $offer
     * @return Boolean
     */
    private function is_on_all_order_items_applicable_for_discount($offer)
    {
        if ($this->total_items_count >= $offer->count_range_min)
            return true;
        return false;
    }

    /**
     * Check if the count of a single ItemType of the items in cart validates the offer's rule.
     *
     * @param  \App\Models\Offer  $offer
     * @return Boolean
     */
    private function is_itemtype_items_applicable_for_discount($offer)
    {
        if ($this->collection->where('type_id', $offer->applied_on->id)->sum('count') >= $offer->count_range_min)
            return true;
        return false;
    }

    /**
     * Check if the count of a single Item in cart validates the offer's rule.
     *
     * @param  \App\Models\Offer  $offer
     * @return Boolean
     */
    private function is_items_applicable_for_discount($offer)
    {
        if ($this->collection->where('id', $offer->applied_on->id)->sum('count') >= $offer->count_range_min)
            return true;
        return false;
    }

    /**
     * Find the $discountable for the specified offer.
     *
     * @param  \App\Models\Offer  $offer
     * @return Float $discountable
     */
    private function getDiscountable($offer)
    {
        return ($offer->is_shipping_discount()) ?
            $this->shipping : ($offer->discount_on->price) ??
            $this->collection->where('type_id', $offer->discount_on->id)->first()->price;
    }

    /**
     * Calculate the discount value for the specified $discountable.
     *
     * @param  String  $type
     * @param  Float  $value
     * @param  Float  $discountable
     * @return Float $discount_value
     */
    private function getDiscountValue($type = 'FIXED', $value, $discountable)
    {
        if ($type == 'PERCENT')
            return $value * $discountable;
        return $value;
    }
}
