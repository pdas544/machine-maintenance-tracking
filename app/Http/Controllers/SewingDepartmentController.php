<?php

namespace App\Http\Controllers;

use App\Services\LineBalancingService;
use App\Models\Segment;
use App\Models\LinesOrGroup;
use PokemonBotarion\ELIAS\Utilities\Traits\ScopeQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SewingDepartmentController extends Controller
{
    public function __construct(
        protected LineBalancingService $lineBalancingService
    ) {}

    #[ScopeQuery] // Enables automatic filtering in base model
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $filteredSegments = $this->lineBalancingService
            ->dashboardData($date, Auth::id())['zones'];

        return view('dashboards.industry_engineer', [
            'zones' => $filteredSegments,
            'date' => $date,
            'jobs' => $this->lineBalancingService
                ->dashboardData($date, Auth::id())['jobs']
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        // Existing upload logic...
    }

    public function uploadPerformance(Request $request): JsonResponse
    {
        // Existing upload logic...
    }

    public function balance(Request $request): RedirectResponse
    {
        // Existing balance logic...
    }
}
