<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('member.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ];

        if ($userId) {
            $rules['employee_id'] = ['nullable', 'string', 'max:255', Rule::unique('users', 'employee_id')->ignore($userId)];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)];
            $rules['password'] = ['nullable', 'string', 'min:8'];
        } else {
            $rules['employee_id'] = ['nullable', 'string', 'max:255', Rule::unique('users', 'employee_id')];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')];
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }
}
