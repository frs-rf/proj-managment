@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-white">Projects</h4>
    @can('project.create')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
        <i class="bi bi-plus-lg"></i> New Project
    </button>
    @endcan
</div>

<div class="card bg-dark text-white border-secondary">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover border-secondary" id="projects-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>PM</th>
                        <th>Status</th>
                        <th>Planned Start</th>
                        <th>Planned End</th>
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

<!-- Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="projectForm">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add/Edit Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="project_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code" id="code" required>
                                <button class="btn btn-outline-secondary text-white" type="button" id="btn-generate-code">Generate</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project Manager (PM)</label>
                            <select class="form-select" name="pm_id" id="pm_id" required>
                                <option value="" disabled selected>Select PM</option>
                                @foreach($projectManagers as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Planned Start Date</label>
                            <input type="date" class="form-control" name="planned_start_date" id="planned_start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Planned End Date</label>
                            <input type="date" class="form-control" name="planned_end_date" id="planned_end_date" required>
                        </div>
                    </div>



                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#projects-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('projects.data') }}",
            columns: [
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'pm_name', name: 'pm_name', searchable: false},
                {data: 'status', name: 'status'},
                {data: 'planned_start_date', name: 'planned_start_date'},
                {data: 'planned_end_date', name: 'planned_end_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                search: "",
                searchPlaceholder: "Search projects..."
            }
        });
        
        $('.dataTables_filter input').addClass('form-control form-control-sm border-secondary bg-dark text-white');
        $('.dataTables_length select').addClass('form-select form-select-sm border-secondary bg-dark text-white');
        
        // Form submission
        $('#projectForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let id = $('#project_id').val();
            let url = id ? "{{ url('projects') }}/" + id : "{{ route('projects.store') }}";
            let method = id ? 'PUT' : 'POST';
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#projectModal').modal('hide');
                        table.ajax.reload();
                        $('#projectForm')[0].reset();
                        $('#project_id').val('');
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        });

        // Edit button click
        $('#projects-table').on('click', '.edit-project', function() {
            let id = $(this).data('id');
            $.get("{{ url('projects') }}/" + id, function(response) {
                if (response.success) {
                    let project = response.data;
                    $('#project_id').val(project.id);
                    $('#code').val(project.code);
                    $('#name').val(project.name);
                    $('#description').val(project.description);
                    $('#pm_id').val(project.pm_id);
                    $('#planned_start_date').val(project.planned_start_date ? project.planned_start_date.split('T')[0] : '');
                    $('#planned_end_date').val(project.planned_end_date ? project.planned_end_date.split('T')[0] : '');
                    $('#projectModal').modal('show');
                }
            });
        });
        
        // Generate Code
        $('#btn-generate-code').on('click', function() {
            let dateStr = new Date().toISOString().slice(0,10).replace(/-/g,"");
            let randomStr = Math.random().toString(36).substring(2, 6).toUpperCase();
            $('#code').val('PRJ-' + dateStr + '-' + randomStr);
        });
        
        // Reset form on modal close
        $('#projectModal').on('hidden.bs.modal', function () {
            $('#projectForm')[0].reset();
            $('#project_id').val('');
        });
    });
</script>
@endpush
