@extends('layouts.app')

@section('content')
    <div class="container-fluid px-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center px-2 mb-3 gap-2">
            <h4 class="mb-0 fw-bold">Sewing Department — Line Balancing</h4>
            <form method="GET" class="d-flex align-items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </form>
        </div>

        {{-- Upload forms (attendance + performance tabs) --}}
        <div class="px-2 mb-3">
            <ul class="nav nav-tabs" id="uploadTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab"
                            data-bs-target="#attendance" type="button" role="tab">Attendance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="performance-tab" data-bs-toggle="tab"
                            data-bs-target="#performance" type="button" role="tab">Performance</button>
                </li>
            </ul>
            <div class="tab-content border border-top-0 rounded-bottom p-3 bg-white">
                <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                    <form id="attendanceForm" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="attendance" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button class="btn btn-primary" type="submit">Upload</button>
                        </div>
                        <small class="text-muted">Columns: operator_id, status (present|absent)</small>
                    </form>
                </div>
                <div class="tab-pane fade" id="performance" role="tabpanel">
                    <form id="performanceForm" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="performance" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button class="btn btn-primary" type="submit">Upload</button>
                        </div>
                        <small class="text-muted">Columns: operator_id, performance_percentage</small>
                    </form>
                </div>
            </div>
        </div>

        {{-- Progress indicators for import jobs --}}
        <div class="px-2 mb-3">
            <h6 class="fw-bold">Import Jobs</h6>
            @forelse($jobs as $job)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small">
                        <span>{{ ucfirst($job->type) }} — {{ $job->filename }}</span>
                        <span class="badge bg-{{ $job->status === 'completed' ? 'success' : ($job->status === 'failed' ? 'danger' : 'warning') }}">{{ $job->status }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $job->total_rows ? ($job->processed_rows / $job->total_rows * 100) : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">No imports yet.</p>
            @endforelse
        </div>

        {{-- Balance button --}}
        <div class="px-2 mb-3">
            <form method="POST" action="{{ route('sewing.balance') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Run balancing
                </button>
            </form>
        </div>

        {{-- Green/red grid --}}
        @foreach($zones as $zone)
            <div class="px-2 mb-4">
                <h5 class="fw-bold text-uppercase">{{ $zone->name }}</h5>
                @foreach($zone->linesOrGroups as $line)
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">{{ $line->name }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($line->machines as $machine)
                                @php
                                    $assignment = $machine->machineAssignments->first();
                                    $occupied = !is_null($assignment);
                                @endphp
                                <div class="border rounded text-center p-2 shadow-sm"
                                     style="width: 90px; background-color: {{ $occupied ? '#d1e7dd' : '#f8d7da' }}; border-color: {{ $occupied ? '#198754' : '#dc3545' }} !important;">
                                    <div class="small fw-bold">{{ $machine->machine_code }}</div>
                                    @if($occupied)
                                        <div class="small text-success">Occ. #{{ $assignment->operator_id }}</div>
                                    @else
                                        <div class="small text-danger">Vacant</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

    </div>

    @push('scripts')
        <script>
            function uploadFile(formId, url) {
                const form = document.getElementById(formId);
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const data = new FormData(form);
                    fetch(url, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(() => location.reload());
                });
            }
            uploadFile('attendanceForm', '{{ route('sewing.upload') }}');
            uploadFile('performanceForm', '{{ route('sewing.upload.performance') }}');
        </script>
    @endpush
@endsection
