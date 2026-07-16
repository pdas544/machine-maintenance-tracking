<?php

namespace App\Services;

use App\Jobs\ImportAttendanceJob;
use App\Jobs\ImportPerformanceJob;
use App\Jobs\RunLineBalancingJob;
use App\Models\ImportJob;
use App\Models\Machine;
use App\Models\MachineAssignment;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LineBalancingService
{
    /**
     * Queue the attendance Excel import as a background job.
     */
    public function queueAttendanceImport($file, int $createdBy): ImportJob
    {
        $path = $file->store('imports');
        $job = ImportJob::create([
            'type' => 'attendance',
            'filename' => $file->getClientOriginalName(),
            'total_rows' => 0,
            'processed_rows' => 0,
            'status' => 'pending',
            'created_by' => $createdBy,
        ]);

        ImportAttendanceJob::dispatch($path, $job->id);

        return $job;
    }

    /**
     * Queue the performance Excel import as a background job.
     */
    public function queuePerformanceImport($file, int $createdBy): ImportJob
    {
        $path = $file->store('imports');
        $job = ImportJob::create([
            'type' => 'performance',
            'filename' => $file->getClientOriginalName(),
            'total_rows' => 0,
            'processed_rows' => 0,
            'status' => 'pending',
            'created_by' => $createdBy,
        ]);

        ImportPerformanceJob::dispatch($path, $job->id);

        return $job;
    }

    /**
     * Run the line-balancing algorithm for a given date.
     *
     * Flow:
     *   1. Vacant machines = machines without an assignment on $date
     *   2. Present operators = operators with attendance_status 'present' on $date
     *   3. Floaters = present operators NOT already assigned on $date
     *   4. Rank floaters by performance_percentage DESC
     *   5. Assign top N floaters to vacant machines
     */
    public function balance(string $date, ?int $ieUserId = null): array
    {
        $vacantMachines = $this->machinesWithoutAssignment($date);
        $assignedOperatorIds = MachineAssignment::where('date', $date)
            ->pluck('operator_id')
            ->all();

        $presentOperators = User::where('role_id', User::OPERATOR_ROLE_ID)
            ->where('attendance_status', 'present')
            ->whereDate('attendance_date', $date)
            ->whereNotIn('id', $assignedOperatorIds)
            ->orderByDesc('performance_percentage')
            ->get();

        $assignedCount = 0;
        $vacantMachines = $vacantMachines->values();
        $floaters = $presentOperators->values();

        $maxAssignments = min($vacantMachines->count(), $floaters->count());
        for ($i = 0; $i < $maxAssignments; $i++) {
            MachineAssignment::create([
                'machine_id' => $vacantMachines[$i]->id,
                'operator_id' => $floaters[$i]->id,
                'date' => $date,
            ]);
            $vacantMachines[$i]->update(['status' => 'occupied']);
            $assignedCount++;
        }

        return [
            'assigned' => $assignedCount,
            'floaters_remaining' => $floaters->slice($maxAssignments),
            'vacant_machines' => $vacantMachines->slice($maxAssignments),
        ];
    }

    /**
     * Machines that have no assignment on the given date.
     */
    public function machinesWithoutAssignment(string $date): Collection
    {
        return Machine::whereNotIn('id', function ($query) use ($date) {
            $query->select('machine_id')
                ->from('machine_assignments')
                ->where('date', $date);
        })->get();
    }

    /**
     * Operators marked present on the given date (unassigned floaters).
     */
    public function floaters(string $date): Collection
    {
        $assignedOperatorIds = MachineAssignment::where('date', $date)
            ->pluck('operator_id')
            ->all();

        return User::where('role_id', User::OPERATOR_ROLE_ID)
            ->where('attendance_status', 'present')
            ->whereDate('attendance_date', $date)
            ->whereNotIn('id', $assignedOperatorIds)
            ->orderByDesc('performance_percentage')
            ->get();
    }

    /**
     * Data contract for the sewing dashboard view.
     */
    public function dashboardData(string $date, int $userId): array
    {
        return [
            'zones' => Segment::with([
                'linesOrGroups.machines.machineAssignments' => fn($q) => $q->where('date', $date),
            ])->get(),
            'date' => $date,
            'jobs' => ImportJob::where('created_by', $userId)->latest()->get(),
        ];
    }
}
