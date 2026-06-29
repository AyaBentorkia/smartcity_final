<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class StoreIncidentRequest extends FormRequest
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
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            // 'city_id'               => 'nullable|integer|exists:cities,id', 
            // 'city'               => 'nullable|integer|exists:cities,id', 

            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            // 'priority_score' => 'nullable|numeric|min:0|max:100',
            'urgency_level' => 'nullable|string|in:faible,moyenne,élevée',
            'status' => 'nullable|string|in:signalé,en cours,résolu',
            'reported_at' => 'nullable|date',
            'resolved_at' => 'nullable|date|after_or_equal:reported_at',
        ];
    }
       /**
 * Retourne la réponse en cas d'échec de validation.
 */
protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    $response = response()->json([
        'message' => 'Erreur de validation',
        'errors' => $validator->errors()
    ], 422);

    throw new \Illuminate\Validation\ValidationException($validator, $response);
}
}