@extends('layouts.studio')

@section('title', 'Create New Post')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Create Master Post & Auto-Translate</h4>
                <p class="text-muted small mb-0">Compose your original content in English and configure AI automated localization.</p>
            </div>
            <a href="{{ route('studio.posts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Posts
            </a>
        </div>

        <div class="card-custom p-4">
            <form action="{{ route('studio.posts.store') }}" method="POST">
                @csrf

                <!-- Post Title -->
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">Master Post Title (English) <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control form-control-lg @error('title') is-invalid @enderror"
                           placeholder="e.g. Modern Web Architecture with Laravel and React" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Post Content -->
                <div class="mb-4">
                    <label for="content" class="form-label fw-bold">Master Post Content (English) <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror"
                              placeholder="Write your full article, announcement, or blog content here..." required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        <i class="bi bi-shield-lock text-info me-1"></i> Active Brand Lock terms from the Glossary will be preserved automatically.
                    </small>
                </div>

                <!-- Translation Engine Mode Selection (Feature 2) -->
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-cpu-fill text-primary"></i> Multi-Language Localization Engine Mode
                    </label>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="translation_mode" id="mode_async" value="async" checked>
                        <label class="form-check-label" for="mode_async">
                            <strong class="text-primary"><i class="bi bi-lightning-charge-fill me-1"></i> Asynchronous Background Queue (Recommended)</strong>
                            <p class="text-muted small mb-0">Dispatches <code>TranslatePostJob</code> to Laravel's background queue. Instant response without HTTP timeout, resilient to rate-limits.</p>
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="translation_mode" id="mode_sync" value="sync">
                        <label class="form-check-label" for="mode_sync">
                            <strong><i class="bi bi-hourglass-split me-1"></i> Synchronous (Immediate)</strong>
                            <p class="text-muted small mb-0">Translates immediately across all languages before responding. Best for short announcements.</p>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="translation_mode" id="mode_none" value="none">
                        <label class="form-check-label" for="mode_none">
                            <strong><i class="bi bi-pencil-square me-1"></i> Manual / Translate Later</strong>
                            <p class="text-muted small mb-0">Saves the master English post only. You can trigger translations individually in the Studio editor.</p>
                        </label>
                    </div>
                </div>

                <!-- Languages to be generated preview -->
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold text-uppercase">Target Locales</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($supportedLanguages as $code => $name)
                            <span class="badge bg-white text-dark border px-3 py-2 font-mono">
                                <i class="bi bi-check2 text-success me-1"></i> {{ $name }} ({{ strtoupper($code) }})
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('studio.posts.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Create Post & Proceed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
