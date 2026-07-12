@php use App\Enums\Role; @endphp
@props(['ticket', 'showActions' => false])

<div class="col-12 col-md-6 col-lg-4">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0">{{ $showActions ? 'Job' : 'Request' }} #{{ $ticket->id }}</h6>
                <span class="badge
                {{ $ticket->status == 'pending' ? 'bg-warning text-dark' : '' }}
                {{ $ticket->status == 'in_progress' ? 'bg-info text-dark' : '' }}
                {{ $ticket->status == 'completed' ? 'bg-success' : '' }}
                {{ $ticket->status == 'unfixable_escalated' ? 'bg-danger' : '' }}
                rounded-pill">
                {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
            </span>
            </div>
            <p class="text-muted small mb-2"><i class="bi bi-upc-scan"></i> Machine ID:
                <strong>{{ $ticket->machine_id }}</strong></p>
            <p class="mb-2 text-truncate">{{ $ticket->issue_description }}</p>
            <div class="text-muted" style="font-size: 0.8rem;">
                <i class="bi bi-clock"></i> Raised: {{ $ticket->raised_at }}
            </div>
            @if($ticket->acknowledged_at)
                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                    <i class="bi bi-person-check"></i> Acknowledged by: {{ $ticket->mechanic->name ?? 'Mechanic' }}
                    at {{ $ticket->acknowledged_at }}
                </div>
            @endif
            @if($ticket->latestEscalation)

                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                    <i class="bi bi-person-exclamation"></i> Escalated
                    To: {{ Role::tryFrom(Role::getTargetRoleForEscalation($ticket->escalation_level))?->name ?? 'Higher Authority' }}
                    at {{ $ticket->latestEscalation->created_at }}
                </div>

            @endif
            @if($ticket->resolved_at)
                <div class="text-muted mt-1" style="font-size: 0.8rem;">
                    <i class="bi bi-check2-circle"></i> Closed at: {{ $ticket->resolved_at }}
                </div>
            @endif

            @if($showActions && $ticket->status !== 'completed')
                <div class="d-flex gap-2 mt-3">
                    @can('update', $ticket)
                        <button class="btn btn-sm btn-outline-primary w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#updateStatusModal"
                                data-ticket-id="{{ $ticket->id }}"
                                data-escalation-level="{{ $ticket->escalation_level }}">
                            <i class="bi bi-tools"></i> Update Status
                        </button>
                        <a href="{{ route('tickets.print', $ticket->id) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-printer"></i> Print Job Card
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
