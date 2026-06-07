## 1. Routes & Controller Scaffold

- [ ] 1.1 Create `app/Http/Controllers/QueueController.php` with method stubs: `guestForm`, `guestSubmit`, `adminPanel`, `adminCall`, `adminSkip`, `adminRecall`, `boardDisplay`, `sseStream`
- [ ] 1.2 Add all queue routes to `routes/web.php` — `GET /queue/guest`, `POST /queue/guest/submit`, `GET /queue/admin`, `POST /queue/admin/call`, `POST /queue/admin/skip`, `POST /queue/admin/recall`, `GET /queue/board`, `GET /sse/queue-stream`
- [ ] 1.3 Name the routes: `queue.guest`, `queue.guest.submit`, `queue.admin`, `queue.admin.call`, `queue.admin.skip`, `queue.admin.recall`, `queue.board`, `sse.queue-stream`

## 2. Queue State Management (Cache Layer)

- [ ] 2.1 Implement private `QueueController::getState()` method: reads `queue:list` from Cache, returns default state if null (`version:0, next_number:1, current:null, waiting:[], skipped:[]`)
- [ ] 2.2 Implement private `QueueController::saveState(array $state)` method: increments `state['version']`, stores to Cache with 3600s TTL using `Cache::lock('queue:write', 5)` for write safety
- [ ] 2.3 Implement `guestSubmit()`: validate `name` required, call `getState()`, build new entry `{number: next_number, name, status: 'waiting'}`, append to `waiting`, increment `next_number`, call `saveState()`, redirect back with `queued` session flash containing the assigned number
- [ ] 2.4 Implement `adminCall()`: call `getState()`, check `waiting` not empty (return 422 JSON if empty), move first waiting entry to `current` with `status: 'called'`, remove from `waiting`, call `saveState()`, return JSON `{success:true, state}`
- [ ] 2.5 Implement `adminSkip()`: call `getState()`, check `current` not null (return 422 JSON if null), move `current` to `skipped` array with `status: 'skipped'`, set `current` to null, call `saveState()`, return JSON `{success:true}`
- [ ] 2.6 Implement `adminRecall()`: validate `number` input, call `getState()`, find entry in `skipped` by number (return 404 JSON if not found), remove from `skipped`, prepend to `waiting` with `status: 'waiting'`, call `saveState()`, return JSON `{success:true}`

## 3. SSE Stream Endpoint

- [ ] 3.1 Implement `sseStream()` in `QueueController`: return `response()->stream()` with headers `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no`
- [ ] 3.2 Inside the stream closure: initialize `$lastVersion = -1`, start `while(true)` loop
- [ ] 3.3 Inside loop: check `connection_aborted()` — if true, break out of loop
- [ ] 3.4 Inside loop: call `getState()`, if `state['version'] !== $lastVersion` then echo `data: ` + `json_encode(state)` + `\n\n`, update `$lastVersion = state['version']`
- [ ] 3.5 Inside loop: always echo `:heartbeat\n\n` to keep connection alive through proxies, then `ob_flush()` and `flush()`, then `sleep(2)`
- [ ] 3.6 Set PHP `set_time_limit(0)` and `ignore_user_abort(true)` at the start of the stream closure to allow long-running connections

## 4. Guest View (`resources/views/queue/guest.blade.php`)

- [ ] 4.1 Extend `layouts.app` and set `@section('title', 'Daftar Antrian')` and `@push('stylepage')` for custom queue CSS
- [ ] 4.2 Build the registration form: card-based layout, name input with validation error display, submit button "Ambil Nomor Antrian"
- [ ] 4.3 Show assigned number card: when `session('queued')` is set, display a prominent success card showing "Nomor Antrian Anda:" and the number in large bold text, with a "Daftar Lagi" link to clear and go back
- [ ] 4.4 Style the view with modern CSS — gradient card background, large queue-number font (≥72px), smooth fade-in animation for the number card, fully responsive

## 5. Admin View (`resources/views/queue/admin.blade.php`)

- [ ] 5.1 Extend `layouts.app`, set title "Admin Panel — Antrian"
- [ ] 5.2 Build three-column grid layout: Current Called card, Waiting List card, Skipped List card
- [ ] 5.3 Current Called card: shows `number` and `name` of the current entry (initially from server render), "Panggil Berikutnya" button (calls `POST /queue/admin/call` via JS), "Tandai Terlambat" button (calls `POST /queue/admin/skip` via JS)
- [ ] 5.4 Waiting List card: renders ordered list of waiting entries as badge-style items with number and name; shows "Antrian kosong" if empty
- [ ] 5.5 Skipped List card: renders skipped entries each with a "Recall" button that calls `POST /queue/admin/recall` via JS with the entry's number
- [ ] 5.6 Implement `EventSource` JS on admin view: connect to `/sse/queue-stream`, on `message` event parse JSON and update all three sections' DOM without page reload
- [ ] 5.7 Implement AJAX handlers for Call, Skip, and Recall buttons using `fetch()` with CSRF token in headers
- [ ] 5.8 Add visual feedback: loading spinners on button clicks, toast notifications for success/error responses

## 6. Board View (`resources/views/queue/board.blade.php`)

- [ ] 6.1 Create a standalone layout (do NOT extend `layouts.app` — fullscreen, no sidebar): minimal `<!DOCTYPE html>` with embedded CSS for dark/high-contrast display
- [ ] 6.2 Add start overlay: full-viewport dark overlay with centered "Mulai Papan Antrian" button; overlay `z-index` higher than board content
- [ ] 6.3 Build board layout: centered number display (padding at least 10rem font size), name display (3–4rem), status text "Sedang Dipanggil", footer "Sistem Antrian Digital — Papan Informasi"
- [ ] 6.4 Add `<audio id="chime" src="/audio/dingdong.mp3" preload="auto"></audio>` element
- [ ] 6.5 Implement start button click handler: hide overlay, establish `EventSource` to `/sse/queue-stream`, set `boardActivated = true`
- [ ] 6.6 Implement SSE `onmessage` handler: parse JSON, update number/name DOM elements, if `current.number !== lastAnnouncedNumber` then trigger announcement
- [ ] 6.7 Implement announcement function: play `#chime` audio, then on `ended` event (or after 1s timeout) call `window.speechSynthesis.speak(utterance)` with text "Ting tong, nomor antrian [N]. [Name]. Silakan masuk.", lang `id-ID`
- [ ] 6.8 Add graceful degradation: if `!window.speechSynthesis`, show a small warning badge "Speech API tidak didukung" and continue with chime-only
- [ ] 6.9 Style board view: dark background (`#0f172a`), large glowing number text (gradient or neon), smooth fade/scale transition when number changes, pulsing ring animation on active number

## 7. Sidebar Navigation

- [ ] 7.1 Add new `<li class="nav-item">` block to `resources/views/layouts/sidebar.blade.php` after the Kunjungan Toko menu item
- [ ] 7.2 Add collapsible toggle link: label "Queue System (SSE)", icon `mdi-clock-fast`, `data-toggle="collapse"`, `href="#queueSystem"`, `aria-expanded` based on `request()->is('queue/*')`
- [ ] 7.3 Add collapse `<div id="queueSystem">` with three sub-links: "Daftar Antrian" → `route('queue.guest')`, "Admin Panel" → `route('queue.admin')`, "Papan Antrian" → `route('queue.board')`
- [ ] 7.4 Set sub-link `active` class correctly: each sub-link active when its exact route matches

## 8. Static Assets

- [ ] 8.1 Create directory `public/audio/` and add a placeholder `dingdong.mp3` note in a `README.txt` inside the folder explaining the user must supply an actual audio file
- [ ] 8.2 Verify that existing Purple Admin CSS (`/assets/css/style.css`) styles do not conflict with queue views; add scoped overrides in `@push('stylepage')` as needed for guest and admin views

## 9. Verification & Smoke Test

- [ ] 9.1 Start the Laravel dev server (`php artisan serve`) and navigate to `/queue/guest` — verify form displays and number assignment works
- [ ] 9.2 Open `/queue/admin` in one browser tab and `/queue/board` in another — verify sidebar highlighting, initial render, and SSE connections establish
- [ ] 9.3 On the board tab, click "Mulai Papan Antrian" — verify overlay disappears and SSE connects
- [ ] 9.4 On the admin tab, click "Panggil Berikutnya" — verify board updates within 2 seconds and admin waiting list shrinks
- [ ] 9.5 On the admin tab, click "Tandai Terlambat" — verify current entry moves to skipped list
- [ ] 9.6 On the admin tab, click "Recall" on a skipped entry — verify it reappears at the top of the waiting list
- [ ] 9.7 Check browser console on both admin and board tabs for any JS errors
- [ ] 9.8 Verify sidebar "Queue System (SSE)" collapses on non-queue routes and expands with correct sub-link highlighted on queue routes
