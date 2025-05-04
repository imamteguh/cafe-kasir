<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'image',
        'is_avaliable'
    ];

    protected $casts = [
        'is_avaliable' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
