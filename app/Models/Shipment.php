<?php

namespace App\Models;

use App\Enums\DelaySeverity;
use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

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
            'expected_departure' => 'datetime',
            'actual_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_arrival' => 'datetime',
            'is_delayed' => 'boolean',
            'total_weight_kg' => 'float',
            'total_volume_cbm' => 'float',
            'delay_duration_minutes' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /**
     * Chronological timeline of tracking events
     */
    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class)->latest('detected_at');
    }

    public function openExceptions()
    {
        return $this->hasMany(ShipmentException::class)->whereIn('status', ['OPEN', 'INVESTIGATING']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Multi-tenancy & Advanced Filtering)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope query based on authenticated user's role (RBAC).
     * Customers only see their own shipments.
     * Administrators and Operators see all operational shipments.
     */
    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isCustomer()) {
            return $query->where('customer_id', $user->id);
        }

        return $query;
    }

    /**
     * Advanced server-side search and filtering
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(!empty($filters['search']), function (Builder $q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('shipment_number', 'LIKE', "%{$search}%")
                        ->orWhere('cargo_type', 'LIKE', "%{$search}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%")->orWhere('company_name', 'LIKE', "%{$search}%"));
                });
            })
            ->when(!empty($filters['status']), function (Builder $q) use ($filters) {
                $q->where('current_status', $filters['status']);
            })
            ->when(!empty($filters['carrier_id']), function (Builder $q) use ($filters) {
                $q->where('carrier_id', $filters['carrier_id']);
            })
            ->when(!empty($filters['origin_id']), function (Builder $q) use ($filters) {
                $q->where('origin_id', $filters['origin_id']);
            })
            ->when(!empty($filters['destination_id']), function (Builder $q) use ($filters) {
                $q->where('destination_id', $filters['destination_id']);
            })
            ->when(isset($filters['is_delayed']) && $filters['is_delayed'] !== '', function (Builder $q) use ($filters) {
                $q->where('is_delayed', filter_var($filters['is_delayed'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when(!empty($filters['date_from']), function (Builder $q) use ($filters) {
                $q->whereDate('expected_departure', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function (Builder $q) use ($filters) {
                $q->whereDate('expected_arrival', '<=', $filters['date_to']);
            });
    }

    public function scopeDelayed(Builder $query): Builder
    {
        return $query->where('is_delayed', true);
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where('current_status', ShipmentStatus::IN_TRANSIT);
    }

    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('current_status', ShipmentStatus::DELIVERED);
    }
}
