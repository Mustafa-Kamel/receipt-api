<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class ItemType extends Model
{
    protected $table = 'itemtypes';
    protected $fillable = ['title'];

    public function items()
    {
        return $this->hasMany(Item::class, 'type_id');
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
