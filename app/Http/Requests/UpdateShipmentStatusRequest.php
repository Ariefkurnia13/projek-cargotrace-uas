<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageShipments() ?? false;
    }

    public function rules(): array
    {
        return [
            'target_status' => ['required', 'string', 'in:BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED'],
            'description' => ['nullable', 'string', 'max:500'],
            'location_id' => ['nullable', 'exists:locations,id'],
        ];
    }
}
