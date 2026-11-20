<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    protected $fillable = [
        'shipment_id',
        'location_id',
        'created_by',
        'event_status',
        'event_type',
        'event_time',
        'location_name',
        'latitude',
        'longitude',
        'description',
        'exception_id',
    ];

    protected function casts(): array
    {
        return [
            'event_status' => ShipmentStatus::class,
            'event_time' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exception()
    {
        return $this->belongsTo(ShipmentException::class, 'exception_id');
    }


        /**
     * Memeriksa apakah event ini merupakan kendala/exception (Slide 07)
     */
    public function isException(): bool
    {
        return $this->event_type === 'EXCEPTION' || $this->exception_id !== null;
    }
}