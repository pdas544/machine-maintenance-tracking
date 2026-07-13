@extends('layouts.app')

@section('content')
    <div class="container-fluid px-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(auth()->user()->role_id == 3)
            @php
                $pending = $tickets->where('status', 'pending')->count();
                $inProgress = $tickets->where('status', 'in_progress')->count();
                $completed = $tickets->where('status', 'completed')->count();
            @endphp
            <div class="row g-2 px-2 mb-4">
                <div class="col-4">
                    <div class="card bg-warning text-dark border-0 shadow-sm text-center py-2">
                        <h5 class="mb-0 fw-bold">{{ $pending }}</h5>
                        <small>Pending</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-info text-dark border-0 shadow-sm text-center py-2">
                        <h5 class="mb-0 fw-bold">{{ $inProgress }}</h5>
                        <small>In Progress</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-success text-white border-0 shadow-sm text-center py-2">
                        <h5 class="mb-0 fw-bold">{{ $completed }}</h5>
                        <small>Completed</small>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h4 class="mb-0 fw-bold">Active Jobs</h4>

            @if(in_array(auth()->user()->role_id, [1, 2]))
                <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#raiseTicketModal">
                    <i class="bi bi-plus-lg"></i> Raise Ticket
                </button>
            @endif
        </div>

        <div class="row g-3 px-2">
            @forelse($tickets as $ticket)
                <x-ticket-card
                    :ticket="$ticket"
                    :showActions="auth()->user()->role_id == 3"
                />
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
                        <i class="bi bi-clipboard-check fs-1"></i>
                        <p class="mt-2 mb-0">No active tickets found.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="px-2 mt-3">
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>

    @if(in_array(auth()->user()->role_id, [1, 2]))
        <x-raise-ticket-modal :machines="$machines" />
    @endif

    @if(auth()->user()->role_id == 3)
        <x-update-status-modal />
    @endif
@endsection