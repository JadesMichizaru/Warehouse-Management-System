<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutbondDetails extends Model
{
    protected $table = 'outbond_details';

    protected $fillable = [
        'customer_id',
        'reference_number',
        'product_id',
        'quantity'
    ];

    public function customers() {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function products() {
        return $this->belongsTo(Products::class, 'product_id');
    }

}
