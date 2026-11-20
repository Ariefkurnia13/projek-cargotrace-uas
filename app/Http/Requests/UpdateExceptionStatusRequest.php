<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExceptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageShipments() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:INVESTIGATING,RESOLVED'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
            'root_cause' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
