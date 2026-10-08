<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public const UNITS = [
        'kg' => 'Kg',
        'pcs' => 'Pcs',
        'meter' => 'Meter',
    ];

    protected $fillable = [
        'name',
        'unit',
        'price',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
