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
        <div class="sidebar d-flex flex-column" id="sidebar">
            <div class="p-4 d-flex align-items-center gap-3 border-bottom border-light">
                <div class="trackcorp-logo-icon flex-shrink-0">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div class="sidebar-header-text overflow-hidden">
                    <div class="fw-bold fs-5 text-dark" style="line-height: 1.2;">TrackCorp</div>
                    <div class="small text-muted" style="font-size: 0.75rem;">Enterprise Plan</div>
                </div>
                <button class="btn btn-sm btn-link text-muted ms-auto p-0" id="toggle-sidebar" title="Toggle Sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>
            
            <div class="nav flex-column py-3 flex-grow-1 overflow-auto">
                <a href="/home" class="nav-link {{ request()->is('home') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> 
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="/tasks" class="nav-link {{ request()->is('tasks*') ? 'active' : '' }}">
                    <i class="bi bi-check2-square"></i> 
                    <span class="nav-text">Tasks</span>
                </a>
                <a href="/projects" class="nav-link {{ request()->is('projects*') ? 'active' : '' }}">
                    <i class="bi bi-folder"></i> 
                    <span class="nav-text">Projects</span>
                </a>
                @can('member.manage')
                    <a href="/workload" class="nav-link {{ request()->is('workload*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> 
                        <span class="nav-text">Team</span>
                    </a>
                    <a href="/users" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i> 
                        <span class="nav-text">Users</span>
                    </a>
                    <a href="/roles" class="nav-link {{ request()->is('roles*') ? 'active' : '' }} ms-3">
                        <i class="bi bi-shield-lock"></i> 
                        <span class="nav-text">Roles</span>
                    </a>
                @endcan
            </div>
            
            <div class="p-3 border-top border-light">
                @can('task.assign')
                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 mb-3" data-bs-toggle="modal" data-bs-target="#taskModal">
                    <i class="bi bi-plus-lg"></i> <span class="sidebar-bottom-btn-text">New Task</span>
                </button>
                @endcan
                <a href="#" class="nav-link py-2 text-muted">
                    <i class="bi bi-gear"></i> <span class="nav-text">Settings</span>
                </a>
                <a href="#" class="nav-link py-2 text-muted">
                    <i class="bi bi-question-circle"></i> <span class="nav-text">Support</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar d-flex justify-content-between">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="search-wrapper w-100" style="max-width: 400px;">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control search-input" placeholder="Search projects, tasks...">
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <a href="#" class="text-primary text-decoration-none fw-semibold small d-none d-md-block">Upgrade Plan</a>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0 position-relative" type="button" data-bs-toggle="dropdown" id="notification-btn">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" id="notification-badge" style="display: none;">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 300px; max-height: 400px; overflow-y: auto;" id="notification-list">
                            <li>
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span class="text-dark fw-bold">Notifications</span>
                                    <button class="btn btn-sm btn-link text-primary text-decoration-none p-0" id="mark-all-read" style="display: none;">Mark all read</button>
                                </div>
                            </li>
                            <li id="no-notifications"><a class="dropdown-item text-muted text-center py-3" href="#">No new notifications</a></li>
                        </ul>
                    </div>
                    
                    <a href="#" class="text-muted"><i class="bi bi-question-circle fs-5"></i></a>

                    <div class="dropdown d-flex align-items-center">
                        <button class="btn btn-link text-dark text-decoration-none d-flex align-items-center gap-2 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=0d5cdd&color=fff" alt="User" class="avatar">
                            <span class="fw-semibold d-none d-md-block">{{ Auth::user()->name ?? 'User' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
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
