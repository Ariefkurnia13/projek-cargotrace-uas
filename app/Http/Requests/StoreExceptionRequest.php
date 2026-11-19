<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageShipments() ?? false;
    }

    public function rules(): array
    {
        return [
            'exception_type' => ['required', 'string', 'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE'],
            'severity' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH'],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'root_cause' => ['nullable', 'string', 'max:1000'],
            'impact_delay_hours' => ['nullable', 'numeric', 'min:0', 'max:720'],
        ];
    }
}
