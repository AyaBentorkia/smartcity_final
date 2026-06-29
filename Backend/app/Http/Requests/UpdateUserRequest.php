<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 


class UpdateUserRequest extends FormRequest
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
                $user = auth('api')->user();

        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'address' => 'nullable|string|max:255',
            // 'city' => 'nullable|string|max:100',
            'birthdate' => 'nullable|date',
            'cin' => ['nullable', 'string', 'max:20', Rule::unique('users', 'cin')->ignore($user->id)],
            'photo_id' => 'nullable|exists:photos,id',
        ];
    }
}
