<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentStatusRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ShipmentApiController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $shipments = Shipment::query()
            ->forUser($user)
            ->with(['carrier', 'origin', 'destination', 'currentLocation'])
            ->filter($request->only([
                'search', 'status', 'carrier_id', 'origin_id', 'destination_id', 'is_delayed', 'date_from', 'date_to'
            ]))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
        ], 200);
    }

    public function store(StoreShipmentRequest $request): JsonResponse
    {
        Gate::authorize('create', Shipment::class);

        $shipment = $this->shipmentService->createShipment(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully.',
            'data' => $shipment->load(['carrier', 'origin', 'destination', 'items']),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'items',
            'openExceptions',
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        Gate::authorize('view', $shipment);

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ], 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('update', $shipment);

        $validated = $request->validate([
            'cargo_type' => ['nullable', 'string', 'max:60'],
            'carrier_id' => ['nullable', 'exists:carriers,id'],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'total_volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = $this->shipmentService->updateShipment($shipment, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully.',
            'data' => $updated,
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('delete', $shipment);

        $shipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully.',
        ], 200);
    }

    public function updateStatus(UpdateShipmentStatusRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('updateStatus', $shipment);

        try {
            $updated = $this->shipmentService->transitionStatus(
                $shipment,
                $request->input('target_status'),
                $request->user(),
                $request->input('description'),
                $request->input('location_id') ? (int) $request->input('location_id') : null
            );

            return response()->json([
                'success' => true,
                'message' => "Shipment status transitioned to {$request->input('target_status')}.",
                'data' => $updated,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
