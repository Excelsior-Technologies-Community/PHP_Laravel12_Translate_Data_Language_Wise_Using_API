@extends('layouts.studio')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Localization Dashboard</h4>
        <p class="text-muted small mb-0">Overview of multi-language translation jobs, approval queues, and AI quota consumption.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('studio.posts.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-file-earmark-plus"></i> Create & Translate Post
        </a>
    </div>
</div>

<!-- 4 Key Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Original Posts</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalPosts) }}</h3>
                <small class="text-success"><i class="bi bi-arrow-up-right me-1"></i>Master Content</small>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                <i class="bi bi-file-text-fill fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Translations</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalTranslations) }}</h3>
                <small class="text-muted">{{ count($supportedLanguages) }} active locales</small>
            </div>
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-4">
                <i class="bi bi-globe2 fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Pending Approvals</small>
                <h3 class="fw-bold {{ $pendingApprovals > 0 ? 'text-warning' : 'text-dark' }} mb-0 mt-1">{{ number_format($pendingApprovals) }}</h3>
                <small class="text-muted">Requires review</small>
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4">
                <i class="bi bi-hourglass-split fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Brand Lock Terms</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($activeGlossaryCount) }}</h3>
                <small class="text-info"><i class="bi bi-shield-lock me-1"></i>Protected Terms</small>
            </div>
            <div class="bg-info bg-opacity-10 text-info p-3 rounded-4">
                <i class="bi bi-bookmark-star-fill fs-3"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quota & Cache Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-speedometer2 text-primary fs-5"></i>
                    <h6 class="fw-bold mb-0">Daily API Quota Guard</h6>
                </div>
                <span class="badge bg-{{ $quotaPercent > 80 ? 'danger' : 'success' }} bg-opacity-10 text-{{ $quotaPercent > 80 ? 'danger' : 'success' }} border border-{{ $quotaPercent > 80 ? 'danger' : 'success' }} border-opacity-25 px-2 py-1">
                    {{ $quotaPercent }}% Consumed
                </span>
            </div>
            <p class="text-muted small mb-3">Protects against excessive API costs and provider rate-limiting by strictly tracking daily character counts.</p>
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar {{ $quotaPercent > 80 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $quotaPercent }}%;"></div>
            </div>
            <div class="d-flex justify-content-between font-mono small text-muted">
                <span>Used Today: <strong>{{ number_format($todayUsed) }} chars</strong></span>
                <span>Daily Limit: <strong>{{ number_format($dailyLimit) }} chars</strong></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card-custom p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                    <h6 class="fw-bold mb-0">Cache Performance & Cost Savings</h6>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                    {{ $cacheRatio }}% Cache Hit Ratio
                </span>
            </div>
            <p class="text-muted small mb-3">Identical phrases and previously translated contents are automatically served from cache, accelerating responses and reducing API bills.</p>
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $cacheRatio }}%;"></div>
            </div>
            <div class="d-flex justify-content-between font-mono small text-muted">
                <span>Cache Optimization: <strong>{{ $cacheRatio }}%</strong></span>
                <span>Status: <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Active (30-day TTL)</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Two Column Layout: Recent Posts & Pending Approvals -->
<div class="row g-4">
    <!-- Left Column: Recent Posts -->
    <div class="col-12 col-xl-7">
        <div class="card-custom">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-richtext text-primary fs-5"></i>
                    <h6 class="fw-bold mb-0">Recent Master Content & Posts</h6>
                </div>
                <a href="{{ route('studio.posts.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>

            @if($recentPosts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-plus text-muted fs-1 mb-2 d-block"></i>
                    <p class="text-muted small">No posts created yet.</p>
                    <a href="{{ route('studio.posts.create') }}" class="btn btn-sm btn-primary">Create Your First Post</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Post Title</th>
                                <th>Translations</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPosts as $post)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                            {{ $post->title }}
                                        </div>
                                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($supportedLanguages as $code => $name)
                                                @php
                                                    $tr = $post->postTranslations ? $post->postTranslations->firstWhere('locale', $code) : null;
                                                @endphp
                                                @if($tr)
                                                    <span class="badge {{ $tr->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }} badge-lang" title="{{ $name }} ({{ ucfirst($tr->status) }})">
                                                        {{ strtoupper($code) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border badge-lang" title="{{ $name }} (Missing)">
                                                        {{ strtoupper($code) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $post->translation_status === 'translating' ? 'bg-info' : ($post->translation_status === 'failed' ? 'bg-danger' : 'bg-success') }} font-mono small">
                                            {{ strtoupper($post->translation_status ?? 'completed') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('studio.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-primary" title="Open Multi-tab Studio">
                                            <i class="bi bi-pencil-square"></i> Studio
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Pending Approvals Quick Review -->
    <div class="col-12 col-xl-5">
        <div class="card-custom">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check2-circle text-warning fs-5"></i>
                    <h6 class="fw-bold mb-0">Pending Human Approvals</h6>
                </div>
                <a href="{{ route('studio.approvals') }}" class="btn btn-sm btn-outline-secondary">Review Queue</a>
            </div>

            @if($pendingReviews->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success fs-1 mb-2 d-block"></i>
                    <h6 class="fw-bold text-dark">All caught up!</h6>
                    <p class="text-muted small">No pending translations waiting for review.</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($pendingReviews as $rev)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary badge-lang">{{ strtoupper($rev->locale) }} ({{ $supportedLanguages[$rev->locale] ?? $rev->locale }})</span>
                                <small class="text-muted">{{ $rev->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="fw-semibold text-dark small text-truncate mb-1">
                                {{ $rev->title }}
                            </div>
                            <p class="text-muted small text-truncate mb-2" style="max-width: 320px;">
                                {{ $rev->content }}
                            </p>
                            <div class="d-flex gap-2">
                                <form action="{{ route('studio.approvals.approve', $rev->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2 fw-semibold">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('studio.approvals.reject', $rev->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </form>
                                <a href="{{ route('studio.posts.edit', ['id' => $rev->post_id, 'tab' => $rev->locale]) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
