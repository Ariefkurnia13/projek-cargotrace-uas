<?php

namespace App\Http\Controllers\Web;

use App\Enums\ExceptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExceptionRequest;
use App\Http\Requests\UpdateExceptionStatusRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ExceptionWebController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShipmentException::query()
            ->whereHas('shipment', fn($q) => $q->forUser($user))
            ->with(['shipment.carrier', 'shipment.customer', 'reporter', 'resolver']);

        if ($request->filled('type')) {
            $query->where('exception_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $exceptions = $query->latest('detected_at')->paginate(10)->withQueryString();

        return view('exceptions.index', compact('exceptions'));
    }

    public function store(StoreExceptionRequest $request, Shipment $shipment)
    {
        Gate::authorize('manageExceptions', $shipment);

        $this->exceptionService->createException(
            shipment: $shipment,
            type: $request->input('exception_type'),
            description: $request->input('description'),
            reporter: $request->user(),
            severity: $request->input('severity') ? \App\Enums\DelaySeverity::from($request->input('severity')) : null,
            impactDelayHours: (float) $request->input('impact_delay_hours', 0),
            rootCause: $request->input('root_cause')
        );

        return back()->with('success', 'Exception successfully logged and customer notified.');
    }

    public function updateStatus(UpdateExceptionStatusRequest $request, ShipmentException $exception)
    {
        Gate::authorize('manageExceptions', $exception->shipment);

        try {
            $this->exceptionService->updateStatus(
                exception: $exception,
                newStatus: $request->input('status'),
                actor: $request->user(),
                notes: $request->input('resolution_notes'),
                rootCause: $request->input('root_cause')
            );

            return back()->with('success', "Exception status updated to {$request->input('status')}.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
