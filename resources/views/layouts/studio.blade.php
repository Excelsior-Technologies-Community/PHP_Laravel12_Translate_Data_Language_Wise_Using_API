<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Translation Studio') | Multi-Language AI Localization Suite</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --code-font: 'JetBrains Mono', monospace;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: var(--bs-body-font-family);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-studio {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .navbar-brand-badge {
            background: var(--primary-gradient);
            color: white;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .nav-link.active-studio {
            color: #4f46e5 !important;
            font-weight: 700;
            background-color: #eef2ff;
            border-radius: 8px;
        }
        .nav-link-studio {
            color: #475569;
            font-weight: 500;
            padding: 0.5rem 0.9rem !important;
            transition: all 0.2s;
        }
        .nav-link-studio:hover {
            color: #4f46e5;
            background-color: #f1f5f9;
            border-radius: 8px;
        }
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-custom:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1rem;
        }
        .table-custom td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }
        .badge-lang {
            font-family: var(--code-font);
            font-size: 0.75rem;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
        }
        .font-mono {
            font-family: var(--code-font);
        }
        .footer-studio {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 0;
            color: #64748b;
            font-size: 0.85rem;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Studio Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-studio sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('studio.dashboard') }}">
                <div class="bg-primary text-white p-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-translate fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold fs-6 text-dark tracking-tight">Translation Studio</span>
                    <span class="navbar-brand-badge ms-1">AI PRO</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studioNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="studioNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-link-studio {{ request()->routeIs('studio.dashboard') ? 'active-studio' : '' }}" href="{{ route('studio.dashboard') }}">
                            <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-studio {{ request()->routeIs('studio.posts.*') ? 'active-studio' : '' }}" href="{{ route('studio.posts.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> Content & Posts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-studio {{ request()->routeIs('studio.approvals') ? 'active-studio' : '' }}" href="{{ route('studio.approvals') }}">
                            <i class="bi bi-check2-circle me-1"></i> Approvals
                            @php
                                $pendingBadgeCount = \App\Models\PostTranslation::where('status', 'pending')->where('locale', '!=', 'en')->count();
                            @endphp
                            @if($pendingBadgeCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill ms-1 font-mono small">{{ $pendingBadgeCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-studio {{ request()->routeIs('studio.glossary') ? 'active-studio' : '' }}" href="{{ route('studio.glossary') }}">
                            <i class="bi bi-book me-1"></i> Glossary & Brand Lock
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-studio {{ request()->routeIs('studio.analytics') ? 'active-studio' : '' }}" href="{{ route('studio.analytics') }}">
                            <i class="bi bi-graph-up-arrow me-1"></i> Analytics & Quota
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url('/api/v1/posts') }}" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 font-mono">
                        <i class="bi bi-code-slash"></i> API Docs
                    </a>
                    <a href="{{ route('studio.posts.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-semibold shadow-sm px-3">
                        <i class="bi bi-plus-lg"></i> New Post
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container-fluid px-4 py-4 flex-grow-1">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 border-0 bg-success bg-opacity-10 text-success fw-medium" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 border-0 bg-danger bg-opacity-10 text-danger fw-medium" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 border-0 bg-info bg-opacity-10 text-info fw-medium" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Studio Footer -->
    <footer class="footer-studio">
        <div class="container-fluid px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="fw-semibold text-dark">Translation Studio & Content Localization</span> &bull; Multi-language API Engine for Laravel 12
            </div>
            <div class="d-flex align-items-center gap-3 font-mono small">
                <span><i class="bi bi-shield-check text-success me-1"></i> Brand Lock Active</span>
                <span><i class="bi bi-cpu text-primary me-1"></i> Async Queue Ready</span>
                <span><i class="bi bi-database-check text-info me-1"></i> Cache Enabled</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
