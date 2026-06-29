<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
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
            'name' => 'string|max:200',
            'description' => 'nullable|string|max:355',
            // 'city' => 'string|max:200',
            'latitude_center' => 'numeric|between:-90,90',
            'longitude_center' => 'numeric|between:-180,180',
            'rayon_km' => 'numeric|between:-180,180',
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
