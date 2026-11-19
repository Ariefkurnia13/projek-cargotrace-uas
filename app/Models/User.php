<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::OPERATOR;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function canManageShipments(): bool
    {
        return in_array($this->role, [UserRole::ADMIN, UserRole::OPERATOR], true);
    }

    /**
     * Relationship: Customer's shipments
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    public function reportedExceptions()
    {
        return $this->hasMany(ShipmentException::class, 'reported_by');
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class, 'created_by');
    }

    public function notifications()
    {
        return $this->hasMany(InAppNotification::class, 'user_id')->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(InAppNotification::class, 'user_id')->where('is_read', false)->latest();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id')->latest();
    }
}
