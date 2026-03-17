<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_email',
        'phone',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot('cost', 'supplier_sku')
            ->withTimestamps();
    }
}