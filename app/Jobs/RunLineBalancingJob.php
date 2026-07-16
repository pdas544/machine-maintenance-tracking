<?php

namespace App\Jobs;

use App\Services\LineBalancingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunLineBalancingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $date,
        public ?int $ieUserId = null
    ) {}

    public function handle(LineBalancingService $service): void
    {
        $service->balance($this->date, $this->ieUserId);
    }
}
