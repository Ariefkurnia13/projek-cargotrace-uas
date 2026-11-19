<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExceptionRequest;
use App\Http\Requests\UpdateExceptionStatusRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ExceptionApiController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    public function index(Request $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('view', $shipment);

        $exceptions = $shipment->exceptions()->with(['reporter', 'resolver'])->get();

        return response()->json([
            'success' => true,
            'shipment_number' => $shipment->shipment_number,
            'data' => $exceptions,
        ], 200);
    }

    public function store(StoreExceptionRequest $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        Gate::authorize('manageExceptions', $shipment);

        $exception = $this->exceptionService->createException(
            shipment: $shipment,
            type: $request->input('exception_type'),
            description: $request->input('description'),
            reporter: $request->user(),
            severity: $request->input('severity') ? \App\Enums\DelaySeverity::from($request->input('severity')) : null,
            impactDelayHours: (float) $request->input('impact_delay_hours', 0),
            rootCause: $request->input('root_cause')
        );

        return response()->json([
            'success' => true,
            'message' => 'Shipment exception reported successfully.',
            'data' => $exception->load('reporter'),
        ], 201);
    }

    public function updateStatus(UpdateExceptionStatusRequest $request, int $id): JsonResponse
    {
        $exception = ShipmentException::with('shipment')->find($id);

        if (!$exception) {
            return response()->json(['success' => false, 'message' => 'Exception record not found.'], 404);
        }

        Gate::authorize('manageExceptions', $exception->shipment);

        try {
            $updated = $this->exceptionService->updateStatus(
                exception: $exception,
                newStatus: $request->input('status'),
                actor: $request->user(),
                notes: $request->input('resolution_notes'),
                rootCause: $request->input('root_cause')
            );

            return response()->json([
                'success' => true,
                'message' => "Exception status updated to {$request->input('status')}.",
                'data' => $updated->load(['reporter', 'resolver']),
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
