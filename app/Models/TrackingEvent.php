<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    use HasFactory;

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
            'event_time' => 'datetime',
            'event_status' => ShipmentStatus::class,
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

    public function isException(): bool
    {
        return $this->event_type === 'EXCEPTION' || $this->exception_id !== null;
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('event_time', 'asc');
    }
}
