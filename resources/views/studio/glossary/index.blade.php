@extends('layouts.studio')

@section('title', 'Glossary & Brand Lock')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Glossary & Brand Lock Dictionary</h4>
        <p class="text-muted small mb-0">Protect technical terms, trademarks, and company names from being erroneously translated by AI engines.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTermModal">
            <i class="bi bi-shield-plus"></i> Add Protected Term
        </button>
    </div>
</div>

<!-- Interactive Live Brand Lock Tester Widget -->
<div class="card-custom p-4 mb-4 border-primary border-opacity-25 bg-gradient">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary text-white p-2 rounded-3">
                <i class="bi bi-magic fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-0">Interactive Brand Lock & Glossary Sandbox</h6>
                <small class="text-muted">Test how your active glossary rules preserve technical brand names in real-time.</small>
            </div>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 font-mono">
            <i class="bi bi-shield-check me-1"></i> Live Token Masking Engine
        </span>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label fw-bold small text-muted text-uppercase">Sample Text to Test</label>
            <textarea id="sandboxText" rows="3" class="form-control" placeholder="e.g. We develop mission-critical apps using Laravel, Docker, and Apple macOS hardware.">We develop mission-critical enterprise systems using Laravel, React, and deploy on AWS cloud.</textarea>
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-bold small text-muted text-uppercase">Target Language</label>
            <select id="sandboxTargetLang" class="form-select font-mono">
                @foreach($supportedLanguages as $code => $name)
                    @if($code !== 'en')
                        <option value="{{ $code }}" {{ $code === 'hi' ? 'selected' : '' }}>
                            {{ $name }} ({{ strtoupper($code) }})
                        </option>
                    @endif
                @endforeach
            </select>
            <button type="button" class="btn btn-primary w-100 mt-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="runSandboxTest()">
                <i class="bi bi-play-circle-fill"></i> Test Translation
            </button>
        </div>
        <div class="col-12 col-lg-2 d-flex flex-column justify-content-center text-center p-3 bg-light rounded-3 border">
            <span class="text-muted small fw-semibold">Status</span>
            <div class="fw-bold font-mono text-dark mt-1" id="sandboxStatus">Ready</div>
        </div>
    </div>

    <!-- Sandbox Comparison Results Display (injected by JS) -->
    <div id="sandboxResults" class="mt-4 pt-3 border-top" style="display: none;">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="fw-bold text-success font-mono">
                            <i class="bi bi-shield-check me-1"></i> WITH GLOSSARY BRAND LOCK (PROTECTED)
                        </small>
                        <span class="badge bg-success">Recommended</span>
                    </div>
                    <div class="text-dark fw-medium lh-base" id="resWithGlossary"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="fw-bold text-danger font-mono">
                            <i class="bi bi-x-circle me-1"></i> WITHOUT BRAND LOCK (RAW MACHINE TRANSLATE)
                        </small>
                        <span class="badge bg-danger">Unprotected</span>
                    </div>
                    <div class="text-secondary lh-base" id="resWithoutGlossary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Glossary Terms Table Card -->
<div class="card-custom">
    <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-check text-primary fs-5"></i>
            <h6 class="fw-bold mb-0">Registered Protected Terms & Words</h6>
            <span class="badge bg-light text-dark border font-mono">{{ $terms->total() }}</span>
        </div>
        <form action="{{ route('studio.glossary') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search terms..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
    </div>

    @if($terms->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-shield text-muted fs-1 mb-2 d-block"></i>
            <h5 class="fw-bold text-dark">No glossary terms registered yet</h5>
            <p class="text-muted small">Protect brands like "Laravel", "React", "Docker", or company names from mistranslation.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTermModal">Add First Term</button>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Term / Keyword</th>
                        <th>Protection Strategy</th>
                        <th>Target Locale</th>
                        <th>Description / Category</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terms as $term)
                        <tr>
                            <td class="font-mono text-muted">#{{ $term->id }}</td>
                            <td>
                                <span class="fw-bold text-dark font-mono fs-6">{{ $term->term }}</span>
                            </td>
                            <td>
                                @if($term->do_not_translate)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-lock-fill me-1"></i> Brand Lock (Keep Original)
                                    </span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-arrow-right me-1"></i> Replace: <strong>{{ $term->replacement }}</strong>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-mono">
                                    {{ strtoupper($term->target_locale) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $term->description ?: '—' }}
                            </td>
                            <td>
                                <form action="{{ route('studio.glossary.toggle', $term->id) }}" method="POST">
                                    @csrf
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" {{ $term->is_active ? 'checked' : '' }}>
                                    </div>
                                </form>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('studio.glossary.destroy', $term->id) }}" method="POST" onsubmit="return confirm('Delete glossary term \'{{ $term->term }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Term">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-end">
            {{ $terms->links() }}
        </div>
    @endif
</div>

<!-- Add Term Modal -->
<div class="modal fade" id="addTermModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Register Protected Term</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('studio.glossary.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="termInput" class="form-label fw-bold">Word or Phrase to Protect <span class="text-danger">*</span></label>
                        <input type="text" name="term" id="termInput" class="form-control font-mono" placeholder="e.g. Laravel, Apple, React, Docker" required>
                        <small class="text-muted">Case-insensitive whole-word matching will prevent AI from translating this.</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="do_not_translate" id="doNotTranslateSwitch" value="1" checked onchange="toggleReplacementInput(this)">
                            <label class="form-check-label fw-bold" for="doNotTranslateSwitch">Keep Original Term Untouched (Brand Lock)</label>
                        </div>
                    </div>

                    <div class="mb-3" id="replacementWrapper" style="display: none;">
                        <label for="replacementInput" class="form-label fw-bold">Custom Approved Translation</label>
                        <input type="text" name="replacement" id="replacementInput" class="form-control" placeholder="Custom specific replacement">
                    </div>

                    <div class="mb-3">
                        <label for="targetLocaleSelect" class="form-label fw-bold">Applicable Target Locale</label>
                        <select name="target_locale" id="targetLocaleSelect" class="form-select font-mono">
                            <option value="all">All Languages (Universal Lock)</option>
                            @foreach($supportedLanguages as $code => $name)
                                @if($code !== 'en')
                                    <option value="{{ $code }}">{{ $name }} ({{ strtoupper($code) }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descInput" class="form-label fw-bold">Description / Category (Optional)</label>
                        <input type="text" name="description" id="descInput" class="form-control" placeholder="e.g. Trademark, Technical Framework, Product Name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Register Protected Term</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleReplacementInput(checkbox) {
        const wrapper = document.getElementById('replacementWrapper');
        wrapper.style.display = checkbox.checked ? 'none' : 'block';
    }

    function runSandboxTest() {
        const text = document.getElementById('sandboxText').value;
        const targetLang = document.getElementById('sandboxTargetLang').value;
        const statusEl = document.getElementById('sandboxStatus');

        if (!text.trim()) {
            alert('Please enter some text to test.');
            return;
        }

        statusEl.innerHTML = '<span class="text-primary"><i class="bi bi-arrow-repeat spin"></i> Processing...</span>';

        fetch("{{ url('/api/v1/glossary/test') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                text: text,
                target_lang: targetLang,
                source_lang: 'en'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Completed</span>';
                document.getElementById('resWithGlossary').innerText = data.data.with_glossary;
                document.getElementById('resWithoutGlossary').innerText = data.data.without_glossary;
                document.getElementById('sandboxResults').style.display = 'block';
            } else {
                statusEl.innerHTML = '<span class="text-danger">Failed</span>';
                alert('Test translation failed: ' + JSON.stringify(data.errors || data.message));
            }
        })
        .catch(err => {
            statusEl.innerHTML = '<span class="text-danger">Error</span>';
            alert('Error running test: ' + err.toString());
        });
    }
</script>
@endsection
