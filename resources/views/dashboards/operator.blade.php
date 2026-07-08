@extends('layouts.app')

@section('content')
    <div class="container-fluid px-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h4 class="mb-0 fw-bold">My Reported Issues</h4>
            <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#raiseTicketModal">
                <i class="bi bi-plus-lg"></i> Raise Ticket
            </button>
        </div>

        <div class="row g-3 px-2">
            @forelse($tickets as $ticket)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">Request #{{ $ticket->id }}</h6>
                                <span class="badge
                                {{ $ticket->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                {{ $ticket->status == 'in_progress' ? 'bg-info text-dark' : '' }}
                                {{ $ticket->status == 'completed' ? 'bg-success' : '' }}
                                {{ $ticket->status == 'unfixable_escalated' ? 'bg-danger' : '' }}
                                rounded-pill">
                                {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            </div>
                            <p class="text-muted small mb-2"><i class="bi bi-upc-scan"></i> Machine ID: <strong>{{ $ticket->machine_id }}</strong></p>
                            <p class="mb-2 text-truncate">{{ $ticket->issue_description }}</p>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                <i class="bi bi-clock"></i> Raised: {{ $ticket->raised_at }}
                            </div>
                            @if($ticket->acknowledged_at)
                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                    <i class="bi bi-person-check"></i> Acknowledged by: {{ $ticket->mechanic->name ?? 'Mechanic' }} at {{ $ticket->acknowledged_at }}
                                </div>
                            @endif
                            @if($ticket->resolved_at)
                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                    <i class="bi bi-check2-circle"></i> Closed at: {{ $ticket->resolved_at }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
                        <i class="bi bi-clipboard-check fs-1"></i>
                        <p class="mt-2 mb-0">You have no active tickets.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="raiseTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Report Breakdown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tickets.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Machine ID</label>
                            <input type="number" name="machine_id" class="form-control form-control-lg" min="1" max="135" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Issue Description</label>
                            <textarea name="issue_description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Submit Ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
