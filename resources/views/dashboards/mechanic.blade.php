@extends('layouts.app')

@section('content')
    <div class="container-fluid px-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $pending = $tickets->where('status', 'pending')->count();
            $inProgress = $tickets->where('status', 'in_progress')->count();
            $completed = $tickets->where('status', 'completed')->count();
            $escalated = $tickets->where('status','unfixable_escalated')->count();
        @endphp
        <div class="row g-2 px-2 mb-4">
            <div class="col-3">
                <div class="card bg-warning text-dark border-0 shadow-sm text-center py-2">
                    <h5 class="mb-0 fw-bold">{{ $pending }}</h5>
                    <small>Pending</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card bg-info text-dark border-0 shadow-sm text-center py-2">
                    <h5 class="mb-0 fw-bold">{{ $inProgress }}</h5>
                    <small>In Progress</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card bg-success text-white border-0 shadow-sm text-center py-2">
                    <h5 class="mb-0 fw-bold">{{ $completed }}</h5>
                    <small>Completed</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card bg-primary text-white border-0 shadow-sm text-center py-2">
                    <h5 class="mb-0 fw-bold">{{ $escalated }}</h5>
                    <small>Escalated</small>
                </div>
            </div>
        </div>

        <div class="mb-3 px-2">
            <h4 class="mb-0 fw-bold">My Assigned Jobs</h4>
        </div>

        <div class="row g-3 px-2">
            @forelse($tickets as $ticket)
                <x-ticket-card :ticket="$ticket" :showActions="true" />
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
                        <i class="bi bi-wrench fs-1"></i>
                        <p class="mt-2 mb-0">No active jobs assigned to you.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="px-2 mt-3">
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>

    <x-update-status-modal />
@endsection
