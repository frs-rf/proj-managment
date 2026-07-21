@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-white">Users</h4>
    @can('member.manage')
        <button class="btn btn-primary" id="btn-new-user" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="bi bi-plus-lg"></i> New User
        </button>
    @endcan
</div>

<div class="card bg-dark text-white border-secondary">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover border-secondary" id="users-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Roles</th>
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

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="userForm">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Add/Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" name="employee_id" id="employee_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" id="department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <select class="form-select" name="roles[]" id="roles" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>
                    <div class="mb-3" id="password_group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password">
                        <small class="text-muted">Leave blank to keep current password on edit</small>
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
        let table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.data') }}",
            columns: [
                {data: 'employee_id', name: 'employee_id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'department', name: 'department'},
                {data: 'status', name: 'status'},
                {data: 'roles', name: 'roles', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                search: "",
                searchPlaceholder: "Search users..."
            }
        });
        
        // Add minimal DataTables custom styling to fit Jira dark theme
        $('.dataTables_filter input').addClass('form-control form-control-sm border-secondary bg-dark text-white');
        $('.dataTables_length select').addClass('form-select form-select-sm border-secondary bg-dark text-white');
        
        // Form submission
        $('#userForm').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            let id = $('#user_id').val();
            let url = id ? "{{ url('users') }}/" + id : "{{ route('users.store') }}";
            let method = 'POST';
            if (id) {
                formData += '&_method=PUT';
            }
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#userModal').modal('hide');
                        table.ajax.reload();
                        $('#userForm')[0].reset();
                        $('#user_id').val('');
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

        // New User button click
        $('#btn-new-user').on('click', function() {
            $('#userForm')[0].reset();
            $('#user_id').val('');
            $('#password').attr('required', true);
            $('#roles').val([]).trigger('change');
            $('.modal-title').text('New User');
            $('#password_group small').text('Password is required (min 8 chars).');
        });

        // Edit button click
        $('#users-table').on('click', '.edit-user', function() {
            let id = $(this).data('id');
            $.get("{{ url('users') }}/" + id, function(response) {
                if (response.success) {
                    let user = response.data;
                    $('#user_id').val(user.id);
                    $('#employee_id').val(user.employee_id);
                    $('#name').val(user.name);
                    $('#email').val(user.email);
                    $('#department').val(user.department);
                    $('#status').val(user.status);
                    
                    $('#password').removeAttr('required');
                    $('.modal-title').text('Edit User');
                    $('#password_group small').text('Leave blank to keep current password on edit');
                    
                    let userRoles = user.roles ? user.roles.map(r => r.name) : [];
                    $('#roles').val(userRoles);
                    
                    $('#userModal').modal('show');
                }
            });
        });
        
        // Reset form on modal close
        $('#userModal').on('hidden.bs.modal', function () {
            $('#userForm')[0].reset();
            $('#user_id').val('');
        });
    });
</script>
@endpush
