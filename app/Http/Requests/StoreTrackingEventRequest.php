<?php

namespace App\Http\Requests;

use App\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->canManageShipments();
    }

    public function rules(): array
    {
        return [
            'location_id' => ['nullable', 'exists:locations,id'],
            'location_name' => ['nullable', 'string', 'max:150'],
            'event_status' => ['required', Rule::enum(ShipmentStatus::class)],
            'event_type' => ['required', 'string', 'in:NORMAL,CHECKPOINT,DEPARTURE,ARRIVAL,EXCEPTION'],
            'event_time' => ['required', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }
}