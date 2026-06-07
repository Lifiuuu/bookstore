@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Panel Perawat — Antrian Pasien</h4>
                <div class="d-flex align-items-center">
                    <span id="sse-dot" class="me-2" style="width: 10px; height: 10px; border-radius: 50%; background-color: #ccc; display: inline-block;"></span>
                    <small id="sse-label" class="text-muted">Menghubungkan...</small>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills mb-4">
                    @foreach($polyclinics as $poli)
                        <li class="nav-item">
                            <a class="nav-link {{ $currentPoliId === $poli['id'] ? 'active' : '' }}" 
                               href="{{ route('hq.nurse', ['poli' => $poli['id']]) }}">
                                {{ $poli['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="row">
                    {{-- Sedang Dipanggil --}}
                    <div class="col-md-4 mb-3">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">Sedang Dipanggil</div>
                            <div class="card-body text-center d-flex flex-column justify-content-center" id="current-section">
                                @if($state['current'])
                                    <h1 class="display-1 fw-bold text-primary">{{ $state['current']['number'] }}</h1>
                                    <h4>{{ $state['current']['name'] }}</h4>
                                    <p class="badge bg-{{ $state['current']['priority'] === 'darurat' ? 'danger' : ($state['current']['priority'] === 'normal' ? 'secondary' : 'warning') }}">
                                        {{ ucfirst($state['current']['priority']) }}
                                    </p>
                                @else
                                    <p class="text-muted my-5">Belum ada pasien dipanggil</p>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <button class="btn btn-success w-100 mb-2" id="btn-call" data-poli="{{ $currentPoliId }}">Panggil Berikutnya</button>
                                <button class="btn btn-outline-danger w-100" id="btn-skip" data-poli="{{ $currentPoliId }}" {{ is_null($state['current']) ? 'disabled' : '' }}>Tandai Tidak Hadir</button>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Tunggu --}}
                    <div class="col-md-4 mb-3">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white d-flex justify-content-between">
                                <span>Daftar Tunggu</span>
                                <span class="badge bg-light text-dark rounded-pill" id="waiting-count">{{ count($state['waiting']) }}</span>
                            </div>
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <ul class="list-group list-group-flush" id="waiting-section">
                                    @forelse($state['waiting'] as $patient)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $patient['number'] }}</strong> - {{ $patient['name'] }}
                                            </div>
                                            <span class="badge bg-{{ $patient['priority'] === 'darurat' ? 'danger' : ($patient['priority'] === 'normal' ? 'secondary' : 'warning') }} rounded-pill">
                                                {{ ucfirst($patient['priority']) }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-muted py-4">Antrian kosong</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Tidak Hadir --}}
                    <div class="col-md-4 mb-3">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between">
                                <span>Tidak Hadir</span>
                                <span class="badge bg-light text-dark rounded-pill" id="absent-count">{{ count($state['absent']) }}</span>
                            </div>
                            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                <ul class="list-group list-group-flush" id="absent-section">
                                    @forelse($state['absent'] as $patient)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $patient['number'] }}</strong> - {{ $patient['name'] }}
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary btn-recall-item" data-number="{{ $patient['number'] }}">Recall</button>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-muted py-4">Tidak ada data</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="card-footer bg-transparent">
                                <button class="btn btn-outline-danger w-100" id="btn-reset" data-poli="{{ $currentPoliId }}">Reset Antrian</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Toasts container for notifications --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11" id="toast-container"></div>

@endsection

@push('scripts')
<script>
(function () {
  'use strict';

  const POLI_ID   = @json($currentPoliId);
  const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const SSE_URL   = `/sse/hospital-queue-stream?poli=${POLI_ID}`;

  // ── Polling Connection ─────────────────────────────────────────────────────────
  const sseDot   = document.getElementById('sse-dot');
  const sseLabel = document.getElementById('sse-label');
  let pollInterval = null;
  let errorCount = 0;

  function startPolling() {
    fetchState();
    pollInterval = setInterval(fetchState, 2000); // Poll every 2 seconds
  }

  function fetchState() {
    fetch(SSE_URL, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        sseDot.style.backgroundColor = '#28a745';
        sseLabel.textContent = 'Terhubung';
        errorCount = 0;
        
        if (data.type === 'single') {
            renderState(data.state);
        } else {
            renderState(data);
        }
    })
    .catch(err => {
        errorCount++;
        if (errorCount > 2) {
            sseDot.style.backgroundColor = '#dc3545';
            sseLabel.textContent = 'Terputus...';
        }
    });
  }

  startPolling();

  // ── Render State ───────────────────────────────────────────────────────────
  function getBadgeClass(priority) {
      if(priority === 'darurat') return 'danger';
      if(priority === 'normal') return 'secondary';
      return 'warning';
  }

  function renderState(state) {
    // Current
    const curSec = document.getElementById('current-section');
    const btnSkip = document.getElementById('btn-skip');
    if(state.current) {
        curSec.innerHTML = `
            <h1 class="display-1 fw-bold text-primary">${state.current.number}</h1>
            <h4>${escHtml(state.current.name)}</h4>
            <p class="badge bg-${getBadgeClass(state.current.priority)}">${capitalize(state.current.priority)}</p>
        `;
        btnSkip.disabled = false;
    } else {
        curSec.innerHTML = `<p class="text-muted my-5">Belum ada pasien dipanggil</p>`;
        btnSkip.disabled = true;
    }

    // Waiting
    document.getElementById('waiting-count').textContent = state.waiting ? state.waiting.length : 0;
    const waitSec = document.getElementById('waiting-section');
    if(!state.waiting || state.waiting.length === 0) {
        waitSec.innerHTML = `<li class="list-group-item text-center text-muted py-4">Antrian kosong</li>`;
    } else {
        waitSec.innerHTML = state.waiting.map(p => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div><strong>${p.number}</strong> - ${escHtml(p.name)}</div>
                <span class="badge bg-${getBadgeClass(p.priority)} rounded-pill">${capitalize(p.priority)}</span>
            </li>
        `).join('');
    }

    // Absent
    document.getElementById('absent-count').textContent = state.absent ? state.absent.length : 0;
    const absSec = document.getElementById('absent-section');
    if(!state.absent || state.absent.length === 0) {
        absSec.innerHTML = `<li class="list-group-item text-center text-muted py-4">Tidak ada data</li>`;
    } else {
        absSec.innerHTML = state.absent.map(p => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div><strong>${p.number}</strong> - ${escHtml(p.name)}</div>
                <button class="btn btn-sm btn-outline-primary btn-recall-item" data-number="${p.number}">Recall</button>
            </li>
        `).join('');
        bindRecallButtons();
    }
  }

  // ── Actions ────────────────────────────────────────────────────────────────
  function postAction(url, body, btnEl) {
    const originalText = btnEl ? btnEl.innerHTML : '';
    if (btnEl) { btnEl.disabled = true; btnEl.innerHTML = 'Loading...'; }
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify(body),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .catch(() => ({ ok: false, data: { error: 'Jaringan error' } }))
    .finally(() => { 
        if (btnEl) { 
            btnEl.disabled = false; 
            btnEl.innerHTML = originalText; 
        } 
    });
  }

  document.getElementById('btn-call')?.addEventListener('click', function() {
    postAction('/hospital-queue/nurse/call', { poli: POLI_ID }, this).then(res => {
      if (res.ok) showToast('Success', 'Memanggil pasien...');
      else showToast('Error', res.data.error || 'Gagal memanggil', true);
    });
  });

  document.getElementById('btn-skip')?.addEventListener('click', function() {
    postAction('/hospital-queue/nurse/skip', { poli: POLI_ID }, this).then(res => {
      if (res.ok) showToast('Success', 'Pasien ditandai tidak hadir');
      else showToast('Error', res.data.error, true);
    });
  });

  function bindRecallButtons() {
    document.querySelectorAll('.btn-recall-item').forEach(btn => {
      btn.addEventListener('click', function() {
        const number = parseInt(this.dataset.number);
        postAction('/hospital-queue/nurse/recall', { poli: POLI_ID, number: number }, this).then(res => {
          if (res.ok) showToast('Success', `Pasien No.${number} dikembalikan`);
          else showToast('Error', res.data.error, true);
        });
      });
    });
  }
  bindRecallButtons();

  document.getElementById('btn-reset')?.addEventListener('click', function() {
    if (!confirm('Reset antrian poli ini?')) return;
    postAction('/hospital-queue/nurse/reset', { poli: POLI_ID }, this).then(res => {
      if (res.ok) showToast('Success', 'Antrian direset');
      else showToast('Error', 'Gagal mereset', true);
    });
  });

  // ── Helpers ────────────────────────────────────────────────────────────────
  function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }
  function capitalize(str) {
      if(!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
  }
  
  function showToast(title, msg, isError = false) {
      const container = document.getElementById('toast-container');
      const bgClass = isError ? 'bg-danger text-white' : 'bg-success text-white';
      const toastHtml = `
        <div class="toast align-items-center ${bgClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <strong>${title}</strong>: ${msg}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;
      container.innerHTML = toastHtml;
      setTimeout(() => { container.innerHTML = ''; }, 3000);
  }
})();
</script>
@endpush
