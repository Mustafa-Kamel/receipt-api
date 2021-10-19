<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use App\Models\ItemType;
use App\Models\Offer;

class Item extends Model
{
    protected $table = 'items';
    protected $fillable = ['title', 'price', 'weight', 'in_stock', 'type_id', 'country_id'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function itemtype()
    {
        return $this->belongsTo(ItemType::class, 'type_id');
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, __FUNCTION__, 'applied_on_type', 'applied_on_id');
    }

    public function discounts()
    {
        return $this->morphMany(Offer::class, __FUNCTION__, 'discount_on_type', 'discount_on_id');
    }
}
