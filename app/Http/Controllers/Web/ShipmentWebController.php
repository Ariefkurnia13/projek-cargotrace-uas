<?php

namespace App\Http\Controllers\Web;

use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentStatusRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ExternalApiService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ShipmentWebController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService,
        protected ExternalApiService $externalApiService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Shipment::query()
            ->forUser($user)
            ->with(['carrier', 'customer', 'origin', 'destination', 'currentLocation'])
            ->filter($request->only([
                'search', 'status', 'carrier_id', 'origin_id', 'destination_id', 'is_delayed', 'date_from', 'date_to'
            ]));

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['shipment_number', 'expected_departure', 'expected_arrival', 'current_status', 'is_delayed', 'created_at'];

        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $shipments = $query->paginate(10)->withQueryString();

        $carriers = Carrier::active()->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'carriers', 'locations'));
    }

    public function show(Request $request, Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'items',
            'exceptions.reporter',
            'exceptions.resolver',
        ]);

        $timeline = $this->trackingService->getTimeline($shipment);

        // Fetch situational weather at current active coordinate
        $activeLocation = $shipment->currentLocation ?? $shipment->origin;
        $weatherData = null;
        if ($activeLocation && $activeLocation->latitude && $activeLocation->longitude) {
            $weatherData = $this->externalApiService->getWeather(
                (float) $activeLocation->latitude,
                (float) $activeLocation->longitude
            );
        }

        // Available locations for status update / tracking modal
        $locations = Location::orderBy('name')->get();

        return view('shipments.show', compact('shipment', 'timeline', 'weatherData', 'locations'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Shipment::class);

        $carriers = Carrier::active()->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'CUSTOMER')->orderBy('name')->get();

        return view('shipments.create', compact('carriers', 'locations', 'customers'));
    }

    public function store(StoreShipmentRequest $request)
    {
        Gate::authorize('create', Shipment::class);

        $shipment = $this->shipmentService->createShipment(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment #{$shipment->shipment_number} created successfully.");
    }

    public function edit(Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $carriers = Carrier::active()->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.edit', compact('shipment', 'carriers', 'locations'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $validated = $request->validate([
            'cargo_type' => ['nullable', 'string', 'max:60'],
            'carrier_id' => ['nullable', 'exists:carriers,id'],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'total_volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->shipmentService->updateShipment($shipment, $validated, $request->user());

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment #{$shipment->shipment_number} updated successfully.");
    }

    public function updateStatus(UpdateShipmentStatusRequest $request, Shipment $shipment)
    {
        Gate::authorize('updateStatus', $shipment);

        try {
            $this->shipmentService->transitionStatus(
                $shipment,
                $request->input('target_status'),
                $request->user(),
                $request->input('description'),
                $request->input('location_id') ? (int) $request->input('location_id') : null
            );

            return back()->with('success', "Shipment status updated to {$request->input('target_status')}.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
