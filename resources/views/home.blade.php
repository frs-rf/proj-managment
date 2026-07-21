@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Main Charts -->
        <div class="col-md-8 mb-4">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Burn-down Chart (Tasks Remaining)</h5>
                </div>
                <div class="card-body">
                    <canvas id="burndownChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <!-- Status Distribution -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tasks by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Burn-down Chart
    var burndownCtx = document.getElementById('burndownChart').getContext('2d');
    var burndownChart = new Chart(burndownCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($burndownLabels ?? []) !!},
            datasets: [{
                label: 'Ideal Tasks Remaining',
                data: {!! json_encode($idealLine ?? []) !!},
                borderColor: '#6c757d',
                borderDash: [5, 5],
                fill: false,
                tension: 0
            }, {
                label: 'Actual Tasks Remaining',
                data: {!! json_encode($actualLine ?? []) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Status Chart
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($tasksByStatus ?? [])) !!},
            datasets: [{
                data: {!! json_encode(array_values($tasksByStatus ?? [])) !!},
                backgroundColor: ['#dc3545', '#fd7e14', '#0d6efd', '#198754']
            }]
        },
        options: { responsive: true }
    });
</script>
@endpush
