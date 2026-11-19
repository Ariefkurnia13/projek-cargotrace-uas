<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class TrackingWebController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService
    ) {}

    public function store(StoreTrackingEventRequest $request, Shipment $shipment)
    {
        Gate::authorize('addTracking', $shipment);

        try {
            $this->trackingService->addEvent(
                $shipment,
                $request->user(),
                $request->validated()
            );

            return back()->with('success', 'Tracking event successfully appended to historical timeline.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
