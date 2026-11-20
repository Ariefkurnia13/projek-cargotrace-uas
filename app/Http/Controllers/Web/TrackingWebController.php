<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class TrackingWebController extends Controller
{
    public function __construct(protected TrackingService $trackingService)
    {
    }

    public function store(StoreTrackingEventRequest $request, Shipment $shipment)
    {
        try {
            $this->trackingService->addEvent($shipment, Auth::user(), $request->validated());

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'Tracking checkpoint logged successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['event_time' => $e->getMessage()]);
        }
    }
}