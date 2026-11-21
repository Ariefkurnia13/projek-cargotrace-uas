<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use App\Enums\DelaySeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'current_location_id',
        'cargo_type',
        'total_weight_kg',
        'total_volume_cbm',
        'current_status',
        'expected_departure',
        'actual_departure',
        'expected_arrival',
        'estimated_arrival',
        'actual_arrival',
        'is_delayed',
        'delay_duration_minutes',
        'delay_severity',
        'delay_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_status' => ShipmentStatus::class,
            'delay_severity' => DelaySeverity::class,
            'is_delayed' => 'boolean',
            'expected_departure' => 'datetime',
            'actual_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_arrival' => 'datetime',
            'total_weight_kg' => 'float',
            'total_volume_cbm' => 'float',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function currentLocation()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class)->latest('detected_at');
    }

        /**
     * Scope query untuk Role-Based Access Control (Slide 05)
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isCustomer()) {
            return $query->where('customer_id', $user->id);
        }

        return $query;
    }

        public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('shipment_number', 'like', "%{$filters['search']}%")
                  ->orWhere('cargo_type', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('current_status', $filters['status']);
        }

        if (!empty($filters['carrier_id'])) {
            $query->where('carrier_id', $filters['carrier_id']);
        }

        return $query;
    }

    /**
     * Relasi untuk mengambil kendala kargo yang masih aktif/belum selesai
     */
    public function openExceptions()
    {
        return $this->hasMany(ShipmentException::class)
            ->whereIn('status', ['OPEN', 'INVESTIGATING']);
    }
}