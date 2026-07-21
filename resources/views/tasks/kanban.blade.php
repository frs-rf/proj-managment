<div class="row kanban-board">
    @foreach(['To Do', 'In Progress', 'Review', 'Done'] as $status)
    <div class="col-md-3 mb-4">
        <div class="card bg-dark border-secondary">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white">{{ $status }}</h6>
                <span class="badge bg-secondary rounded-pill">{{ isset($tasks[$status]) ? $tasks[$status]->count() : 0 }}</span>
            </div>
            <div class="card-body kanban-column p-2" data-status="{{ $status }}" style="min-height: 400px; background-color: #1a1d20;">
                @if(isset($tasks[$status]))
                    @foreach($tasks[$status] as $task)
                        <div class="card bg-secondary text-white mb-2 kanban-card" style="cursor: grab;" data-id="{{ $task->id }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-info">{{ $task->project->name ?? 'No Project' }}</small>
                                    <button class="btn btn-sm btn-link text-white p-0 edit-task" data-id="{{ $task->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </div>
                                <h6 class="card-title mb-1">{{ $task->name }}</h6>
                                <p class="card-text small mb-2 text-light">Assignee: {{ $task->assignee->name ?? 'Unassigned' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ new \Carbon\Carbon($task->end_date) < now() && $task->status !== 'Done' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ \Carbon\Carbon::parse($task->end_date)->format('M d') }}
                                    </span>
                                    <small>{{ $task->weight }}%</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
