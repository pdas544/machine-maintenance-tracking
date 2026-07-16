# Line-Balancing Module — Analysis, Agent Split & Estimates

## Context
`line-balancing.md` specifies a Sewing Department Line Balancing module: manage machine
allocation, operator attendance, and line balancing. This document (a) analyzes the file against
the current codebase, (b) recommends how many implementation agents to use for **minimum token
usage**, and (c) estimates total token usage and elapsed time.

Decisions confirmed with the user (both chosen for the lowest-token path):
- **Reuse existing hierarchy** `Segment → LinesOrGroup → Machine`; add only `MachineAssignment`
  + performance/attendance columns. No new Zone/Part/AssemblyLine tables.
- **Reuse `User` model** (operators = `role_id = 1`) with added metric columns. No new Operator model.

## Analysis: line-balancing.md vs. current code
The file has 3 phases. Mapping to today's repo:

| Plan item | Current state | Gap |
|---|---|---|
| `industrial_engineer` role | 6 roles, no IE; only inline `role_id` checks, no role middleware | Add role (id 7) + seed; add gate/middleware |
| Zone → Part → Assembly Line hierarchy | `Segment → LinesOrGroup → Machine` already exists | Map "Zone"→Segment, "Assembly Line"→LinesOrGroup; 15 machines per line = seed concern |
| `MachineAssignment` (machine, operator, date) | Does not exist | New migration + model |
| Operator performance metrics | `User` has no metric cols | Migration: `performance_percentage`, `attendance_status` |
| Excel upload (maatwebsite/excel) | **Not installed** | `composer require maatwebsite/excel` |
| Balancing logic (vacancies → rank → assign) | None | New `LineBalancingService` |
| Dashboard grid (green=occupied / red=vacant) | Per-role Bootstrap dashboards exist | New `dashboards/sewing` blade |
| `SewingDepartmentController` | Only `TicketController` | New controller + routes |

**Net:** mostly new scaffolding on top of existing models — not heavy refactor.

## 3. Logic Architecture: Floater Selection & Performance Ranking

### Floater Definition
- **Floaters** = operators with `status: present` in attendance sheet who are **not yet assigned** to any machine
- Selection logic: `attendance.status = 'present'` AND NOT IN (today's `machine_assignments.operator_id`)

### Performance Percentage Source
- **Primary sheet**: `attendance.xlsx` → `operator_id`, `status` (present/absent)
- **Ranking sheet**: `performance.xlsx` → `operator_id`, `performance_percentage`
- Both sheets processed via queued jobs; floater selection occurs **after** both imports complete

### Service Layer Logic (Recommended: LineBalancingService)
The balancing algorithm resides entirely in `LineBalancingService` - **not the controller**:

```php
// LineBalancingService.php
public function balance(string $date, ?int $ieUserId = null): array
{
    // 1. Get present operators from attendance import
    // 2. Get performance scores for ranking
    // 3. Identify floaters (present + unassigned)
    // 4. Rank floaters by performance_percentage DESC
    // 5. Assign top performers to vacant machines
    
    return [
        'assigned' => int,
        'floaters_remaining' => Collection,
        'vacant_machines' => Collection,
    ];
}
```

**Rationale for Service Layer:**
- Controllers orchestrate requests, Services hold business logic
- Service logic is testable in isolation
- Aligns with existing `TicketController` pattern (thin controller, logic elsewhere)
- Allows queuing: `RunLineBalancingJob` wraps this service method

### Controller Endpoints (Thin Delegation)
```php
// SewingDepartmentController.php
public function upload(Request $request): JsonResponse
{
    $jobId = $this->lineBalancingService->queueAttendanceImport($request->file('attendance'));
    return response()->json(['job_id' => $jobId, 'type' => 'attendance']);
}

public function uploadPerformance(Request $request): JsonResponse
{
    $jobId = $this->lineBalancingService->queuePerformanceImport($request->file('performance'));
    return response()->json(['job_id' => $jobId, 'type' => 'performance']);
}

public function balance(Request $request): RedirectResponse
{
    $this->lineBalancingService->balance($request->date, Auth::id());
    return redirect()->route('sewing.index')->with('success', 'Line balanced successfully');
}
```

## 4. Phase Structure Update

### Phase 1: Setup & Access Control (unchanged)
- Define `industrial_engineer` role
- Create route group for `/sewing/*`

### Phase 2: Data Ingestion — Dual Excel Sheets
- **Attendance upload**: operator_id + present/absent status
- **Performance upload**: operator_id + performance_percentage (for ranking)
- Both via queued imports with progress tracking
- **Floater identification**: occurs in `balance()` after both sheets imported

### Phase 3: Balancing Logic (entirely in Service)
```
balance(date):
├── vacantMachines = machinesWithoutAssignment(date)
├── presentOperators = operatorsWithAttendanceStatus('present', date)
├── floaters = presentOperators NOT IN assignedOperatorIds(date)
├── rankedFloaters = floaters sortByDesc('performance_percentage')
└── assign top N floaters to vacant machines (creates MachineAssignment rows)
```

## 5. Recommended Agent Split (Updated)

| Agent A — Backend/Service | Agent B — Frontend/UI |
|---|---|
| Migrations: MachineAssignment, import_jobs | dashboards/sewing.blade.php grid |
| Models: MachineAssignment with relationships | Upload forms (attendance + performance tabs) |
| **LineBalancingService** (core logic) | Progress indicator for both uploads |
| Jobs: ImportAttendanceJob, ImportPerformanceJob, RunLineBalancingJob | Ajax polling for job status |
| Controller methods (thin delegation) | Balance button + result display |
| IE role seed + middleware | Conditional display based on job status |

**Overlap**: Both agents use shared data contract for:
- `machine.status`: `occupied` (has assignment) vs `vacant` (no assignment)
- `operator.status`: `present`/`absent` from attendance
- `operator.performance_percentage` for ranking display
- `job.status`: `pending`/`processing`/`completed`/`failed`

## 6. Database Contract

```sql
-- machine_assignments
id, machine_id FK, operator_id FK, date, created_at

-- users (additional columns)
performance_percentage DECIMAL(5,2), -- for ranking
attendance_status ENUM('present','absent'), -- set by import
attendance_date DATE -- for quick querying

-- import_jobs (new table)
id, type (attendance|performance), filename, total_rows, processed_rows, status, created_by, created_at
```

## 7. Token Usage Estimate

| Phase | Tokens |
|---|---|
| Agent A (backend + queue + service logic) | ~70k |
| Agent B (frontend + dual upload + progress) | ~35k |
| Main-agent synthesis | ~20k |
| **Total** | **~125k** |

Note: Service-layer logic increases Agent A tokens slightly but reduces overall complexity by keeping business rules centralized.

## 8. Large File Handling (~10,000 rows, ~300 MB)

### Memory Optimization
- **Chunked processing**: Use maatwebsite/excel's `chunkOn(500)` to process 500 rows at a time
- **Queue driver**: Required for large files (database or redis); web process would timeout
- **PHP limits** (add to `.env` or php.ini):
  ```
  UPLOAD_MAX_FILESIZE=500M
  POST_MAX_SIZE=500M
  MEMORY_LIMIT=1G
  ```

### Alternative Approaches for 300 MB Files
| Option | Pros | Cons |
|---|---|---|
| **Split files** | Simple to implement; smaller chunks | Manual step; user friction |
| **CLI import** | No memory limits; reliable | Requires server access; not web-friendly |
| **Temporary storage + queue** | Best UX; scalable | More infrastructure (queue workers) |

**Recommended**: Queue-based chunked import (default `composer dev` already includes `queue:listen`).

## Agent count: **2 agents** (token-optimal)

Rationale for minimum tokens:
- The biggest token cost is **context re-reading**. The main agent explored inline (~7k tokens) instead of spawning 3 Explore agents (which failed on a credit limit and would have tripled exploration cost).
- A **shared context brief** (this analysis + the data contract) is embedded into the task prompts so neither implementation agent re-explores from scratch — the single biggest token saver.
- Frontend depends on backend's routes/data shape, so they are **not fully parallel**; the contract lets UI start against an agreed shape.

**Split:**
- **Agent A — Backend/Service**: migrations, `MachineAssignment` model, `LineBalancingService` (Excel parse + balance algo), jobs, `SewingDepartmentController`, routes, IE role seed, `composer require maatwebsite/excel`.
- **Agent B — Frontend/UI**: `dashboards/sewing.blade.php` grid (green/red), upload forms (attendance + performance tabs), progress indicators, balance button, IE auth gating. Built against the contract; wired after A.

*Absolute token-minimum alternative:* a **single agent** doing A then B sequentially saves ~1 agent's worth of context but roughly doubles wall-clock time. Default recommendation is **2**; choose 1 only if time is not a concern.

## Data Contract (for both agents)

`SewingDepartmentController@index` returns:
```php
[
    'zones' => Segment::with(['linesOrGroups.machines.machineAssignments' => fn($q) => $q->where('date', $date)])->get(),
    'date' => $date,
    'jobs' => ImportJob::where('created_by', Auth::id())->latest()->get()
]
```

Each machine exposes `->machineAssignments->first()` → occupied if present.

## Time estimate (elapsed, excluding planning)
- Agent A (backend + Excel install + seed): **~25–35 min**
- Agent B (frontend, against contract): **~15–25 min**
- Overlap on contract: **~5 min**
- Verification (`migrate:fresh --seed`, manual grid check): **~10–15 min**
- **Total wall-clock: ~45–75 min** (single-agent path would be ~70–110 min).

## Verification
1. `composer require maatwebsite/excel` then `php artisan migrate:fresh --seed`.
2. Log in as an `industrial_engineer` user → `/sewing` shows 5 zones, lines, 15 machines/line, grid green/red.
3. Upload attendance xlsx → operators marked present/absent.
4. Upload performance xlsx → operators get `performance_percentage` values.
5. Click "Run balancing" → vacant machines get top-performing present, unassigned operators (floaters) assigned (`MachineAssignment` rows created).
6. `composer test` stays green.

### Large File Test (Optional)
- Test with 10k-row attendance sheet → verify chunked queue processing completes
- Check `import_jobs` table shows progress updates
- Confirm memory usage stays under configured limits