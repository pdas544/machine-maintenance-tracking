<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Reader;

class ImportAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $path,
        public int $jobId
    ) {}

    public function handle(): void
    {
        $job = ImportJob::find($this->jobId);
        if ($job) {
            $job->update(['status' => 'processing']);
        }

        $rows = Excel::toArray(new \stdClass(), storage_path('app/' . $this->path))[0] ?? [];
        $total = count($rows);
        $processed = 0;

        foreach ($rows as $row) {
            // Expected columns: operator_id, status (present|absent)
            if (!isset($row[0])) {
                continue;
            }
            $operatorId = $row[0];
            $status = strtolower(trim((string) ($row[1] ?? 'absent')));
            $status = in_array($status, ['present', 'absent']) ? $status : 'absent';

            User::where('id', $operatorId)->update([
                'attendance_status' => $status,
                'attendance_date' => now()->toDateString(),
            ]);

            $processed++;
            if ($job) {
                $job->update(['total_rows' => $total, 'processed_rows' => $processed]);
            }
        }

        if ($job) {
            $job->update(['status' => 'completed']);
        }
    }
}
