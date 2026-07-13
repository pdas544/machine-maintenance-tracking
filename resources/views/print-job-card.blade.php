<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Card #{{ $ticket->id }}</title>
    @vite(['resources/css/app.css'])
    <style>
    @media print {
        .no-print { display: none; }
        body { font-size: 14pt; }
    }
    </style>
</head>
<body class="bg-white p-4">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h3 class="mb-0 fw-bold">Maintenance Job Card</h3>
        <button class="btn btn-dark no-print" onclick="window.print()">Print Now</button>
    </div>

    <div class="alert alert-info no-print mb-4">
        Review the job card below, then click <strong>Print Now</strong> to generate a physical copy.
    </div>

    <table class="table table-bordered border-dark">
        <tbody>
            <tr>
                <th class="w-25 bg-light">Sl. No (Job ID)</th>
                <td>{{ $ticket->id }}</td>
            </tr>
            <tr>
                <th class="bg-light">Machine ID</th>
                <td>{{ $ticket->machine->machine_code ?? $ticket->machine_id }}</td>
            </tr>
            <tr>
                <th class="bg-light">Part/Line Number</th>
                <td>{{ $ticket->machine->linesOrGroup->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Raised By</th>
                <td>{{ $ticket->raiser->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Raised On</th>
                <td>{{ $ticket->raised_at }}</td>
            </tr>
            <tr>
                <th class="bg-light">Current Status</th>
                <td class="fw-bold text-uppercase">{{ str_replace('_', ' ', $ticket->status) }}</td>
            </tr>
            <tr>
                <th class="bg-light">Issue Description</th>
                <td>{{ $ticket->issue_description }}</td>
            </tr>
            @if($ticket->escalation_level > 0 && Auth::user()->role_id >= \App\Enums\Role::FloorIncharge->value)
            <tr>
                <th class="bg-light">Escalated From</th>
                <td>{{ $ticket->mechanic->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Escalated On</th>
                <td>{{ $ticket->escalated_at ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Reason</th>
                <td>{{ $ticket->escalation_reason ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Mechanic Remarks</th>
                <td>{{ $ticket->mechanic_remarks ?? 'N/A' }}</td>
            </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
