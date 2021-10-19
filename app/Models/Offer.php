<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';
    protected $fillable = [
        'title', 'valid_from', 'valid_to', 'applied_on_type', 'applied_on_id',
        'count_range_min', 'count_range_max', 'discount_on_type', 'discount_on_id', 'discount_type',
        'discount_value'
    ];

    public function applied_on()
    {
        return $this->morphTo(__FUNCTION__, 'applied_on_type', 'applied_on_id');
    }

    public function discount_on()
    {
        return $this->morphTo(__FUNCTION__, 'discount_on_type', 'discount_on_id');
    }
}
