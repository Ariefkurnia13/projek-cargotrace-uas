<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_email',
        'phone',
        'service_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
