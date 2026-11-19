<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageShipments() ?? false;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['nullable', 'exists:locations,id'],
            'location_name' => ['nullable', 'string', 'max:150'],
            'event_status' => ['nullable', 'string', 'in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED'],
            'event_type' => ['required', 'string', 'in:NORMAL,EXCEPTION,CHECKPOINT,DEPARTURE,ARRIVAL'],
            'event_time' => ['required', 'date'],
            'description' => ['required', 'string', 'min:3', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'exception_id' => ['nullable', 'exists:shipment_exceptions,id'],
        ];
    }
}
