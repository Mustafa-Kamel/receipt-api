<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $table = 'itemtypes';
    protected $fillable = ['title'];
}
