## 1. Controller & Route Scaffold

- [x] 1.1 Buat `app/Http/Controllers/HospitalQueueController.php` dengan property `$polyclinics` berisi array 6 poli (umum, anak, gigi, kandungan, penyakit-dalam, mata) dan method stubs: `registerForm`, `registerSubmit`, `nursePanel`, `nurseCall`, `nurseSkip`, `nurseRecall`, `nurseReset`, `boardDisplay`, `sseStream`
- [x] 1.2 Tambahkan semua route antrian rumah sakit ke `routes/web.php`: `GET /hospital-queue/register`, `POST /hospital-queue/register/submit`, `GET /hospital-queue/nurse`, `POST /hospital-queue/nurse/call`, `POST /hospital-queue/nurse/skip`, `POST /hospital-queue/nurse/recall`, `POST /hospital-queue/nurse/reset`, `GET /hospital-queue/board`, `GET /sse/hospital-queue-stream`
- [x] 1.3 Namai semua route: `hq.register`, `hq.register.submit`, `hq.nurse`, `hq.nurse.call`, `hq.nurse.skip`, `hq.nurse.recall`, `hq.nurse.reset`, `hq.board`, `sse.hospital-queue`

## 2. Queue State Management (Cache Layer)

- [x] 2.1 Implementasikan `private getState(string $poliId): array` — baca `hospital_queue:{poliId}` dari Cache; jika null, kembalikan state default `{poli_id, poli_name, version:0, next_number:1, current:null, waiting:[], absent:[]}`
- [x] 2.2 Implementasikan `private saveState(string $poliId, array $state): void` — increment `state['version']`, wrap `Cache::put()` dengan `Cache::lock("hospital_queue:write:{poliId}", 5)`, TTL 7200s
- [x] 2.3 Implementasikan `private getNextPatient(array $waiting): ?array` — sort array `waiting` berdasarkan `priority ASC, registered_at ASC` lalu kembalikan elemen pertama (atau null jika kosong)
- [x] 2.4 Implementasikan `registerSubmit()`: validasi `name` required, validasi `poli` ada di `$polyclinics`, validasi `priority` salah satu dari `[normal, lansia, disabilitas, darurat]`; buat entry baru dengan `number: next_number`, `priority_value` (1=darurat, 2=lansia/disabilitas, 3=normal), `registered_at: now()->toISOString()`; append ke `waiting`; increment `next_number`; panggil `saveState()`; redirect dengan flash session berisi nomor antrian dan posisi antrian
- [x] 2.5 Implementasikan `nurseCall()`: validasi `poli` param; panggil `getState()`; jika `waiting` kosong return JSON 422 `{error:'Antrian kosong'}`; panggil `getNextPatient($state['waiting'])` untuk pilih pasien berikutnya; pindahkan ke `current` dengan `status:'called'`; hapus dari `waiting`; panggil `saveState()`; return JSON `{success:true, state}`
- [x] 2.6 Implementasikan `nurseSkip()`: validasi `poli` param; jika `current` null return JSON 422; pindahkan `current` ke `absent` dengan `status:'absent'`; set `current` ke null; `saveState()`; return JSON `{success:true}`
- [x] 2.7 Implementasikan `nurseRecall()`: validasi `poli` dan `number` param; cari entry di `absent` by number (return 404 JSON jika tidak ada); hapus dari `absent`; prepend ke `waiting` dengan `status:'waiting'` dan priority asli tetap; `saveState()`; return JSON `{success:true}`
- [x] 2.8 Implementasikan `nurseReset()`: validasi `poli` param; hapus cache key `hospital_queue:{poliId}`; return JSON `{success:true}`

## 3. SSE Stream Endpoint

- [x] 3.1 Implementasikan `sseStream()`: kembalikan `response()->stream()` dengan headers `Content-Type: text/event-stream`, `Cache-Control: no-cache`, `X-Accel-Buffering: no`
- [x] 3.2 Di dalam stream closure: baca `$poliId = request('poli', 'umum')`; validasi ada di `$polyclinics` (fallback ke 'umum' jika tidak valid); inisialisasi `$lastVersion = -1`
- [x] 3.3 Set `set_time_limit(0)` dan `ignore_user_abort(true)` sebelum loop
- [x] 3.4 Di dalam loop `while(true)`: cek `connection_aborted()` — jika true, break
- [x] 3.5 Di dalam loop: panggil `getState($poliId)`; jika `state['version'] !== $lastVersion` maka echo `data: ` + `json_encode($state)` + `\n\n`; update `$lastVersion`
- [x] 3.6 Di dalam loop: selalu echo `:heartbeat\n\n`, kemudian `ob_flush()`, `flush()`, lalu `sleep(2)`

## 4. View: Pendaftaran Pasien (`resources/views/hospital-queue/register.blade.php`)

- [x] 4.1 Extend `layouts.app`, set `@section('title', 'Pendaftaran Antrian Pasien')`, gunakan `@push('stylepage')` untuk CSS kustom
- [x] 4.2 Bangun form pendaftaran: card layout, input nama dengan tampilan error validasi, dropdown poli dari `$polyclinics`, radio group prioritas (Normal/Lansia/Disabilitas/Darurat) dengan ikon medis, tombol submit "Ambil Nomor Antrian"
- [x] 4.3 Tampilkan card sukses: ketika `session('queued')` ada, tampilkan card menonjol dengan nomor antrian besar (≥72px), nama poli, dan estimasi waktu tunggu `(posisi × 10)` menit; sertakan link "Daftar Pasien Lain"
- [x] 4.4 Styling: gradient card background bernuansa medis (putih/biru tua), nomor antrian font besar bold, animasi fade-in pada card sukses, fully responsive

## 5. View: Panel Perawat (`resources/views/hospital-queue/nurse.blade.php`)

- [x] 5.1 Extend `layouts.app`, set title "Panel Perawat — Antrian Pasien"
- [x] 5.2 Tambahkan selector poli di bagian atas: dropdown/tab untuk memilih poli aktif; navigasi ke `?poli={id}` saat berubah
- [x] 5.3 Bangun layout tiga kolom: card "Sedang Dipanggil", card "Daftar Tunggu", card "Tidak Hadir"
- [x] 5.4 Card "Sedang Dipanggil": tampilkan nomor dan nama pasien saat ini (dari server render), badge prioritas berwarna, tombol "Panggil Berikutnya" dan "Tandai Tidak Hadir"
- [x] 5.5 Card "Daftar Tunggu": render daftar pasien sebagai item badge dengan nomor, nama, dan badge prioritas berwarna (merah=darurat, oranye=lansia/disabilitas, abu=normal); tampilkan "Antrian kosong" jika tidak ada
- [x] 5.6 Card "Tidak Hadir": render daftar dengan tombol "Recall" per pasien; tampilkan "Tidak ada" jika kosong
- [x] 5.7 Implementasikan `EventSource` JS: konek ke `/sse/hospital-queue-stream?poli={poliId}`, pada event `message` parse JSON dan update semua tiga section DOM tanpa reload halaman
- [x] 5.8 Implementasikan AJAX handler untuk tombol Panggil, Skip, Recall, Reset menggunakan `fetch()` dengan CSRF token di header
- [x] 5.9 Tambahkan feedback visual: loading spinner pada klik tombol, toast notifikasi untuk respons sukses/error, konfirmasi dialog untuk Reset Antrian

## 6. View: Papan Antrian (`resources/views/hospital-queue/board.blade.php`)

- [x] 6.1 Buat layout standalone (TIDAK extend `layouts.app` — fullscreen, tanpa sidebar): `<!DOCTYPE html>` minimal dengan embedded CSS dark mode untuk tampilan layar informasi publik
- [x] 6.2 Tambahkan overlay aktivasi: overlay fullscreen gelap dengan tombol terpusat "Aktifkan Papan Antrian — Klik untuk mengaktifkan audio"; `z-index` lebih tinggi dari konten board
- [x] 6.3 Bangun layout board: CSS Grid responsif menampilkan card per poli; setiap card memuat: nama poli (header), nomor yang dipanggil (font ≥8rem), nama pasien (font 2rem), teks status "Sedang Dipanggil" atau "Menunggu panggilan"
- [x] 6.4 Tambahkan elemen `<audio id="chime" src="/audio/dingdong.mp3" preload="auto"></audio>`
- [x] 6.5 Implementasikan handler klik tombol aktivasi: sembunyikan overlay, buka satu `EventSource` per poli mengarah ke `/sse/hospital-queue-stream?poli={poliId}`, set `boardActivated = true`
- [x] 6.6 Implementasikan handler `onmessage` SSE per poli: parse JSON, update card DOM poli terkait, jika `current.number !== lastAnnouncedNumber[poliId]` jalankan fungsi pengumuman
- [x] 6.7 Implementasikan fungsi pengumuman: mainkan `#chime`; pada event `ended` (atau timeout 1s) panggil `speechSynthesis.speak(utterance)` dengan teks "Perhatian. Pasien nomor [N], [Nama]. Harap menuju [Nama Poli]. Terima kasih." bahasa `id-ID`, rate `0.85`
- [x] 6.8 Tambahkan degradasi graceful: jika `!window.speechSynthesis` tampilkan badge peringatan statis "Pengumuman suara tidak didukung" tanpa melempar error JS
- [x] 6.9 Animasi card: saat nomor baru terpanggil, tambahkan kelas CSS `just-called` ke card poli terkait untuk animasi pulse/glow selama 3 detik, lalu hapus kelas
- [x] 6.10 Styling board: background gelap `#0d1117`, setiap card poli dengan gradient berbeda berdasarkan index, nomor besar dengan efek glow, transisi fade/scale saat nomor berubah

## 7. Sidebar Navigation

- [x] 7.1 Tambahkan blok `<li class="nav-item">` baru ke `resources/views/layouts/sidebar.blade.php` setelah item Queue System (SSE) yang sudah ada
- [x] 7.2 Tambahkan toggle collapsible: label "Antrian Pasien (SSE)", ikon `mdi-hospital-building` atau `mdi-account-heart`, `data-toggle="collapse"`, `href="#hospitalQueue"`, `aria-expanded` berdasarkan `request()->is('hospital-queue/*')`
- [x] 7.3 Tambahkan `<div id="hospitalQueue">` dengan tiga sub-link: "Daftar Pasien" → `route('hq.register')`, "Panel Perawat" → `route('hq.nurse')`, "Papan Antrian" → `route('hq.board')`
- [x] 7.4 Set kelas `active` sub-link dengan benar: setiap sub-link aktif hanya saat route-nya yang tepat cocok

## 8. Static Assets

- [x] 8.1 Pastikan direktori `public/audio/` ada (sudah dibuat oleh modul antrian sebelumnya); jika belum ada, buat direktori tersebut
- [x] 8.2 Verifikasi `public/audio/dingdong.mp3` ada; jika belum ada, buat `README.txt` di `public/audio/` yang menjelaskan pengguna harus menyediakan file audio mp3

## 9. Verifikasi & Smoke Test

- [x] 9.1 Jalankan `php artisan serve` dan buka `/hospital-queue/register` — verifikasi form tampil dengan dropdown poli dan radio prioritas berfungsi
- [x] 9.2 Submit form dengan nama dan poli — verifikasi card sukses muncul dengan nomor antrian dan estimasi waktu tunggu
- [x] 9.3 Buka `/hospital-queue/nurse?poli=umum` — verifikasi panel perawat tampil dengan state kosong awal dan koneksi SSE terbentuk
- [x] 9.4 Klik "Panggil Berikutnya" — verifikasi pasien pindah ke card "Sedang Dipanggil" dan daftar tunggu berkurang
- [x] 9.5 Daftarkan pasien dengan prioritas berbeda (Normal dan Darurat) — verifikasi pasien Darurat dipanggil lebih dahulu
- [x] 9.6 Klik "Tandai Tidak Hadir" — verifikasi pasien pindah ke card "Tidak Hadir"
- [x] 9.7 Klik "Recall" pada pasien tidak hadir — verifikasi pasien kembali ke atas daftar tunggu
- [x] 9.8 Buka `/hospital-queue/board` di tab lain, klik "Aktifkan Papan Antrian" — verifikasi overlay hilang dan semua card poli tampil
- [x] 9.9 Lakukan panggilan dari panel perawat — verifikasi papan board update dalam ≤2 detik dan pengumuman suara diputar
- [x] 9.10 Verifikasi sidebar "Antrian Pasien (SSE)" collapse di route non-antrian dan expand dengan sub-link aktif yang tepat di route antrian
- [x] 9.11 Cek console browser pada tab perawat dan board untuk error JS
