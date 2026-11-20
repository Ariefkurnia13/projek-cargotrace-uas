<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine whether the user can view any shipments list.
     * Customers will be scoped by scopeForUser() to only see their own.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific shipment.
     * Customer can ONLY view if customer_id matches their ID.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $shipment->customer_id === $user->id;
    }

    /**
     * Determine whether the user can create shipments.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can update the shipment.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can delete the shipment.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can transition shipment status.
     */
    public function updateStatus(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can log tracking events.
     */
    public function addTracking(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Determine whether the user can report/manage exceptions.
     */
    public function manageExceptions(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }
}
