@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dashboard Overview</h3>
        <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}. Here's what's happening today.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-light border bg-white text-muted fw-semibold px-3 py-2"><i class="bi bi-calendar-event me-2"></i> Last 30 Days</button>
        <button class="btn btn-primary fw-semibold px-4 py-2"><i class="bi bi-box-arrow-up me-2"></i> Export</button>
    </div>
</div>

<!-- 4 KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper bg-light-primary">
                    <i class="bi bi-folder"></i>
                </div>
                <span class="badge text-success bg-white border border-success border-opacity-25 rounded-pill">+12% <i class="bi bi-graph-up-arrow"></i></span>
            </div>
            <div class="text-muted small fw-semibold text-uppercase tracking-wide mb-1">Total Projects</div>
            <h2 class="fw-bold mb-0 text-dark">{{ str_pad($totalProjects ?? 0, 2, '0', STR_PAD_LEFT) }}</h2>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper bg-light-warning">
                    <i class="bi bi-check-circle"></i>
                </div>
                <span class="badge text-secondary bg-white border border-secondary border-opacity-25 rounded-pill">Stable <i class="bi bi-dash"></i></span>
            </div>
            <div class="text-muted small fw-semibold text-uppercase tracking-wide mb-1">Active Tasks</div>
            <h2 class="fw-bold mb-0 text-dark">{{ str_pad($activeTasks ?? 0, 2, '0', STR_PAD_LEFT) }}</h2>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper bg-light-primary" style="background-color: #f0f4fb;">
                    <i class="bi bi-people"></i>
                </div>
                <span class="text-dark small fw-semibold">{{ $teamCapacity ?? 0 }}% Cap</span>
            </div>
            <div class="text-muted small fw-semibold text-uppercase tracking-wide mb-2">Team Capacity</div>
            <div class="progress mt-auto" style="height: 8px;">
                <div class="progress-bar" style="background-color: #4a5c78; width: {{ $teamCapacity ?? 0 }}%"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm p-3 border-danger border-opacity-25" style="border-width: 1px !important;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon-wrapper bg-light-danger">
                    <i class="bi bi-exclamation-lg"></i>
                </div>
                <span class="text-danger small fw-semibold">Critical</span>
            </div>
            <div class="text-muted small fw-semibold text-uppercase tracking-wide mb-1">Upcoming Deadlines</div>
            <h2 class="fw-bold mb-0 text-dark">{{ str_pad($upcomingDeadlines ?? 0, 2, '0', STR_PAD_LEFT) }}</h2>
        </div>
    </div>
</div>

<!-- Charts & Activity -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Task Completion Rate</h5>
                    <p class="text-muted small mb-0">Weekly performance vs previous period</p>
                </div>
                <div class="d-flex gap-3">
                    <span class="small fw-semibold text-muted d-flex align-items-center gap-2"><div style="width: 8px; height: 8px; border-radius: 50%; background-color: #0d5cdd;"></div> This Week</span>
                    <span class="small fw-semibold text-muted d-flex align-items-center gap-2"><div style="width: 8px; height: 8px; border-radius: 50%; background-color: #ced4da;"></div> Last Week</span>
                </div>
            </div>
            <div style="height: 250px;">
                <canvas id="completionChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">Recent Activity</h5>
                <a href="#" class="text-primary text-decoration-none small fw-semibold">View All</a>
            </div>
            
            <div class="activity-feed">
                @if(isset($recentActivities) && count($recentActivities) > 0)
                    @foreach($recentActivities as $activity)
                    <div class="d-flex gap-3 mb-4">
                        <div class="position-relative">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($activity->user->name ?? 'U') }}&background=random" class="avatar" style="width: 40px; height: 40px;">
                            <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1" style="transform: translate(25%, 25%); box-shadow: 0 0 5px rgba(0,0,0,0.1);">
                                @if(str_contains(strtolower($activity->description), 'update') || str_contains(strtolower($activity->description), 'change'))
                                    <i class="bi bi-pencil-fill text-primary" style="font-size: 0.6rem;"></i>
                                @elseif(str_contains(strtolower($activity->description), 'comment'))
                                    <i class="bi bi-chat-fill text-warning" style="font-size: 0.6rem;"></i>
                                @else
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 0.6rem;"></i>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="mb-1 text-dark" style="font-size: 0.9rem;">
                                <span class="fw-bold">{{ $activity->user->name ?? 'User' }}</span> 
                                {{ $activity->description }} 
                                @if($activity->task)
                                <span class="fw-semibold text-primary">{{ $activity->task->name }}</span>
                                @endif
                            </p>
                            <span class="text-muted small">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-muted small">No recent activity.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Team Workload -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white border-bottom border-light p-4 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0">Team Workload</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Sort by:</span>
            <select class="form-select form-select-sm bg-light border-0 fw-semibold text-dark" style="width: auto;">
                <option>Utilization</option>
                <option>Name</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead class="bg-light text-muted small fw-semibold text-uppercase tracking-wide" style="font-size: 0.75rem;">
                <tr>
                    <th class="ps-4 py-3">Team Member</th>
                    <th class="py-3">Current Focus</th>
                    <th class="py-3">Workload</th>
                    <th class="py-3">Efficiency</th>
                    <th class="py-3">Status</th>
                </tr>
            </thead>
            <tbody class="border-top border-light">
                @if(isset($teamWorkload) && count($teamWorkload) > 0)
                    @foreach($teamWorkload as $member)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($member['name']) }}&background=random" class="avatar" style="width: 36px; height: 36px;">
                                <div>
                                    <div class="fw-bold text-dark">{{ $member['name'] }}</div>
                                    <div class="text-muted small">{{ $member['role'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-dark py-3">{{ $member['focus'] }}</td>
                        <td class="py-3">
                            <div class="progress" style="height: 6px; width: 100px; background-color: #f1f3f5;">
                                <div class="progress-bar {{ $member['workload_pct'] > 80 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $member['workload_pct'] }}%"></div>
                            </div>
                        </td>
                        <td class="fw-bold text-dark py-3">{{ $member['efficiency'] }}</td>
                        <td class="py-3">
                            @if($member['status'] == 'Focused')
                                <span class="badge text-primary bg-primary bg-opacity-10 rounded-pill px-3 py-2 fw-semibold border border-primary border-opacity-25">{{ $member['status'] }}</span>
                            @else
                                <span class="badge text-danger bg-danger bg-opacity-10 rounded-pill px-3 py-2 fw-semibold border border-danger border-opacity-25">{{ $member['status'] }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="5" class="text-center py-4 text-muted">No workload data available.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    var ctx = document.getElementById('completionChart').getContext('2d');
    var completionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($days ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']) !!},
            datasets: [
                {
                    label: 'This Week',
                    data: {!! json_encode($thisWeek ?? [28,35,22,48,32,18,10]) !!},
                    backgroundColor: '#0d5cdd',
                    borderWidth: 0,
                    borderRadius: 4,
                    barPercentage: 0.8,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Last Week',
                    data: {!! json_encode($lastWeek ?? [15,25,18,40,35,22,12]) !!},
                    backgroundColor: '#ced4da',
                    borderWidth: 0,
                    borderRadius: 4,
                    barPercentage: 0.8,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f3f5',
                        drawBorder: false
                    },
                    border: { display: false },
                    ticks: { display: false }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    border: { display: false }
                }
            }
        }
    });
</script>
@endpush
