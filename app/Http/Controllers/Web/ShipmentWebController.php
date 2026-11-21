<?php

namespace App\Http\Controllers\Web;

use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ExternalApiService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination', 'currentLocation']);

        // Data Isolation RBAC: Customer HANYA melihat kargo miliknya sendiri (Slide 05)
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Filter status, carrier, no. resi
        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        $shipments = $query->latest()->paginate(10)->withQueryString();
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'carriers', 'locations'));
    }

    public function create()
    {
        $this->authorizeOperator();

        $customers = User::where('role', 'CUSTOMER')->get();
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.create', compact('customers', 'carriers', 'locations'));
    }

        public function store(StoreShipmentRequest $request)
    {
        $validated = $request->validated();
        if (empty($validated['shipment_number'])) {
            $validated['shipment_number'] = 'CT-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        }
        $validated['current_status'] = ShipmentStatus::CREATED;
        $validated['current_location_id'] = $validated['origin_id'];
        $validated['estimated_arrival'] = $validated['expected_arrival'];

        $shipment = Shipment::create($validated);

        return redirect()->route('shipments.index')->with('success', "Shipment {$shipment->shipment_number} created successfully.");
    }

    public function show(Shipment $shipment, ExternalApiService $externalApiService)
    {
        $user = Auth::user();

        // Otorisasi: Customer tidak boleh melihat shipment orang lain (Slide 05 & 13)
        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            abort(403, 'Unauthorized: You can only view your own consignments.');
        }

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions.reporter',
        ]);

        // Mengambil riwayat perjalanan kronologis (Slide 07)
        $timeline = $shipment->trackingEvents;
        $locations = Location::orderBy('name')->get();

        // Mengambil cuaca real-time di titik koordinat kapal saat ini (Slide 09)
        $activeLocation = $shipment->currentLocation ?? $shipment->origin;
        $weatherData = null;
        if ($activeLocation && $activeLocation->latitude && $activeLocation->longitude) {
            $weatherData = $externalApiService->getWeather(
                (float) $activeLocation->latitude,
                (float) $activeLocation->longitude
            );
        }

        return view('shipments.show', compact('shipment', 'timeline', 'locations', 'weatherData'));
    }

    public function updateStatus(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorizeOperator();

        $request->validate([
            'target_status' => ['required', 'string'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $targetStatus = ShipmentStatus::from($request->input('target_status'));

            $shipmentService->transitionStatus(
                $shipment,
                $targetStatus,
                Auth::user(),
                $request->input('description')
            );

            if ($request->filled('location_id')) {
                $shipment->update(['current_location_id' => $request->input('location_id')]);
            }

            return back()->with('success', "Shipment status updated to {$targetStatus->value} successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function authorizeOperator(): void
    {
        if (! Auth::user()->canManageShipments()) {
            abort(403, 'Unauthorized action: Access restricted to Logistics Operators and Administrators.');
        }
    }
}