<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('project.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:projects',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pm_id' => 'required|exists:users,id',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after_or_equal:planned_start_date',
        ];
    }
}
