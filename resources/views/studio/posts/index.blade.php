@extends('layouts.studio')

@section('title', 'Content & Posts')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Content & Multi-Language Posts</h4>
        <p class="text-muted small mb-0">Manage source content and monitor multi-locale translation coverage.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('studio.posts.create') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Create New Post
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card-custom p-3 mb-4">
    <form action="{{ route('studio.posts.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by post title or content..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-filter"></i> Search & Filter
            </button>
        </div>
        <div class="col-12 col-md-3">
            @if(request('search'))
                <a href="{{ route('studio.posts.index') }}" class="btn btn-light border w-100">Reset Search</a>
            @endif
        </div>
    </form>
</div>

<!-- Posts Table Card -->
<div class="card-custom">
    @if($posts->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-text text-muted fs-1 mb-2 d-block"></i>
            <h5 class="fw-bold text-dark">No posts found</h5>
            <p class="text-muted small">Create your first post or adjust your search keywords.</p>
            <a href="{{ route('studio.posts.create') }}" class="btn btn-primary btn-sm">Create Post Now</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;"># ID</th>
                        <th>Original Title & Content</th>
                        <th>Language Coverage</th>
                        <th>Job Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td class="font-mono text-muted">#{{ $post->id }}</td>
                            <td>
                                <div class="fw-bold text-dark mb-1">{{ $post->title }}</div>
                                <p class="text-muted small mb-0 text-truncate" style="max-width: 320px;">
                                    {{ Str::limit($post->content, 80) }}
                                </p>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 280px;">
                                    @foreach($supportedLanguages as $code => $name)
                                        @php
                                            $tr = $post->postTranslations ? $post->postTranslations->firstWhere('locale', $code) : null;
                                        @endphp
                                        @if($tr)
                                            <span class="badge {{ $tr->status === 'approved' ? 'bg-success' : ($tr->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} badge-lang"
                                                  title="{{ $name }}: {{ ucfirst($tr->status) }}">
                                                {{ strtoupper($code) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border badge-lang" title="{{ $name }}: Missing">
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
                            <td class="text-muted small font-mono">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('studio.posts.edit', $post->id) }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1" title="Open Studio Editor">
                                        <i class="bi bi-pencil-square"></i> Studio
                                    </a>
                                    <a href="{{ url('/api/v1/posts/' . $post->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View JSON API">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-end">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
