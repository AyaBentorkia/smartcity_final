<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:355',
            'address_text' => 'nullable|string',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
                         'city_id' => 'sometimes|integer|exists:cities,id', 

            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            // 'priority_score' => 'nullable|numeric|min:0|max:100',
            'urgency_level' => 'nullable|string|in:faible,moyenne,élevée',
            'status' => 'nullable|string|in:signalé,en cours,résolu',
            'reported_at' => 'nullable|date',
            'resolved_at' => 'nullable|date|after_or_equal:reported_at',
        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    $response = response()->json([
        'message' => 'Erreur de validation',
        'errors' => $validator->errors()
    ], 422);

    throw new \Illuminate\Validation\ValidationException($validator, $response);
}
}
