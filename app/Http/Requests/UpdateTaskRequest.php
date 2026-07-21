<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('task.assign');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'acceptance_criteria' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'reporter_id' => 'nullable|exists:users,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'watchers' => 'nullable|array',
            'watchers.*' => 'exists:users,id',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'task_type' => 'nullable|string|max:100',
            'module' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'weight' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'required|date',
            'status' => 'required|in:To Do,In Progress,Review,Done',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $projectId = $this->input('project_id');
            $weight = $this->input('weight');
            $taskId = $this->route('task') ? $this->route('task')->id : null;
            
            if ($projectId && $weight !== null && $taskId) {
                $currentTotalWeight = \App\Models\Task::where('project_id', $projectId)
                                    ->where('id', '!=', $taskId)
                                    ->sum('weight');
                                    
                if (($currentTotalWeight + (float)$weight) > 100) {
                    $validator->errors()->add('weight', 'The total task weights for this project cannot exceed 100%. Current total: ' . $currentTotalWeight . '%');
                }
            }
        });
    }
}
