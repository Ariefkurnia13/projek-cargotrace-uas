<?php

namespace App\Http\Controllers\Web;

use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
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

        return view('shipments.index', compact('shipments', 'carriers'));
    }

    public function create()
    {
        $this->authorizeOperator();

        $customers = User::where('role', 'CUSTOMER')->get();
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::all();

        return view('shipments.create', compact('customers', 'carriers', 'locations'));
    }

    public function store(StoreShipmentRequest $request)
    {
        $validated = $request->validated();
        $validated['current_status'] = ShipmentStatus::CREATED;
        $validated['current_location_id'] = $validated['origin_id'];
        $validated['estimated_arrival'] = $validated['expected_arrival']; // Default awal

        $shipment = Shipment::create($validated);

        return redirect()->route('shipments.index')->with('success', "Shipment {$shipment->shipment_number} created successfully.");
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        // Otorisasi: Customer tidak boleh melihat shipment orang lain (Slide 05 & 13)
        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            abort(403, 'Unauthorized: You can only view your own consignments.');
        }

        $shipment->load(['customer', 'carrier', 'origin', 'destination', 'currentLocation', 'items', 'trackingEvents.location', 'exceptions']);

        return view('shipments.show', compact('shipment'));
    }

    private function authorizeOperator(): void
    {
        if (! Auth::user()->canManageShipments()) {
            abort(403, 'Unauthorized action: Access restricted to Logistics Operators and Administrators.');
        }
    }
}