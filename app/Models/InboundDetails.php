<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundDetails extends Model
{
    protected $table = 'inbound_details';

    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity'
    ];

    public function products() {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function suppliers() {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }
}
