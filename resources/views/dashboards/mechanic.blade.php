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

        <div class="mb-3 px-2">
            <h4 class="mb-0 fw-bold">My Assigned Jobs</h4>
        </div>

        <div class="row g-3 px-2">
            @forelse($tickets as $ticket)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">Job #{{ $ticket->id }}</h6>
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
                                    <i class="bi bi-person-check"></i> Acknowledged at {{ $ticket->acknowledged_at }}
                                </div>
                            @endif
                            @if($ticket->resolved_at)
                                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                    <i class="bi bi-check2-circle"></i> Closed at: {{ $ticket->resolved_at }}
                                </div>
                            @endif

                            @if(!in_array($ticket->status, ['completed', 'unfixable_escalated']))
                                <div class="d-flex gap-2 mt-3">
                                    @if(!in_array($ticket->status, ['completed', 'unfixable_escalated']))
                                        <button class="btn btn-sm btn-outline-primary w-100"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal"
                                                data-ticket-id="{{ $ticket->id }}">
                                            <i class="bi bi-tools"></i> Update Status
                                        </button>
                                    @endif
                                    <a href="{{ route('tickets.print', $ticket->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="bi bi-printer"></i> Print Job Card
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
                        <i class="bi bi-wrench fs-1"></i>
                        <p class="mt-2 mb-0">No active jobs assigned to you.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Job Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select form-select-lg" required>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed (Fixed)</option>
                                <option value="unfixable_escalated">Unfixable (Escalate)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mechanic Remarks</label>
                            <textarea name="mechanic_remarks" class="form-control" rows="2" placeholder="Required if escalating..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Save Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var updateModal = document.getElementById('updateStatusModal');
            if (updateModal) {
                updateModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var ticketId = button.getAttribute('data-ticket-id');
                    var form = document.getElementById('updateStatusForm');
                    form.action = '/tickets/' + ticketId + '/status';
                });
            }
        });
    </script>
@endsection
