<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true" data-user-role="{{ Auth::user()->role_id }}">
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
                        <select name="status" class="form-select form-select-lg" required id="statusSelect">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed (Fixed)</option>
                            <option value="unfixable_escalated">Unfixable (Escalate)</option>
                        </select>
                    </div>

                    @if(Auth::user()->reportingOfficer)
                        <div id="escalationInfo" class="mb-3 d-none">
                            <label class="form-label">Immediate Reporting Officer</label>
                            <input type="text" class="form-control" disabled value="{{ Auth::user()->reportingOfficer->name }}">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="mechanic_remarks" class="form-control" rows="2" placeholder="Required if escalating..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Save Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
