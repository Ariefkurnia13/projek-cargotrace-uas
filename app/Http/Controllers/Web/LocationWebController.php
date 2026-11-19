<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\ExternalApiService;
use Illuminate\Http\Request;

class LocationWebController extends Controller
{
    public function __construct(
        protected ExternalApiService $externalApiService
    ) {}

    public function index()
    {
        $locations = Location::orderBy('name')->paginate(15);
        return view('locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:locations,code'],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'is_hub' => ['nullable', 'boolean'],
        ]);

        $validated['is_hub'] = $request->boolean('is_hub');
        Location::create($validated);

        return back()->with('success', 'Location created successfully.');
    }

    public function geocode(Request $request)
    {
        $query = $request->query('q', '');
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = $this->externalApiService->searchGeocoding($query);
        return response()->json($results);
    }
}
