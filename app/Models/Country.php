<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class Country extends Model
{
    protected $table = 'countries';
    protected $fillable = ['title', 'code', 'ship_rate', 'ship_weight'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
