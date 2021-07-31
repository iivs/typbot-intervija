<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        /*
         * Due to $timestamps = false; this field must be included here. Otherwide createMany or saveMany will never add
         * it even if manually given.
         */
        'created_at'
    ];
}
