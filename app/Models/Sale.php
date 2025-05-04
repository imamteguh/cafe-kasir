<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //
    protected $fillable = [
        'user_id',
        'invoice_number',
        'payment_method',
        'total_amount',
        'total_paid',
        'change',
    ];

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
