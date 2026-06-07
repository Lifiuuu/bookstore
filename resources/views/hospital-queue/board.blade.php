@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Papan Antrian Digital</h4>
                <div class="d-flex align-items-center">
                    <span id="board-clock" class="fw-bold me-3 font-monospace">--:--:--</span>
                    <span id="sse-dot-board" class="me-2" style="width: 10px; height: 10px; border-radius: 50%; background-color: #ccc; display: inline-block;"></span>
                    <small id="sse-label-board" class="text-muted">Menunggu aktivasi…</small>
                </div>
            </div>
            <div class="card-body">
                
                {{-- Activation Overlay (Inline inside card) --}}
                <div id="activation-overlay" class="text-center py-5">
                    <h2 class="mb-4">Sistem Informasi Antrian Pasien</h2>
                    <button id="btn-activate" class="btn btn-primary btn-lg">
                        ▶ Aktifkan Papan Antrian & Audio
                    </button>
                </div>

                {{-- Speech warning badge --}}
                <div class="alert alert-danger" id="speech-warning" style="display: none;">
                    ⚠ Pengumuman suara (Web Speech API) tidak didukung di browser ini.
                </div>

                {{-- Board Grid --}}
                <div class="row" id="board-grid" style="display: none;">
                    @foreach($polyclinics as $index => $poli)
                        @php
                            // Assign different border colors for aesthetics
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                            $color = $colors[$index % count($colors)];
                        @endphp
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-{{ $color }} h-100 poli-card shadow-sm" id="card-{{ $poli['id'] }}">
                                <div class="card-header bg-{{ $color }} text-white text-center fw-bold text-uppercase">
                                    {{ $poli['name'] }}
                                </div>
                                <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                                    <h1 class="display-1 fw-bold text-{{ $color }} card-number" id="num-{{ $poli['id'] }}" style="font-size: 5rem;">—</h1>
                                    <h3 class="mt-3 card-patient-name" id="name-{{ $poli['id'] }}">Menunggu panggilan</h3>
                                </div>
                                <div class="card-footer bg-transparent text-center text-muted fw-bold text-uppercase card-status" id="status-{{ $poli['id'] }}">
                                    Menunggu panggilan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Chime audio --}}
<audio id="chime" src="/audio/dingdong.mp3" preload="auto"></audio>
@endsection

@push('scripts')
<style>
    /* Add a subtle pulse animation for newly called cards */
    .poli-card { transition: all 0.3s ease; }
    .poli-card.just-called {
        transform: scale(1.02);
        box-shadow: 0 0 20px rgba(0, 123, 255, 0.5) !important;
    }
</style>
<script>
(function () {
  'use strict';

  const POLYCLINICS = @json($polyclinics);

  let boardActivated       = false;
  let speechSupported      = ('speechSynthesis' in window);
  const lastAnnouncedNumber = {}; 
  const eventSources        = {}; 

  // ── Clock ──────────────────────────────────────────────────────────────────
  function updateClock() {
    const now = new Date();
    document.getElementById('board-clock').textContent =
      now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }
  setInterval(updateClock, 1000);
  updateClock();

  // ── Activation ─────────────────────────────────────────────────────────────
  document.getElementById('btn-activate').addEventListener('click', function () {
    document.getElementById('activation-overlay').style.display = 'none';
    document.getElementById('board-grid').style.display = 'flex';
    boardActivated = true;

    if (!speechSupported) {
      document.getElementById('speech-warning').style.display = 'block';
    }

    // Pre-load chime to satisfy autoplay policy
    const chime = document.getElementById('chime');
    chime.volume = 0;
    chime.play().catch(() => {}).finally(() => { chime.volume = 1; chime.pause(); chime.currentTime = 0; });

    // Open one single multiplexed SSE connection for all polyclinics
    connectSSE();

    document.getElementById('sse-dot-board').style.backgroundColor = '#28a745';
    document.getElementById('sse-label-board').textContent = 'Terhubung';
  });

  // ── Polling multiplexed ────────────────────────────────────────────────────────
  let pollInterval = null;
  let errorCount = 0;

  function connectSSE() {
    fetchState();
    pollInterval = setInterval(fetchState, 2000);
  }

  function fetchState() {
    fetch('/sse/hospital-queue-stream?poli=all', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('sse-dot-board').style.backgroundColor = '#28a745';
        document.getElementById('sse-label-board').textContent = 'Terhubung';
        errorCount = 0;

        if (data.type === 'all' && Array.isArray(data.states)) {
            data.states.forEach(state => updateCard(state));
        }
    })
    .catch(err => {
        errorCount++;
        if (errorCount > 2) {
            document.getElementById('sse-dot-board').style.backgroundColor = '#dc3545';
            document.getElementById('sse-label-board').textContent = 'Reconnecting…';
        }
    });
  }

  // ── Update Card ────────────────────────────────────────────────────────────
  function updateCard(state) {
    const poliId   = state.poli_id;
    const poliName = state.poli_name;
    const current  = state.current;

    const numEl    = document.getElementById(`num-${poliId}`);
    const nameEl   = document.getElementById(`name-${poliId}`);
    const statusEl = document.getElementById(`status-${poliId}`);
    const cardEl   = document.getElementById(`card-${poliId}`);

    if (!numEl) return;

    const newNumber = current ? current.number : null;
    const lastNumber = lastAnnouncedNumber[poliId] ?? null;

    if (newNumber !== lastNumber) {
        numEl.textContent   = current ? String(current.number) : '—';
        nameEl.textContent  = current ? current.name : 'Menunggu panggilan';
        statusEl.textContent = current ? 'Sedang Dipanggil' : 'Menunggu panggilan';
        
        if(current) {
            statusEl.classList.remove('text-muted');
            statusEl.classList.add('text-success');
        } else {
            statusEl.classList.add('text-muted');
            statusEl.classList.remove('text-success');
        }

      if (current && boardActivated) {
        // Pulse animation
        cardEl.classList.remove('just-called');
        void cardEl.offsetWidth; // reflow
        cardEl.classList.add('just-called');
        setTimeout(() => cardEl.classList.remove('just-called'), 3000);

        // Announcement
        announce(current.number, current.name, poliName);
        lastAnnouncedNumber[poliId] = current.number;
      } else {
        lastAnnouncedNumber[poliId] = newNumber;
      }
    }
  }

  // ── Announcement ───────────────────────────────────────────────────────────
  let announcementQueue = [];
  let isAnnouncing = false;

  function announce(number, name, poliName) {
    announcementQueue.push({ number, name, poliName });
    if (!isAnnouncing) processNextAnnouncement();
  }

  function processNextAnnouncement() {
    if (announcementQueue.length === 0) { isAnnouncing = false; return; }
    isAnnouncing = true;
    const { number, name, poliName } = announcementQueue.shift();

    const chime = document.getElementById('chime');
    chime.currentTime = 0;

    const playPromise = chime.play();
    if (playPromise !== undefined) {
      playPromise.catch(() => { speakText(number, name, poliName); });
    }

    const onChimeEnd = () => {
      chime.removeEventListener('ended', onChimeEnd);
      speakText(number, name, poliName);
    };
    chime.addEventListener('ended', onChimeEnd);

    setTimeout(() => { chime.removeEventListener('ended', onChimeEnd); }, 5000);
  }

  function speakText(number, name, poliName) {
    if (!speechSupported) {
      setTimeout(processNextAnnouncement, 500);
      return;
    }
    window.speechSynthesis.cancel();
    const text = `Perhatian. Pasien nomor ${number}, ${name}. Harap menuju ${poliName}. Terima kasih.`;
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang  = 'id-ID';
    utterance.rate  = 0.85;
    utterance.pitch = 1.0;
    utterance.onend = processNextAnnouncement;
    utterance.onerror = processNextAnnouncement;
    window.speechSynthesis.speak(utterance);
  }

})();
</script>
@endpush
