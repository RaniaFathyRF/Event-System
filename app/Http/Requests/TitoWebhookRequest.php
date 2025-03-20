<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TitoWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference' => 'required|string',
            'release_id' => 'required|numeric',
            'release_title' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'phone_number' => 'nullable|string',
            'state_name' => 'required|string',
            '_type' => 'required|string|in:ticket', // Ensure it's a ticket type
        ];
    }
}
