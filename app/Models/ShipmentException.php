<?php

namespace App\Models;

use App\Enums\DelaySeverity;
use App\Enums\ExceptionStatus;
use App\Enums\ExceptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'exception_type',
        'status',
        'severity',
        'reported_by',
        'resolved_by',
        'detected_at',
        'resolved_at',
        'description',
        'root_cause',
        'resolution_notes',
        'impact_delay_hours',
    ];

    protected function casts(): array
    {
        return [
            'exception_type' => ExceptionType::class,
            'status' => ExceptionStatus::class,
            'severity' => DelaySeverity::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'impact_delay_hours' => 'float',
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'exception_id');
    }

    public function isOpen(): bool
    {
        return $this->status === ExceptionStatus::OPEN;
    }

    public function isInvestigating(): bool
    {
        return $this->status === ExceptionStatus::INVESTIGATING;
    }

    public function isResolved(): bool
    {
        return $this->status === ExceptionStatus::RESOLVED;
    }

    public function resolutionDurationHours(): ?float
    {
        if (!$this->resolved_at || !$this->detected_at) {
            return null;
        }

        return round($this->detected_at->diffInMinutes($this->resolved_at) / 60, 2);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', ExceptionStatus::OPEN);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [ExceptionStatus::OPEN, ExceptionStatus::INVESTIGATING]);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', ExceptionStatus::RESOLVED);
    }
}
