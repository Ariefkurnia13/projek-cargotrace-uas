<?php

namespace App\Http\Controllers\Web;

use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Base query scoped to RBAC
        $baseShipmentQuery = Shipment::query()->forUser($user);

        // Core operational metrics
        $totalShipments = (clone $baseShipmentQuery)->count();
        $inTransitCount = (clone $baseShipmentQuery)->where('current_status', ShipmentStatus::IN_TRANSIT)->count();
        $deliveredCount = (clone $baseShipmentQuery)->where('current_status', ShipmentStatus::DELIVERED)->count();
        $delayedCount = (clone $baseShipmentQuery)->where('is_delayed', true)->count();

        // Exceptions metric
        $exceptionsQuery = ShipmentException::query()
            ->whereHas('shipment', fn($q) => $q->forUser($user));
        $openExceptionsCount = (clone $exceptionsQuery)->whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        // Performance Rates
        $onTimeRate = $totalShipments > 0 
            ? round((($totalShipments - $delayedCount) / $totalShipments) * 100, 1) 
            : 100.0;
        $delayRate = $totalShipments > 0 
            ? round(($delayedCount / $totalShipments) * 100, 1) 
            : 0.0;
        $exceptionRate = $totalShipments > 0 
            ? round(($openExceptionsCount / $totalShipments) * 100, 1) 
            : 0.0;

        // Chart 1: Shipments breakdown by Status
        $statuses = [
            ShipmentStatus::CREATED->value => 0,
            ShipmentStatus::BOOKED->value => 0,
            ShipmentStatus::PICKED_UP->value => 0,
            ShipmentStatus::IN_TRANSIT->value => 0,
            ShipmentStatus::DELIVERED->value => 0,
        ];
        $statusCounts = (clone $baseShipmentQuery)
            ->select('current_status', DB::raw('count(*) as aggregate'))
            ->groupBy('current_status')
            ->pluck('aggregate', 'current_status')
            ->toArray();

        foreach ($statusCounts as $st => $count) {
            $key = $st instanceof ShipmentStatus ? $st->value : (string) $st;
            if (isset($statuses[$key])) {
                $statuses[$key] = $count;
            }
        }

        // Chart 2: Shipments by Carrier (Top carriers)
        $carrierLabels = [];
        $carrierCounts = [];
        $carriersData = (clone $baseShipmentQuery)
            ->join('carriers', 'shipments.carrier_id', '=', 'carriers.id')
            ->select('carriers.name', DB::raw('count(shipments.id) as aggregate'))
            ->groupBy('carriers.name')
            ->orderByDesc('aggregate')
            ->limit(5)
            ->get();

        foreach ($carriersData as $item) {
            $carrierLabels[] = $item->name;
            $carrierCounts[] = $item->aggregate;
        }

        // Recent tracking activity feed
        $recentEvents = TrackingEvent::query()
            ->whereHas('shipment', fn($q) => $q->forUser($user))
            ->with(['shipment', 'location', 'creator'])
            ->latest('event_time')
            ->limit(6)
            ->get();

        // Recent high-priority delayed or exception shipments
        $criticalShipments = (clone $baseShipmentQuery)
            ->where(function($q) {
                $q->where('is_delayed', true)
                  ->orWhereHas('openExceptions');
            })
            ->with(['carrier', 'origin', 'destination', 'openExceptions'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'onTimeRate',
            'delayRate',
            'exceptionRate',
            'statuses',
            'carrierLabels',
            'carrierCounts',
            'recentEvents',
            'criticalShipments'
        ));
    }
}
