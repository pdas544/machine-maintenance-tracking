<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:in_progress,completed,unfixable_escalated',
            'mechanic_remarks' => [
                Rule::requiredIf($this->status === 'unfixable_escalated'),
                'nullable',
                'string',
            ],
        ];
    }
}
