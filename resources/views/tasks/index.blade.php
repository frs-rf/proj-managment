@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-white">Tasks</h4>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="view_mode" id="btn_list_view" autocomplete="off" value="list" checked>
            <label class="btn btn-outline-primary" for="btn_list_view"><i class="bi bi-list-task"></i> List</label>
          
            <input type="radio" class="btn-check" name="view_mode" id="btn_kanban_view" autocomplete="off" value="kanban">
            <label class="btn btn-outline-primary" for="btn_kanban_view"><i class="bi bi-kanban"></i> Kanban</label>

            <input type="radio" class="btn-check" name="view_mode" id="btn_calendar_view" autocomplete="off" value="calendar">
            <label class="btn btn-outline-primary" for="btn_calendar_view"><i class="bi bi-calendar-event"></i> Calendar</label>
            
            <input type="radio" class="btn-check" name="view_mode" id="btn_gantt_view" autocomplete="off" value="gantt">
            <label class="btn btn-outline-primary" for="btn_gantt_view"><i class="bi bi-bar-chart-steps"></i> Gantt</label>
        </div>
        <div>
            <a href="{{ route('tasks.export') }}" class="btn btn-outline-danger me-2"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
            @can('task.assign')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                <i class="bi bi-plus-lg"></i> Add Task
            </button>
            @endcan
        </div>
    </div>
</div>

<div id="list-view-container">
    <div class="card bg-dark text-white border-secondary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover border-secondary" id="tasks-table" width="100%">
                    <thead>
                        <tr>
                            <th>Task Code</th>
                            <th>Project</th>
                            <th>Task Name</th>
                            <th>Assignee</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="kanban-view-container" style="display: none;">
    <div id="kanban-wrapper">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<div id="calendar-view-container" style="display: none;">
    <div id="calendar-wrapper">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<div id="gantt-view-container" style="display: none;">
    <div id="gantt-wrapper">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Task Details <span id="display_task_code" class="badge bg-secondary ms-2"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs border-secondary mb-3" id="taskTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-white" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="relations-tab" data-bs-toggle="tab" data-bs-target="#relations" type="button" role="tab" disabled>Relations</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="timesheet-tab" data-bs-toggle="tab" data-bs-target="#timesheet" type="button" role="tab" disabled>Time Log</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white" id="collaboration-tab" data-bs-toggle="tab" data-bs-target="#collaboration" type="button" role="tab" disabled>Collaboration</button>
                    </li>
                </ul>

                <form id="taskForm">
                    <input type="hidden" id="task_id" name="id">
                    
                    <div class="tab-content" id="taskTabsContent">
                        
                        <!-- General Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Project <span class="text-danger">*</span></label>
                                    <select class="form-select" name="project_id" id="project_id" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->code }} - {{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Assignee <span class="text-danger">*</span></label>
                                    <select class="form-select" name="assigned_to" id="assigned_to" required>
                                        <option value="">Unassigned</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Task Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" required placeholder="e.g. Integrate Payment Gateway">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description" rows="4" placeholder="Detailed background and context..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option value="To Do">To Do</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Review">Review</option>
                                        <option value="Done">Done</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select" name="priority" id="priority" required>
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="end_date" id="end_date" required>
                                </div>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div class="tab-pane fade" id="details" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Acceptance Criteria (Definition of Done)</label>
                                <textarea class="form-control" name="acceptance_criteria" id="acceptance_criteria" rows="3" placeholder="- Feature must pass 1000 concurrent users..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Task Type</label>
                                    <select class="form-select" name="task_type" id="task_type">
                                        <option value="New Feature">New Feature</option>
                                        <option value="Bug Fix">Bug Fix</option>
                                        <option value="Improvement">Improvement</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Documentation">Documentation</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Module / Component</label>
                                    <input type="text" class="form-control" name="module" id="module" placeholder="e.g. Authentication">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" id="start_date">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Reporter</label>
                                    <select class="form-select" name="reporter_id" id="reporter_id">
                                        <option value="">Auto (Current User)</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Reviewer / QA</label>
                                    <select class="form-select" name="reviewer_id" id="reviewer_id">
                                        <option value="">Unassigned</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Est. Effort (Hours)</label>
                                    <input type="number" step="0.5" min="0" class="form-control" name="estimated_hours" id="estimated_hours">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Actual Time (Hours)</label>
                                    <input type="number" step="0.5" min="0" class="form-control" name="actual_hours" id="actual_hours">
                                </div>
                            </div>
                        </div>

                        <!-- Relations Tab -->
                        <div class="tab-pane fade" id="relations" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Parent Task ID (Optional)</label>
                                <input type="number" class="form-control" name="parent_id" id="parent_id" placeholder="ID of parent task">
                            </div>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-3">
                                <h6>Subtasks</h6>
                                <small class="text-muted">Create subtasks by setting this Task's ID as their Parent.</small>
                            </div>
                            <ul class="list-group list-group-flush border border-secondary rounded mb-4" id="subtasks-list">
                                <!-- Populated dynamically -->
                            </ul>
                            
                            <h6>Dependencies</h6>
                            <p class="text-muted small mb-2">Dependencies tracking will be implemented in future phases.</p>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4 border-top border-secondary pt-3">
                        <button type="submit" class="btn btn-primary" id="btn-save-task">Save Task</button>
                    </div>
                </form>

                <!-- Collaboration & Timesheet Tab (Outside main form, only for existing tasks) -->
                <div class="tab-content">
                    <div class="tab-pane fade" id="timesheet" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Log Time</h6>
                                <form id="timesheetForm">
                                    <div class="mb-3">
                                        <label class="form-label small">Date</label>
                                        <input type="date" class="form-control form-control-sm border-secondary bg-dark text-white" id="ts_date" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label small">Start Time</label>
                                            <input type="time" class="form-control form-control-sm border-secondary bg-dark text-white" id="ts_start" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label small">End Time</label>
                                            <input type="time" class="form-control form-control-sm border-secondary bg-dark text-white" id="ts_end" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Notes</label>
                                        <textarea class="form-control form-control-sm border-secondary bg-dark text-white" id="ts_notes" rows="2"></textarea>
                                    </div>
                                    <button class="btn btn-sm btn-primary w-100" type="submit">Save Time Log</button>
                                </form>
                            </div>
                            <div class="col-md-8">
                                <h6>Time Entries</h6>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-dark border-secondary">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>User</th>
                                                <th>Duration</th>
                                                <th>Notes</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="timesheets-list">
                                            <!-- Populated dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-2">
                                    <strong>Total Actual Hours: <span id="display_actual_hours" class="text-info">0</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="collaboration" role="tabpanel">
                        <div class="row">
                            <div class="col-md-7">
                                <h6>Comments & Discussion</h6>
                                <div class="chat-box mb-3 p-2 border border-secondary rounded" id="comments-list" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Populated dynamically -->
                                </div>
                                <form id="commentForm">
                                    <div class="input-group">
                                        <input type="text" class="form-control border-secondary bg-dark text-white" id="comment_content" placeholder="Type your comment..." required>
                                        <button class="btn btn-primary" type="submit">Send</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-5">
                                <h6>Attachments</h6>
                                <form id="attachmentForm" enctype="multipart/form-data" class="mb-3">
                                    <div class="input-group">
                                        <input type="file" class="form-control form-control-sm border-secondary bg-dark text-white" id="attachment_file" name="file" required>
                                        <button class="btn btn-sm btn-primary" type="submit">Upload</button>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-sm table-dark border-secondary">
                                        <thead>
                                            <tr>
                                                <th>File</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="attachments-list">
                                            <!-- Populated dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <h6 class="mt-4">Activity Log</h6>
                                <div id="activity-list" class="small text-muted" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#tasks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tasks.data') }}",
            columns: [
                {data: 'task_code', name: 'task_code', defaultContent: '-'},
                {data: 'project_name', name: 'project_name', searchable: false},
                {data: 'name', name: 'name'},
                {data: 'assignee_name', name: 'assignee_name', searchable: false},
                {data: 'status', name: 'status'},
                {data: 'priority', name: 'priority', defaultContent: 'Medium'},
                {data: 'end_date', name: 'end_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                search: "",
                searchPlaceholder: "Search tasks..."
            }
        });
        
        $('.dataTables_filter input').addClass('form-control form-control-sm border-secondary bg-dark text-white');
        $('.dataTables_length select').addClass('form-select form-select-sm border-secondary bg-dark text-white');

        // View Mode Toggle
        $('input[name="view_mode"]').change(function() {
            let view = $(this).val();
            $('#list-view-container, #kanban-view-container, #calendar-view-container, #gantt-view-container').hide();
            
            if (view === 'kanban') {
                $('#kanban-view-container').show();
                loadKanban();
            } else if (view === 'calendar') {
                $('#calendar-view-container').show();
                loadCalendar();
            } else if (view === 'gantt') {
                $('#gantt-view-container').show();
                loadGantt();
            } else {
                $('#list-view-container').show();
                table.ajax.reload(null, false);
            }
        });

        function loadCalendar() {
            if ($('#calendar-wrapper').find('.fc').length === 0) {
                $.get("{{ route('tasks.calendar') }}", function(html) {
                    $('#calendar-wrapper').html(html);
                });
            }
        }
        
        function loadGantt() {
            if ($('#gantt-wrapper').find('.gantt').length === 0) {
                $.get("{{ route('tasks.gantt') }}", function(html) {
                    $('#gantt-wrapper').html(html);
                });
            }
        }

        // Global function for calendar to call
        window.openTaskEditModal = function(taskId) {
            let fakeBtn = $('<button class="edit-task" style="display:none" data-id="'+taskId+'"></button>');
            $('body').append(fakeBtn);
            fakeBtn.click();
            fakeBtn.remove();
        };

        function loadKanban() {
            $.get("{{ route('tasks.kanban') }}", function(html) {
                $('#kanban-wrapper').html(html);
                initSortable();
            });
        }

        function initSortable() {
            $('.kanban-column').each(function() {
                new Sortable(this, {
                    group: 'shared',
                    animation: 150,
                    ghostClass: 'bg-primary',
                    onEnd: function (evt) {
                        let taskId = $(evt.item).data('id');
                        let newStatus = $(evt.to).data('status');
                        
                        $.ajax({
                            url: "{{ route('tasks.update_status') }}",
                            type: "POST",
                            data: {
                                task_id: taskId,
                                status: newStatus
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                loadKanban();
                            },
                            error: function(xhr) {
                                alert('Error updating status');
                                loadKanban();
                            }
                        });
                    },
                });
            });
        }

        $('#taskForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let id = $('#task_id').val();
            let url = id ? "{{ url('tasks') }}/" + id : "{{ route('tasks.store') }}";
            let method = 'POST';
            if (id) {
                formData += '&_method=PUT';
            }
            
            // Fix empty numeric fields (Laravel might reject empty string for numeric if not configured correctly)
            let parsedData = new URLSearchParams(formData);
            if(!parsedData.get('start_date')) parsedData.delete('start_date');
            
            $.ajax({
                url: url,
                type: method,
                data: parsedData.toString(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#taskModal').modal('hide');
                        if($('input[name="view_mode"]:checked').val() === 'list') {
                            table.ajax.reload(null, false);
                        } else {
                            loadKanban();
                        }
                        $('#taskForm')[0].reset();
                        $('#task_id').val('');
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        alert('Error:\n' + errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert('Error: ' + xhr.responseJSON.message);
                    } else {
                        alert('An unexpected error occurred.');
                    }
                }
            });
        });

        $(document).on('click', '.edit-task', function() {
            let id = $(this).data('id');
            $.get("{{ url('tasks') }}/" + id, function(response) {
                if (response.success) {
                    let task = response.data;
                    $('#task_id').val(task.id);
                    $('#project_id').val(task.project_id);
                    $('#parent_id').val(task.parent_id);
                    $('#assigned_to').val(task.assigned_to);
                    $('#name').val(task.name);
                    $('#description').val(task.description);
                    $('#status').val(task.status);
                    $('#priority').val(task.priority);
                    $('#end_date').val(task.end_date ? task.end_date.split('T')[0] : '');
                    
                    $('#acceptance_criteria').val(task.acceptance_criteria);
                    $('#task_type').val(task.task_type);
                    $('#module').val(task.module);
                    $('#start_date').val(task.start_date ? task.start_date.split('T')[0] : '');
                    $('#reporter_id').val(task.reporter_id);
                    $('#reviewer_id').val(task.reviewer_id);
                    $('#estimated_hours').val(task.estimated_hours);
                    $('#actual_hours').val(task.actual_hours);
                    
                    $('#display_task_code').text(task.task_code || 'NEW');
                    $('#display_actual_hours').text(task.actual_hours || '0');
                    
                    $('#relations-tab, #collaboration-tab, #timesheet-tab').prop('disabled', false);
                    
                    let subHtml = '';
                    if(task.subtasks && task.subtasks.length > 0) {
                        task.subtasks.forEach(function(sub) {
                            subHtml += `<li class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                                ${sub.name} <span class="badge bg-primary">${sub.status}</span>
                            </li>`;
                        });
                    } else {
                        subHtml = '<li class="list-group-item bg-dark text-muted border-secondary">No subtasks found.</li>';
                    }
                    $('#subtasks-list').html(subHtml);

                    refreshComments(task.id, task.comments);
                    refreshAttachments(task.id, task.attachments);
                    refreshActivities(task.activities);
                    refreshTimesheets(task.id, task.timesheets);

                    $('#taskModal').modal('show');
                }
            });
        });
        
        function refreshTimesheets(taskId, timesheets) {
            let tHtml = '';
            if(timesheets && timesheets.length > 0) {
                timesheets.forEach(function(t) {
                    tHtml += `<tr>
                        <td>${t.date}</td>
                        <td>${t.user ? t.user.name : '-'}</td>
                        <td>${t.duration}h</td>
                        <td>${t.notes || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-danger del-timesheet" data-id="${t.id}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                tHtml = '<tr><td colspan="5" class="text-center text-muted">No time logged yet.</td></tr>';
            }
            $('#timesheets-list').html(tHtml);
            
            $('#timesheetForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/timesheets`,
                    type: 'POST',
                    data: {
                        date: $('#ts_date').val(),
                        start_time: $('#ts_start').val(),
                        end_time: $('#ts_end').val(),
                        notes: $('#ts_notes').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $('#timesheetForm')[0].reset();
                        $('#actual_hours').val(res.actual_hours);
                        $('#display_actual_hours').text(res.actual_hours);
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshTimesheets(taskId, response.data.timesheets);
                        });
                    },
                    error: function(xhr) {
                        alert("Failed to log time.");
                    }
                });
            });

            $('.del-timesheet').off('click').on('click', function() {
                let tid = $(this).data('id');
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/timesheets/${tid}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $('#actual_hours').val(res.actual_hours);
                        $('#display_actual_hours').text(res.actual_hours);
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshTimesheets(taskId, response.data.timesheets);
                        });
                    }
                });
            });
        }
        
        function refreshComments(taskId, comments) {
            let cHtml = '';
            if(comments && comments.length > 0) {
                comments.forEach(function(c) {
                    let date = new Date(c.created_at).toLocaleString();
                    cHtml += `<div class="p-2 mb-2 bg-secondary rounded position-relative">
                        <strong>${c.user ? c.user.name : 'User'}</strong> <small class="text-light">${date}</small>
                        <p class="mb-0 mt-1">${c.comment}</p>
                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 del-comment" data-id="${c.id}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>`;
                });
            } else {
                cHtml = '<p class="text-muted">No comments yet.</p>';
            }
            $('#comments-list').html(cHtml);
            $('#commentForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                let content = $('#comment_content').val();
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/comments`,
                    type: 'POST',
                    data: { comment: content },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $('#comment_content').val('');
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshComments(taskId, response.data.comments);
                        });
                    }
                });
            });

            $('.del-comment').off('click').on('click', function() {
                let cid = $(this).data('id');
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/comments/${cid}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshComments(taskId, response.data.comments);
                        });
                    }
                });
            });
        }

        function refreshAttachments(taskId, attachments) {
            let aHtml = '';
            if(attachments && attachments.length > 0) {
                attachments.forEach(function(a) {
                    let date = new Date(a.created_at).toLocaleDateString();
                    aHtml += `<tr>
                        <td><a href="{{ url('tasks') }}/${taskId}/attachments/${a.id}" target="_blank" class="text-info">${a.file_name}</a></td>
                        <td>
                            <button class="btn btn-sm btn-danger del-attachment" data-id="${a.id}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            } else {
                aHtml = '<tr><td colspan="2" class="text-center text-muted">No attachments</td></tr>';
            }
            $('#attachments-list').html(aHtml);
            
            $('#attachmentForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/attachments`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        $('#attachment_file').val('');
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshAttachments(taskId, response.data.attachments);
                        });
                    }
                });
            });

            $('.del-attachment').off('click').on('click', function() {
                let aid = $(this).data('id');
                $.ajax({
                    url: `{{ url('tasks') }}/${taskId}/attachments/${aid}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        $.get("{{ url('tasks') }}/" + taskId, function(response) {
                            if(response.success) refreshAttachments(taskId, response.data.attachments);
                        });
                    }
                });
            });
        }
        
        function refreshActivities(activities) {
            let actHtml = '';
            if(activities && activities.length > 0) {
                activities.forEach(function(act) {
                    let date = new Date(act.created_at).toLocaleString();
                    let user = act.user ? act.user.name : 'System';
                    actHtml += `<div class="mb-2 pb-2 border-bottom border-secondary">
                        <strong>${user}</strong> ${act.action} this task<br>
                        <span class="text-muted" style="font-size: 0.8rem;">${date}</span>
                    </div>`;
                });
            } else {
                actHtml = 'No activity recorded yet.';
            }
            $('#activity-list').html(actHtml);
        }

        $('#taskModal').on('hidden.bs.modal', function () {
            $('#taskForm')[0].reset();
            $('#task_id').val('');
            $('#display_task_code').text('');
            $('#relations-tab, #collaboration-tab').prop('disabled', true);
            $('#taskTabs button[data-bs-target="#general"]').tab('show');
            
            // Make sure the form is visible again if it was hidden
            $('#taskForm').show();
        });
        
        // Custom tab switching logic to ensure form submit button is visible/hidden correctly
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $(e.target).attr("data-bs-target"); // activated tab
            if(target === '#collaboration') {
                $('#taskForm').hide();
            } else {
                $('#taskForm').show();
            }
        });
    });
</script>
@endpush
