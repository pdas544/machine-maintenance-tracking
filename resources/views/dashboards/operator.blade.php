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
                <x-ticket-card :ticket="$ticket" />
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5 bg-white rounded shadow-sm">
                        <i class="bi bi-clipboard-check fs-1"></i>
                        <p class="mt-2 mb-0">You have no active tickets.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="px-2 mt-3">
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>

    <x-raise-ticket-modal :machines="$machines" />
@endsection