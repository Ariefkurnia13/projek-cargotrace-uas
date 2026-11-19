<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'item_name',
        'sku',
        'package_type',
        'quantity',
        'weight_kg',
        'volume_cbm',
        'declared_value',
        'is_hazardous',
    ];

    protected $casts = [
        'weight_kg' => 'float',
        'volume_cbm' => 'float',
        'declared_value' => 'float',
        'is_hazardous' => 'boolean',
        'quantity' => 'integer',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
