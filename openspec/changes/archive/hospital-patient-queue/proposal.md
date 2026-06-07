## Why

Rumah sakit umumnya masih mengandalkan sistem antrian manual (kertas, tiket fisik, papan tulis) yang menyebabkan penumpukan pasien, kurangnya transparansi, dan staf kewalahan mengelola panggilan. Modul ini menghadirkan sistem antrian digital berbasis SSE yang mencakup multi-poli (poliklinik), triage prioritas, dan papan informasi publik — menggantikan antrian kertas dengan solusi real-time tanpa biaya infrastruktur tambahan.

## What Changes

- **Modul baru**: `Antrian Pasien (SSE)` ditambahkan ke sidebar navigasi dengan sub-menu per peran: Pendaftaran Pasien, Panel Perawat/Admin, Papan Antrian Poli.
- **Controller baru**: `HospitalQueueController` menangani semua logika antrian pasien (pendaftaran, panggilan dokter, manajemen poli, SSE stream).
- **State berbasis Cache**: Data antrian disimpan di Laravel Cache per poliklinik — tidak perlu migrasi database baru.
- **Multi-poliklinik**: Setiap poli (Umum, Anak, Gigi, Kandungan, dll.) memiliki antrian independen dalam satu cache key per poli.
- **Prioritas pasien**: Setiap pendaftar dapat ditandai prioritas: Normal, Lansia, Disabilitas, Darurat — antrian diurutkan berdasarkan prioritas.
- **Routes baru** (publik untuk demo sandbox):
  - `GET /hospital-queue/register` — form pendaftaran pasien
  - `POST /hospital-queue/register/submit` — submit nama, poli, prioritas; terima nomor antrian
  - `GET /hospital-queue/nurse` — panel perawat/admin per poli
  - `POST /hospital-queue/nurse/call` — panggil pasien berikutnya
  - `POST /hospital-queue/nurse/skip` — tandai tidak hadir (absent)
  - `POST /hospital-queue/nurse/recall` — recall pasien tidak hadir
  - `POST /hospital-queue/nurse/reset` — reset antrian poli (akhir sesi)
  - `GET /hospital-queue/board` — papan informasi publik (multi-poli)
  - `GET /sse/hospital-queue-stream` — SSE endpoint per poli
- **Blade views baru**: `hospital-queue/register.blade.php`, `hospital-queue/nurse.blade.php`, `hospital-queue/board.blade.php`
- **Client-side SSE**: `EventSource` pada panel perawat dan papan board untuk update real-time.
- **Web Speech API**: Papan board mengumumkan panggilan pasien dalam Bahasa Indonesia dengan format: *"Pasien nomor [N], [Nama], Poli [X], silakan menuju ruangan."*
- **Update sidebar**: Item "Antrian Pasien (SSE)" ditambahkan ke `layouts/sidebar.blade.php`.

## Capabilities

### New Capabilities

- `patient-registration`: Form pendaftaran pasien mandiri — memilih poliklinik, mengisi nama, memilih kategori prioritas; mendapat nomor antrian beserta estimasi waktu tunggu.
- `nurse-panel`: Panel perawat/admin per poli — melihat daftar tunggu dengan badge prioritas, memanggil pasien berikutnya (urut prioritas lalu urut waktu daftar), menandai tidak hadir, melakukan recall, dan mereset antrian sesi.
- `queue-board-display`: Papan informasi publik multi-poli — menampilkan nomor yang sedang dipanggil per poli secara bersamaan; menggunakan Web Speech API untuk pengumuman suara dan audio chime.
- `hospital-sse-stream`: SSE endpoint per poliklinik yang mengirimkan state antrian sebagai JSON event; mendukung parameter `?poli=umum` untuk streaming spesifik poli.
- `hospital-queue-state`: State antrian per poli disimpan di Cache dengan key `hospital_queue:{poli_id}`; mendukung prioritas antrian (emergency > lansia/disabilitas > normal), penandaan tidak hadir, dan recall.

### Modified Capabilities

- `sidebar-navigation`: Penambahan menu "Antrian Pasien (SSE)" dengan tiga sub-link ke sidebar yang sudah ada.

## Impact

- **Files dibuat**: `app/Http/Controllers/HospitalQueueController.php`, `resources/views/hospital-queue/register.blade.php`, `resources/views/hospital-queue/nurse.blade.php`, `resources/views/hospital-queue/board.blade.php`
- **Files dimodifikasi**: `routes/web.php`, `resources/views/layouts/sidebar.blade.php`
- **Static asset dibutuhkan**: `public/audio/dingdong.mp3` (chime pengumuman)
- **Dependensi**: Laravel Cache (driver file/redis sudah terkonfigurasi), tanpa package Composer baru
- **Kebutuhan browser**: SSE (`EventSource`) didukung semua browser modern; Web Speech API optimal di Chrome/Edge
- **Tanpa breaking change** pada routes, controller, atau view yang sudah ada
