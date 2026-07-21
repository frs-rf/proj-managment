@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-white">Roles</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal">
        <i class="bi bi-plus-lg"></i> New Role
    </button>
</div>

<div class="card bg-dark text-white border-secondary">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover border-secondary" id="roles-table" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Guard Name</th>
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

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="roleForm">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add/Edit Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="role_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" class="form-control" name="name" id="name" required placeholder="e.g. auditor">
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
        let table = $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('roles.data') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'guard_name', name: 'guard_name'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                search: "",
                searchPlaceholder: "Search roles..."
            }
        });
        
        $('.dataTables_filter input').addClass('form-control form-control-sm border-secondary bg-dark text-white');
        $('.dataTables_length select').addClass('form-select form-select-sm border-secondary bg-dark text-white');
        
        // Form submission
        $('#roleForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let id = $('#role_id').val();
            let url = id ? "{{ url('roles') }}/" + id : "{{ route('roles.store') }}";
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
                        $('#roleModal').modal('hide');
                        table.ajax.reload();
                        $('#roleForm')[0].reset();
                        $('#role_id').val('');
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                }
            });
        });

        // Edit button click
        $('#roles-table').on('click', '.edit-role', function() {
            let id = $(this).data('id');
            $.get("{{ url('roles') }}/" + id, function(response) {
                if (response.success) {
                    $('#role_id').val(response.data.id);
                    $('#name').val(response.data.name);
                    $('#roleModal').modal('show');
                }
            });
        });

        // Delete button click
        $('#roles-table').on('click', '.delete-role', function() {
            if (confirm("Are you sure you want to delete this role?")) {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ url('roles') }}/" + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            table.ajax.reload();
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong'));
                    }
                });
            }
        });
        
        // Reset form on modal close
        $('#roleModal').on('hidden.bs.modal', function () {
            $('#roleForm')[0].reset();
            $('#role_id').val('');
        });
    });
</script>
@endpush
