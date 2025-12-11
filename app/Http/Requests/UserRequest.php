<?php

namespace App\Http\Requests;

use App\Http\Rules\PhoneNumberRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['required', new PhoneNumberRule],
                'active' => ['sometimes', 'boolean'],
                'roles' => ['array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ];
        } else {
            return [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['required', new PhoneNumberRule],
                'active' => ['sometimes', 'boolean'],
                'roles' => ['array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ];
        }
    }
}
