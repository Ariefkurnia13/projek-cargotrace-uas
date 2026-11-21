<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Operator & Admin yang boleh membuat shipment (Slide 05)
        return $this->user() && $this->user()->canManageShipments();
    }

    public function rules(): array
    {
        return [
            'shipment_number' => ['nullable', 'string', 'max:40', 'unique:shipments,shipment_number'],
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id', 'different:destination_id'],
            'destination_id' => ['required', 'exists:locations,id'],
            'cargo_type' => ['required', 'string', 'max:60'],
            'total_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'total_volume_cbm' => ['required', 'numeric', 'min:0.01'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'notes' => ['nullable', 'string'],
        ];
    }
}