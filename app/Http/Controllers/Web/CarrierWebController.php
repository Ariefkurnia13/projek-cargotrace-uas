<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierWebController extends Controller
{
    public function index()
    {
        $carriers = Carrier::withCount('shipments')->orderBy('name')->paginate(15);
        return view('carriers.index', compact('carriers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:carriers,code'],
            'name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'in:OCEAN,AIR,ROAD,RAIL'],
        ]);

        Carrier::create($validated);

        return back()->with('success', 'Carrier added successfully.');
    }
}
