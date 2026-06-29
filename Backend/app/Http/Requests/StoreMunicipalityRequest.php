<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreMunicipalityRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|integer|exists:governorates,id',
            'address' => 'required|string|max:255',
            'city' => 'required|integer|exists:cities,id',
            'number_of_inhabitants' => 'integer|min:0|nullable',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    $response = response()->json([
        'message' => 'Erreur de validation',
        'errors' => $validator->errors()
    ], 422);
    Log::error('Validation failed for StoreMunicipalityRequest', [
        'errors' => $validator->errors()->toArray(),
        'input' => $this->all()
    ]);

    throw new \Illuminate\Validation\ValidationException($validator, $response);
}
}
