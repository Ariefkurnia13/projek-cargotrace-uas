<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageShipments() ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:60'],
            'total_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'total_volume_cbm' => ['required', 'numeric', 'min:0.01'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'estimated_arrival' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['required_with:items', 'string', 'max:150'],
            'items.*.sku' => ['nullable', 'string', 'max:60'],
            'items.*.package_type' => ['nullable', 'string', 'max:40'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'items.*.declared_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_hazardous' => ['nullable', 'boolean'],
        ];
    }
}
