<?php

namespace App\Http\Resources;

use App\Models\Offer;

class Receipt
{

    /**
     * The count of all the items in cart.
     * 
     * @var float
     */
    public $totalItemsCount = 0;

    /**
     * The total sum of the price value for all the items in cart.
     * 
     * @var float
     */
    public $subtotal = 0;

    /**
     * The total VAT taxes value for all the items in cart.
     * 
     * @var float
     */
    public $vat = 0;

    /**
     * The shipping fees value for all the items in cart.
     * 
     * @var float
     */
    public $shippingFees = 0;

    /**
     * The total sum of all the applied discounts values.
     * 
     * @var float
     */
    public $discountsSum = 0;

    /**
     * The descriptions of all applied discounts.
     * 
     * @var array
     */
    public $discounts = [];

    /**
     * The receipt total value.
     * 
     * @var float 
     */
    public $total = 0;

    /**
     * Create a new receipt.
     *
     * @param  \Illuminate\Support\Collection $collection
     * @return void
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->calculate();
    }

    /**
     * Calculate the parameters of the receipt.
     *
     * @return void
     */
    public function calculate()
    {
        foreach ($this->collection as $item) {
            $this->subtotal += $item->subtotal();
            $this->shippingFees += $item->shippingFees();
        }
        $this->vat = $this->subtotal * env('VAT', 0.14);
        $this->totalItemsCount = $this->collection->sum('count');
        $this->applyDiscounts();
        $this->total = $this->getTotal();
    }

    /**
     * Calculate the item's total price.
     *
     * @return array
     */
    private function getTotal()
    {
        return array_sum(
            [
                $this->subtotal,
                $this->shippingFees,
                $this->vat,
                -$this->discountsSum
            ]
        );
    }

    /**
     * Calculate the discounts that can apply on current cart items.
     *
     * @return void
     */
    private function applyDiscounts()
    {
        foreach ($this->getOffers() as $offer) {
            $numberOfDiscounts = min(
                $this->getMaxNumberOfEligibleDiscounts($offer),
                $this->getNumberOfDiscountables($offer)
            );
            if ($numberOfDiscounts) {
                $value = $this->getDiscountValue(
                    $offer->discount_type,
                    $offer->discount_value,
                    $this->getDiscountablePrice($offer),
                    $numberOfDiscounts
                );
                $discount_title =  ($numberOfDiscounts > 1) ?
                    $numberOfDiscounts . ' * ' . $offer->title : $offer->title;
                $this->discounts[$discount_title] = '-$' . $value;
                $this->discountsSum += $value;
            }
        }
    }

    /**
     * Get the offers that may be applied on the current cart items.
     * 
     * @return Illuminate\Support\Collection $offers
     */
    private function getOffers()
    {
        $totalItemsCount = $this->totalItemsCount;
        $itemIds = $this->collection->pluck('id');
        $itemTypeIds = $this->collection->pluck('type_id');
        return Offer::where('valid_from', '<=', \Carbon\Carbon::now())
            ->where('expires_at', '>', \Carbon\Carbon::now())
            ->where(
                function ($query) use ($totalItemsCount, $itemIds, $itemTypeIds) {
                    $query->where(function ($query) use ($totalItemsCount) {
                        $query->whereNull('applied_on_id')
                            ->where('count_range_min', '<=', $totalItemsCount);
                    })->orWhere(function ($query) use ($itemIds) {
                        $query->where('applied_on_type', \App\Models\Item::class)
                            ->whereIn('applied_on_id', $itemIds);
                    })->orWhere(function ($query) use ($itemTypeIds) {
                        $query->where('applied_on_type', \App\Models\ItemType::class)
                            ->whereIn('applied_on_id', $itemTypeIds);
                    });
                }
            )->get();
    }

    /**
     * Get the max number of eligible discount of the specified offer.
     *
     * @param  \App\Models\Offer  $offer
     * @return int
     */
    private function getMaxNumberOfEligibleDiscounts($offer)
    {
        if (is_null($offer->applied_on_id))
            $itemsCount = $this->totalItemsCount;
        else {
            $field = $this->getSearchField(app($offer->applied_on_type));
            $itemsCount = $this->collection->where($field, $offer->appliedOn->id)->sum('count');
        }

        if ($offer->count_range_min == $offer->count_range_max)
            return floor($itemsCount / $offer->count_range_min);
        return (int) ($itemsCount >= $offer->count_range_min);
    }

    /**
     * Get the number of the items in cart that are eligible for the discount of the specified offer.
     *
     * @param  \App\Models\Offer  $offer
     * @return int
     */
    private function getNumberOfDiscountables($offer)
    {
        if (is_null($offer->discount_on_id))
            return $this->totalItemsCount;

        $field = $this->getSearchField(app($offer->discount_on_type));

        return $this->collection->where($field, $offer->discountOn->id)->sum('count');
    }

    /**
     * Get the field to use as the search index in the collection depending on the specified model type.
     * 
     * @param \App\Models\ItemType|\App\Models\Item $type
     * @return string $field
     */
    private function getSearchField($type)
    {
        if ($type instanceof \App\Models\ItemType) {
            $field = 'type_id';
        } elseif ($type instanceof \App\Models\Item) {
            $field = 'id';
        } else {
            $field = '';
        }
        return $field;
    }

    /**
     * Get the value (price) of the $discountable for the specified offer.
     *
     * @param  \App\Models\Offer  $offer
     * @return float $discountable
     */
    private function getDiscountablePrice($offer)
    {
        return ($offer->isShippingDiscount()) ?
            $this->shippingFees : ($offer->discountOn->price) ??
            $this->collection->where('type_id', $offer->discountOn->id)->sum('price');
    }

    /**
     * Calculate the discount value for the specified $discountable
     * depending on the discount type and the discountable price.
     *
     * @param  string  $type
     * @param  float  $value
     * @param  float  $discountable
     * @param  int  $numberOfDiscounts
     * @return float $discount_value
     */
    private function getDiscountValue($type = 'FIXED', $value, $discountable, $numberOfDiscounts)
    {
        return ($type == 'PERCENT') ?
            $value * $discountable * $numberOfDiscounts : $value * $numberOfDiscounts;
    }
}
