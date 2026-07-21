@extends('layouts.app')

@section('title', 'Workload Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-white mb-0"><i class="bi bi-activity text-info me-2"></i>Team Workload</h4>
    </div>

    <div class="row">
        @foreach($workload as $wl)
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-white border-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title text-info mb-1">{{ $wl['name'] }}</h5>
                            <small class="text-muted">{{ $wl['email'] }}</small>
                        </div>
                        <span class="badge bg-{{ $wl['color'] }}">{{ $wl['status'] }}</span>
                    </div>

                    <div class="row text-center mt-4">
                        <div class="col-4 border-end border-secondary">
                            <h3 class="mb-0">{{ $wl['active_tasks'] }}</h3>
                            <small class="text-muted">Active Tasks</small>
                        </div>
                        <div class="col-4 border-end border-secondary">
                            <h3 class="mb-0">{{ $wl['estimated_hours'] }}<small class="fs-6">h</small></h3>
                            <small class="text-muted">Estimated</small>
                        </div>
                        <div class="col-4">
                            <h3 class="mb-0">{{ $wl['actual_hours'] }}<small class="fs-6">h</small></h3>
                            <small class="text-muted">Logged</small>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Capacity Usage (Est. vs Actual)</span>
                            @php
                                $percent = $wl['estimated_hours'] > 0 ? min(100, round(($wl['actual_hours'] / $wl['estimated_hours']) * 100)) : 0;
                            @endphp
                            <span>{{ $percent }}%</span>
                        </div>
                        <div class="progress bg-secondary" style="height: 6px;">
                            <div class="progress-bar bg-{{ $percent > 90 ? 'warning' : 'primary' }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
