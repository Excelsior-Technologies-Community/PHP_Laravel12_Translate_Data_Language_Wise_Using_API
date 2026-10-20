@extends('layouts.studio')

@section('title', 'Translation Analytics & Quota Guard')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Translation Analytics & Quota Guard</h4>
        <p class="text-muted small mb-0">Visual performance telemetry, API quota consumption, and cache optimization metrics.</p>
    </div>
    <div>
        <span class="badge bg-white text-dark border px-3 py-2 font-mono shadow-sm">
            <i class="bi bi-clock-history text-primary me-1"></i> Telemetry Window: Last 7 Days
        </span>
    </div>
</div>

<!-- 4 Telemetry Highlight Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Cache Hit Ratio</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $cacheRatio }}%</h3>
                <small class="text-success"><i class="bi bi-lightning-charge-fill me-1"></i>Saved {{ number_format($cacheHits) }} API calls</small>
            </div>
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-4">
                <i class="bi bi-hdd-stack-fill fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Characters Processed</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalChars) }}</h3>
                <small class="text-muted">Total {{ number_format($totalWords) }} words</small>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                <i class="bi bi-fonts fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Est. Cloud Cost Saved</small>
                <h3 class="fw-bold text-success mb-0 mt-1">${{ number_format($costSaved, 3) }}</h3>
                <small class="text-muted">Saved via cache</small>
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4">
                <i class="bi bi-piggy-bank-fill fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Daily Quota Status</small>
                <h3 class="fw-bold {{ $percentUsed > 80 ? 'text-danger' : 'text-dark' }} mb-0 mt-1">{{ $percentUsed }}%</h3>
                <small class="text-muted">{{ number_format($remainingToday) }} chars remaining</small>
            </div>
            <div class="bg-info bg-opacity-10 text-info p-3 rounded-4">
                <i class="bi bi-shield-check fs-3"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quota Guard Detail Banner -->
<div class="card-custom p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-fill-check text-success fs-5"></i>
            <h6 class="fw-bold mb-0">Daily API Character Quota Monitor</h6>
        </div>
        <span class="badge bg-dark font-mono px-3 py-1">
            Resets Midnight UTC
        </span>
    </div>
    <p class="text-muted small mb-3">Enforces rate limits to avoid unexpected cloud charges or IP blocking from Google Translate endpoints.</p>
    <div class="progress mb-2" style="height: 12px;">
        <div class="progress-bar {{ $percentUsed > 80 ? 'bg-danger' : 'bg-primary' }} progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percentUsed }}%;"></div>
    </div>
    <div class="d-flex flex-wrap justify-content-between font-mono small text-muted">
        <span>Consumed Today: <strong>{{ number_format($usedToday) }} characters</strong></span>
        <span>Remaining: <strong class="text-success">{{ number_format($remainingToday) }} characters</strong></span>
        <span>Daily Hard Limit: <strong>{{ number_format($dailyLimit) }} characters</strong></span>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Daily Translation Volume -->
    <div class="col-12 col-xl-8">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up text-primary fs-5"></i>
                    <h6 class="fw-bold mb-0">Daily Translation Request Volume (Last 7 Days)</h6>
                </div>
            </div>
            <div style="height: 280px;">
                <canvas id="dailyVolumeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Approval Status Breakdown -->
    <div class="col-12 col-xl-4">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart text-success fs-5"></i>
                    <h6 class="fw-bold mb-0">Translation Status Distribution</h6>
                </div>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Language Distribution Row -->
<div class="row g-4">
    <div class="col-12">
        <div class="card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line-fill text-warning fs-5"></i>
                    <h6 class="fw-bold mb-0">Translation Density by Language Locale</h6>
                </div>
            </div>
            <div style="height: 240px;">
                <canvas id="languageChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Prepare Data for Chart 1: Daily Volume
    const dailyData = @json($dailyData);
    const dailyLabels = dailyData.length ? dailyData.map(d => d.date) : ['Today'];
    const liveCallsData = dailyData.length ? dailyData.map(d => d.live_calls) : [{{ $liveCalls }}];
    const cacheHitsData = dailyData.length ? dailyData.map(d => d.cache_hits) : [{{ $cacheHits }}];

    new Chart(document.getElementById('dailyVolumeChart'), {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Cache Hits (Saved API)',
                    data: cacheHitsData,
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: 'Live API Translations',
                    data: liveCallsData,
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Chart 2: Approval Status Donut
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending Review', 'Rejected'],
            datasets: [{
                data: [{{ $approvedCount }}, {{ $pendingCount }}, {{ $rejectedCount }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Chart 3: Language Popularity
    const languageData = @json($languages);
    const langLabels = languageData.map(l => l.locale.toUpperCase());
    const langCounts = languageData.map(l => l.total);

    new Chart(document.getElementById('languageChart'), {
        type: 'bar',
        data: {
            labels: langLabels,
            datasets: [{
                label: 'Translations Count',
                data: langCounts,
                backgroundColor: '#8b5cf6',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
