<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class TrackingApiController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    public function index(Request $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('view', $shipment);

        $events = $this->trackingService->getTimeline($shipment);

        return response()->json([
            'success' => true,
            'shipment_number' => $shipment->shipment_number,
            'current_status' => $shipment->current_status->value,
            'current_location' => $shipment->currentLocation?->name ?? 'En Route',
            'data' => $events,
        ], 200);
    }

    public function store(StoreTrackingEventRequest $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('addTracking', $shipment);

        try {
            $event = $this->trackingService->addEvent(
                $shipment,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Tracking event recorded successfully.',
                'data' => $event->load(['location', 'creator']),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
