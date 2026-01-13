<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->user_id, 'user_id'), 'email:strict'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/(^[\+]?[(]?[0-9]{3}[)]?[-\s\. ]?[0-9]{3}[-\s\. ]?[0-9]{4,6}$)/', Rule::unique(User::class)->ignore($this->user()->user_id, 'user_id')],
            'ic_number' => ['required', 'string', Rule::unique(User::class)->ignore($this->user()->user_id, 'user_id')],
        ];
    }
}
