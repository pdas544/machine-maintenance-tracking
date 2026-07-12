@props(['machines' => collect()])

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
                        <label class="form-label">Machine</label>
                        <select name="machine_id" id="machine_select" class="form-select form-select-lg" required>
                            <option value="">Search for a machine...</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->machine_code }}</option>
                            @endforeach
                        </select>
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
