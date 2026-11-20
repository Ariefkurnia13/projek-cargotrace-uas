<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function departingShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_id');
    }

    public function arrivingShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_id');
    }

    public function currentShipments()
    {
        return $this->hasMany(Shipment::class, 'current_location_id');
    }
}