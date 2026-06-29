<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ajuste selon la logique d'auth si nécessaire
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            // 'city' => 'nullable|string|max:100',
            'role' => ['nullable', Rule::in(array_map(fn($c) => $c->value, UserRole::cases()))],
            'status' => ['nullable', Rule::in(array_map(fn($c) => $c->value, UserStatus::cases()))],
            'photo_id' => 'nullable|exists:photos,id',
            'birthdate' => 'nullable|date',
            'cin' => 'nullable|string|max:20|unique:users,cin',
            'email_verified_at' => 'nullable|date',
            'category' => 'nullable|string',
            'municipality' => 'nullable|string',
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