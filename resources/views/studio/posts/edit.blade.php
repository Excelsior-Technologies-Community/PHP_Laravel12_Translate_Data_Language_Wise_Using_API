@extends('layouts.studio')

@section('title', 'Multi-Tab Studio - ' . $post->title)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('studio.posts.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left"></i> Posts
            </a>
            <span class="text-muted small">/</span>
            <span class="badge bg-dark font-mono">Post #{{ $post->id }}</span>
            <span class="badge {{ $post->translation_status === 'translating' ? 'bg-info' : ($post->translation_status === 'failed' ? 'bg-danger' : 'bg-success') }} font-mono">
                {{ strtoupper($post->translation_status ?? 'completed') }}
            </span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $post->title }}</h4>
    </div>

    <div class="d-flex align-items-center gap-2">
        <form action="{{ route('studio.posts.translate', $post->id) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="locale" value="all">
            <button type="submit" class="btn btn-outline-primary d-flex align-items-center gap-1 shadow-sm" onclick="return confirm('Re-translate all languages via AI using current Glossary rules?');">
                <i class="bi bi-arrow-repeat"></i> Re-Translate All Languages
            </button>
        </form>
        <a href="{{ url('/api/v1/posts/' . $post->id . '/export') }}" target="_blank" class="btn btn-light border d-flex align-items-center gap-1 font-mono">
            <i class="bi bi-download"></i> Export JSON
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Master Original Post (English Reference) -->
    <div class="col-12 col-xl-5">
        <div class="card-custom h-100">
            <div class="p-3 border-bottom bg-light bg-opacity-50 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-2 py-1 font-mono">EN</span>
                    <h6 class="fw-bold mb-0 text-dark">Master Source Content (English)</h6>
                </div>
                <small class="text-muted font-mono">{{ $post->created_at->format('M d, Y H:i') }}</small>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Master Title</label>
                    <h5 class="fw-bold text-dark">{{ $post->title }}</h5>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Master Body</label>
                    <div class="p-3 bg-light rounded-3 text-secondary lh-lg" style="white-space: pre-wrap;">{{ $post->content }}</div>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <small class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Studio Tip</small>
                    <small class="text-muted">Translations in other languages inherit from this master content. Any active Glossary Brand Lock terms will be protected across all target locales.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Multi-Tab Language Translation Studio -->
    <div class="col-12 col-xl-7">
        <div class="card-custom">
            <!-- Language Nav Tabs -->
            @php
                $activeTab = request('tab', 'hi');
            @endphp
            <div class="p-3 border-bottom bg-light bg-opacity-25">
                <ul class="nav nav-pills gap-1" id="languageTabs" role="tablist">
                    @foreach($supportedLanguages as $code => $name)
                        @php
                            $tr = $translations->get($code);
                            $isCurrentTab = ($activeTab === $code);
                        @endphp
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 px-3 {{ $isCurrentTab ? 'active' : '' }} d-flex align-items-center gap-2 font-mono small"
                                    id="tab-btn-{{ $code }}" data-bs-toggle="pill" data-bs-target="#tab-content-{{ $code }}" type="button" role="tab">
                                <span>{{ strtoupper($code) }}</span>
                                <span class="d-none d-md-inline text-muted" style="font-size: 0.75rem;">{{ $name }}</span>
                                @if($tr)
                                    <span class="rounded-circle d-inline-block {{ $tr->status === 'approved' ? 'bg-success' : ($tr->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}"
                                          style="width: 8px; height: 8px;" title="{{ ucfirst($tr->status) }}"></span>
                                @else
                                    <span class="rounded-circle d-inline-block bg-secondary opacity-50" style="width: 8px; height: 8px;" title="Missing"></span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Tab Content Panes -->
            <div class="tab-content p-4" id="languageTabsContent">
                @foreach($supportedLanguages as $code => $name)
                    @php
                        $tr = $translations->get($code);
                        $isCurrentTab = ($activeTab === $code);
                    @endphp
                    <div class="tab-pane fade {{ $isCurrentTab ? 'show active' : '' }}" id="tab-content-{{ $code }}" role="tabpanel">

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $name }} ({{ strtoupper($code) }})</h5>
                                <div class="d-flex align-items-center gap-2">
                                    @if($tr)
                                        <span class="badge {{ $tr->status === 'approved' ? 'bg-success' : ($tr->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} px-2 py-1">
                                            Status: {{ strtoupper($tr->status) }}
                                        </span>
                                        <small class="text-muted">Last updated: {{ $tr->updated_at->diffForHumans() }}</small>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1">MISSING TRANSLATION</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <!-- Re-translate this language button -->
                                <form action="{{ route('studio.posts.translate', $post->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-robot"></i> {{ $tr ? 'Re-Translate with AI' : 'Generate AI Translation' }}
                                    </button>
                                </form>

                                @if($tr && $tr->status === 'pending')
                                    <form action="{{ route('studio.approvals.approve', $tr->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Translation Edit Form -->
                        <form action="{{ route('studio.posts.translation.update', ['id' => $post->id, 'locale' => $code]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title_{{ $code }}" class="form-label fw-bold">Translated Title</label>
                                <input type="text" name="title" id="title_{{ $code }}" class="form-control"
                                       value="{{ old('title', $tr ? $tr->title : '') }}" placeholder="Enter translated title in {{ $name }}..." required>
                            </div>

                            <div class="mb-3">
                                <label for="content_{{ $code }}" class="form-label fw-bold">Translated Content</label>
                                <textarea name="content" id="content_{{ $code }}" rows="7" class="form-control"
                                          placeholder="Enter translated content in {{ $name }}..." required>{{ old('content', $tr ? $tr->content : '') }}</textarea>
                            </div>

                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Approval State</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="approved" {{ ($tr && $tr->status === 'approved') ? 'selected' : '' }}>Approved (Publicly visible)</option>
                                        <option value="pending" {{ ($tr && $tr->status === 'pending') ? 'selected' : '' }}>Pending Review</option>
                                        <option value="rejected" {{ ($tr && $tr->status === 'rejected') ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 text-md-end mt-md-4">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold shadow-sm">
                                        <i class="bi bi-save me-1"></i> Save {{ $name }} Translation
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
