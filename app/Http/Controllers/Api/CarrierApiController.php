<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierApiController extends Controller
{
    public function index(): JsonResponse
    {
        $carriers = Carrier::active()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $carriers,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:carriers,code'],
            'name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'in:OCEAN,AIR,ROAD,RAIL'],
        ]);

        $carrier = Carrier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Carrier registered successfully.',
            'data' => $carrier,
        ], 201);
    }
}
