<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMunicipalityRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'governorate_id' => 'sometimes|integer|exists:governorates,id',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|integer|exists:cities,id',
            'number_of_inhabitants' => 'sometimes|integer|min:0',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
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
