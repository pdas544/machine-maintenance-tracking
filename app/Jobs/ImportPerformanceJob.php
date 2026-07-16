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

class ImportPerformanceJob implements ShouldQueue
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
            // Expected columns: operator_id, performance_percentage
            if (!isset($row[0])) {
                continue;
            }
            $operatorId = $row[0];
            $percentage = is_numeric($row[1] ?? null) ? (float) $row[1] : null;

            User::where('id', $operatorId)->update([
                'performance_percentage' => $percentage,
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
