@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h4 class="text-white">Dashboard Overview</h4>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-2 col-6 mb-3">
        <div class="card bg-dark border-secondary text-center text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Projects</h6>
                <h2 class="mb-0">{{ $kpi['total_projects'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card bg-dark border-secondary text-center text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Tasks</h6>
                <h2 class="mb-0">{{ $kpi['total_tasks'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card bg-dark border-secondary text-center text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-success">Completed Tasks</h6>
                <h2 class="mb-0">{{ $kpi['completed_tasks'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-3">
        <div class="card bg-dark border-secondary text-center text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-danger">Overdue Tasks</h6>
                <h2 class="mb-0">{{ $kpi['overdue_tasks'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-3">
        <div class="card bg-dark border-secondary text-center text-white h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Active Resources</h6>
                <h2 class="mb-0">{{ $kpi['active_resources'] }} Members</h2>
            </div>
        </div>
    </div>
</div>

<!-- Project Health and S-Curve -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card bg-dark border-secondary text-white">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Project S-Curve (PV vs EV)</h5>
                <form method="GET" action="{{ route('home') }}" class="d-flex" id="projectSelector">
                    <select name="project_id" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="document.getElementById('projectSelector').submit()">
                        @if($projects->isEmpty())
                            <option>No active projects</option>
                        @endif
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ $selectedProject && $selectedProject->id == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body">
                @if($selectedProject)
                    <div class="row mb-3 text-center">
                        <div class="col-md-4">
                            <span class="text-muted d-block">SPI (Schedule Performance Index)</span>
                            <h4 class="mb-0 text-{{ $health['color'] ?? 'secondary' }}">{{ $health['spi'] ?? '0.00' }}</h4>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Project Status</span>
                            <h4 class="mb-0 text-{{ $health['color'] ?? 'secondary' }}">{{ $health['status'] ?? 'N/A' }}</h4>
                        </div>
                    </div>
                    
                    <div style="height: 400px; width: 100%;">
                        <canvas id="sCurveChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bar-chart text-secondary" style="font-size: 3rem;"></i>
                        <p class="mt-3">No active project selected to display S-Curve.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if($selectedProject && !empty($sCurveData))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('sCurveChart').getContext('2d');
        const sCurveData = @json($sCurveData);
        
        const labels = sCurveData.map(d => d.date);
        const pvData = sCurveData.map(d => d.pv);
        const evData = sCurveData.map(d => d.ev);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Planned Value (PV %)',
                        data: pvData,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Earned Value (EV %)',
                        data: evData,
                        borderColor: '#198754',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        spanGaps: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#adb5bd',
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: '#adb5bd'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + '%';
                                }
                                return label;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    });
</script>
@endpush
@endif
