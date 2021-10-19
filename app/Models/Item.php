<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $fillable = ['title', 'price', 'weight', 'in_stock', 'type_id', 'country_id'];
}
