<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PM Tracker') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- DataTables Server-Side (Using standard DT style for Bootstrap 5) -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="p-3 d-flex align-items-center justify-content-between text-white">
                <span class="fs-5 fw-bold" id="sidebar-title"><i class="bi bi-kanban text-primary"></i> PM Tracker</span>
                <button class="btn btn-link text-white p-0" id="toggle-sidebar"><i class="bi bi-list fs-4"></i></button>
            </div>
            <div class="nav flex-column px-2 mt-2">
                <a href="/home" class="nav-link text-white mb-1"><i class="bi bi-grid-1x2"></i> <span class="nav-text">Dashboard</span></a>
                <a href="/projects" class="nav-link text-white mb-1"><i class="bi bi-folder"></i> <span class="nav-text">Projects</span></a>
                <a href="/tasks" class="nav-link text-white mb-1"><i class="bi bi-check2-square"></i> <span class="nav-text">Tasks</span></a>
                @can('member.manage')
                    <a href="/workload" class="nav-link text-white mb-1"><i class="bi bi-activity"></i> <span class="nav-text">Workload</span></a>
                    <a href="/users" class="nav-link text-white mb-1"><i class="bi bi-people"></i> <span class="nav-text">Users</span></a>
                    <a href="/roles" class="nav-link text-white mb-1 ms-3"><i class="bi bi-shield-lock"></i> <span class="nav-text">Roles</span></a>
                @endcan
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar d-flex justify-content-between">
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">App</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">@yield('title', 'Dashboard')</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-3">
                        <button class="btn btn-link text-white p-0 position-relative" type="button" data-bs-toggle="dropdown" id="notification-btn">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" id="notification-badge" style="display: none;">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notification-list">
                            <li>
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <button class="btn btn-sm btn-link text-info text-decoration-none p-0" id="mark-all-read" style="display: none;">Mark all read</button>
                                </div>
                            </li>
                            <li id="no-notifications"><a class="dropdown-item text-muted text-center py-3" href="#">No new notifications</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name ?? 'User' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#toggle-sidebar').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('.nav-text').toggle();
                $('#sidebar-title').toggle();
            });

            // Fetch Notifications
            function fetchNotifications() {
                $.get("{{ route('notifications.unread') }}", function(res) {
                    if (res.success && res.data.length > 0) {
                        $('#notification-badge').show();
                        $('#mark-all-read').show();
                        $('#no-notifications').hide();
                        
                        // Clear existing
                        $('.notif-item').remove();
                        
                        res.data.forEach(function(notif) {
                            let msg = notif.data.message || 'New notification';
                            let html = `
                                <li class="notif-item">
                                    <div class="dropdown-item d-flex justify-content-between align-items-start text-wrap py-2 border-bottom border-secondary">
                                        <div class="me-auto">
                                            <div class="fw-bold text-info">${notif.data.task_code || 'Task'}</div>
                                            <span class="small">${msg}</span>
                                        </div>
                                        <button class="btn btn-sm text-muted ms-2 p-0 mark-read-btn" data-id="${notif.id}" title="Mark as read">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </li>
                            `;
                            $('#notification-list').append(html);
                        });
                    } else {
                        $('#notification-badge').hide();
                        $('#mark-all-read').hide();
                        $('#no-notifications').show();
                        $('.notif-item').remove();
                    }
                });
            }

            // Initial fetch
            fetchNotifications();

            // Mark single as read
            $(document).on('click', '.mark-read-btn', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Keep dropdown open
                let id = $(this).data('id');
                $.post("{{ url('notifications') }}/" + id + "/read", {
                    _token: "{{ csrf_token() }}"
                }, function() {
                    fetchNotifications();
                });
            });

            // Mark all as read
            $('#mark-all-read').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                $.post("{{ route('notifications.read_all') }}", {
                    _token: "{{ csrf_token() }}"
                }, function() {
                    fetchNotifications();
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
