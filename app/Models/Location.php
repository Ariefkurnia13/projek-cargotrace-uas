<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'latitude',
        'longitude',
        'timezone',
        'is_hub',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_hub' => 'boolean',
    ];

    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_id');
    }

    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_id');
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'location_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code}) - {$this->city}, {$this->country}";
    }
}
