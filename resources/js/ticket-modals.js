import Choices from 'choices.js';

// Bootstrap modal event listeners
document.addEventListener('DOMContentLoaded', function () {
    var updateModal = document.getElementById('updateStatusModal');
    if (updateModal) {
        var form = document.getElementById('updateStatusForm');
        var statusSelect = document.getElementById('statusSelect');
        var escalationInfo = document.getElementById('escalationInfo');

        // Toggle escalation info
        statusSelect.addEventListener('change', function() {
            if (this.value === 'unfixable_escalated') {
                if (escalationInfo) escalationInfo.classList.remove('d-none');
            } else {
                if (escalationInfo) escalationInfo.classList.add('d-none');
            }
        });

        // Validation for remarks
        form.addEventListener('submit', function(e) {
            if (statusSelect.value === 'unfixable_escalated') {
                var remarks = form.querySelector('textarea[name="mechanic_remarks"]');
                if (!remarks.value.trim()) {
                    e.preventDefault();
                    alert('Mechanic remarks are required for escalation.');
                }
            }
        });

        updateModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ticketId = button.getAttribute('data-ticket-id');
            var escalationLevel = parseInt(button.getAttribute('data-escalation-level'));
            var escalateOption = statusSelect.querySelector('option[value="unfixable_escalated"]');
            var userRole = parseInt(updateModal.getAttribute('data-user-role'));

            if (form) {
                form.action = '/tickets/' + ticketId + '/status';
            }

            // Reset escalation info visibility
            if (escalationInfo) escalationInfo.classList.add('d-none');
            statusSelect.value = 'in_progress';

            // Logic: Mechanic (3), Incharge (4), Head (5), Manager (6)
            // If escalationLevel >= (userRole - 2), then disable escalation
            var maxLevel = (userRole === 3) ? 1 : (userRole - 1);

            // Ensure the escalation level does not exceed the maximum allowed for the system (level 4)
            // and apply the role-based limit.
            if (escalationLevel >= 4 || escalationLevel >= maxLevel) {
                escalateOption.disabled = true;
            } else {
                escalateOption.disabled = false;
            }
        });
    }

    var raiseTicketModal = document.getElementById('raiseTicketModal');
    if (raiseTicketModal) {
        var machineSelect = document.getElementById('machine_select');
        if (machineSelect) {
            new Choices(machineSelect, {
                searchEnabled: true,
                itemSelectText: '',
            });
        }
    }
});
