@extends('layouts.studio')

@section('title', 'Translation Approvals & Proofreading')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Translation Approvals & Proofreading</h4>
        <p class="text-muted small mb-0">Review AI machine translations against the original master content before publishing.</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="card-custom p-3 mb-4">
    <form action="{{ route('studio.approvals') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Status Tabs -->
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('studio.approvals', ['status' => 'pending', 'locale' => request('locale')]) }}"
               class="btn btn-sm {{ (request('status', 'pending') === 'pending') ? 'btn-warning text-dark fw-bold' : 'btn-light border' }}">
               <i class="bi bi-hourglass-split me-1"></i> Pending Review ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('studio.approvals', ['status' => 'approved', 'locale' => request('locale')]) }}"
               class="btn btn-sm {{ (request('status') === 'approved') ? 'btn-success text-white fw-bold' : 'btn-light border' }}">
               <i class="bi bi-check2-circle me-1"></i> Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('studio.approvals', ['status' => 'rejected', 'locale' => request('locale')]) }}"
               class="btn btn-sm {{ (request('status') === 'rejected') ? 'btn-danger text-white fw-bold' : 'btn-light border' }}">
               <i class="bi bi-x-circle me-1"></i> Rejected ({{ $counts['rejected'] }})
            </a>
            <a href="{{ route('studio.approvals', ['status' => 'all', 'locale' => request('locale')]) }}"
               class="btn btn-sm {{ (request('status') === 'all') ? 'btn-dark text-white fw-bold' : 'btn-light border' }}">
               All Locales
            </a>
        </div>

        <!-- Language Dropdown Filter -->
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted fw-semibold">Language:</label>
            <select name="locale" class="form-select form-select-sm font-mono" onchange="this.form.submit()">
                <option value="all">All Languages</option>
                @foreach($supportedLanguages as $code => $name)
                    @if($code !== 'en')
                        <option value="{{ $code }}" {{ request('locale') === $code ? 'selected' : '' }}>
                            {{ $name }} ({{ strtoupper($code) }})
                        </option>
                    @endif
                @endforeach
            </select>
            <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
        </div>
    </form>
</div>

<!-- Approvals Review Cards List -->
@if($translations->isEmpty())
    <div class="card-custom text-center py-5">
        <i class="bi bi-clipboard-check text-success fs-1 mb-2 d-block"></i>
        <h5 class="fw-bold text-dark">No translations matching your filter</h5>
        <p class="text-muted small">All translations in this queue are processed or no records match the criteria.</p>
        <a href="{{ route('studio.approvals', ['status' => 'all']) }}" class="btn btn-light border btn-sm">View All Translations</a>
    </div>
@else
    <div class="d-flex flex-column gap-3 mb-4">
        @foreach($translations as $tr)
            <div class="card-custom p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary badge-lang px-3 py-2 fs-6">
                            {{ strtoupper($tr->locale) }} &bull; {{ $supportedLanguages[$tr->locale] ?? $tr->locale }}
                        </span>
                        <span class="badge {{ $tr->status === 'approved' ? 'bg-success' : ($tr->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} font-mono">
                            {{ strtoupper($tr->status) }}
                        </span>
                        <span class="text-muted small">Post #{{ $tr->post_id }} &bull; {{ $tr->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if($tr->status !== 'approved')
                            <form action="{{ route('studio.approvals.approve', $tr->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm fw-semibold d-flex align-items-center gap-1 shadow-sm">
                                    <i class="bi bi-check-lg"></i> Approve Translation
                                </button>
                            </form>
                        @endif

                        @if($tr->status !== 'rejected')
                            <form action="{{ route('studio.approvals.reject', $tr->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('studio.posts.edit', ['id' => $tr->post_id, 'tab' => $tr->locale]) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-pencil-square"></i> Edit in Studio
                        </a>
                    </div>
                </div>

                <!-- Side-by-Side Comparison Container -->
                <div class="row g-3">
                    <!-- Master Source English -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <small class="text-muted fw-bold font-mono d-block mb-1">
                                <i class="bi bi-file-text text-primary me-1"></i> MASTER SOURCE (ENGLISH)
                            </small>
                            <h6 class="fw-bold text-dark mb-2">{{ $tr->post->title ?? '(Deleted Post)' }}</h6>
                            <p class="text-secondary small mb-0 lh-base" style="white-space: pre-wrap;">{{ $tr->post->content ?? '' }}</p>
                        </div>
                    </div>

                    <!-- AI Translated Content -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded-3 h-100 border border-primary border-opacity-25 shadow-sm">
                            <small class="text-primary fw-bold font-mono d-block mb-1">
                                <i class="bi bi-robot text-primary me-1"></i> AI TRANSLATION ({{ strtoupper($tr->locale) }})
                            </small>
                            <h6 class="fw-bold text-dark mb-2">{{ $tr->title }}</h6>
                            <p class="text-dark small mb-0 lh-base" style="white-space: pre-wrap;">{{ $tr->content }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-end">
        {{ $translations->links() }}
    </div>
@endif
@endsection
