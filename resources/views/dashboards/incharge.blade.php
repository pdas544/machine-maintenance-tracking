@extends('layouts.app')

@section('content')
    <div class="container-fluid px-0">
        <h4 class="mb-3 fw-bold px-2">Incharge Dashboard - SLA Alerts</h4>

        @if($relevantNotifications->count() > 0)
            <div class="mb-4 px-2">
                <h5 class="fw-bold mb-2 text-danger">Recent Notifications</h5>
                <div class="list-group shadow-sm">
                    @foreach($relevantNotifications as $notification)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $notification->data['message'] }}
                                <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            @if(!$notification->read_at)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Close</button>
                            </form>
                            @else
                                <span class="badge bg-secondary">Read</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <h4 class="mb-3 fw-bold px-2">Active Tickets</h4>
        <div class="row g-3 px-2">
            @forelse($tickets as $ticket)
                <x-ticket-card
                    :ticket="$ticket"
                    :showActions="true"
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
    </div>

    <x-update-status-modal />
@endsection
