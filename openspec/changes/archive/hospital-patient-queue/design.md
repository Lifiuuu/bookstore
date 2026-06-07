## Context

Proyek bookstore ini adalah sandbox Laravel 10 (template Purple Admin, Bootstrap 4, ikon MDI) yang digunakan untuk eksperimen berbagai fitur. Modul antrian rumah sakit ini adalah adaptasi dari konsep `realtime-digital-queue-sse` yang sudah ada, namun dikembangkan secara signifikan untuk skenario nyata rumah sakit: multi-poliklinik, sistem prioritas pasien, dan papan informasi multi-poli.

Konteks rumah sakit menambah kompleksitas berikut dibanding antrian generik:
1. **Multi-antrian sejajar**: Setiap poliklinik punya antrian mandiri yang berjalan bersamaan.
2. **Prioritas pasien**: Pasien lansia, disabilitas, dan darurat harus dipanggil lebih dahulu.
3. **Papan board multi-poli**: Satu layar menampilkan status semua poli secara bersamaan.
4. **Pengumuman suara kontekstual**: Nama poli dan ruangan ikut diumumkan.

App menggunakan Cache driver file secara default (dapat dikonfigurasi ke Redis). Tidak ada WebSocket server dan tidak ada dependensi infrastruktur baru yang diizinkan.

## Goals / Non-Goals

**Goals:**
- Demonstrasikan SSE multi-channel (per poli) menggunakan `response()->stream()` tanpa Laravel Echo/Pusher
- Implementasikan sistem prioritas antrian: Emergency > Lansia/Disabilitas > Normal, dengan tie-breaker urutan waktu daftar
- Simpan state antrian per poli secara ephemeral di Laravel Cache tanpa migrasi
- Papan board multi-poli yang menampilkan semua poli aktif secara bersamaan via SSE
- Pengumuman Web Speech API dalam Bahasa Indonesia dengan format kontekstual rumah sakit
- Integrasi bersih ke sidebar Purple Admin tanpa mengubah logika layout yang ada

**Non-Goals:**
- Autentikasi/otorisasi (sandbox — semua route publik)
- Riwayat antrian persisten atau pelaporan statistik
- Integrasi dengan sistem HIS (Hospital Information System) eksternal
- Multi-tenant atau multi-cabang rumah sakit
- WebSocket atau Laravel Echo/Pusher
- Redis sebagai keharusan (file cache cukup untuk demo localhost)
- Cetak nomor antrian fisik (thermal printer)

## Decisions

### D1: Cache per Poliklinik dengan Key Namespace

**Keputusan**: Gunakan `Cache::put("hospital_queue:{poli_id}", [...], 7200)` untuk setiap poliklinik. Daftar poli yang tersedia dikonfigurasi sebagai array konstanta di controller.

**Poliklinik default**: `umum`, `anak`, `gigi`, `kandungan`, `penyakit-dalam`, `mata` — dapat diperluas tanpa perubahan kode.

**Alternatif dipertimbangkan**: Satu cache key global `hospital_queue:all` berisi array per poli. Ditolak karena menyebabkan write contention lebih tinggi saat banyak poli aktif bersamaan.

---

### D2: Sistem Prioritas dengan Algoritma Sort Deterministik

**Keputusan**: Setiap entry antrian memiliki field `priority` dengan nilai integer:
```
emergency    = 1  (tertinggi)
lansia       = 2
disabilitas  = 2
normal       = 3  (terendah)
```

Saat `adminCall`, fungsi helper `getNextPatient()` mengurutkan array `waiting` berdasarkan `priority ASC, registered_at ASC` dan mengambil elemen pertama.

**Rationale**: Sort deterministik mencegah "priority starvation" untuk pasien normal — dalam antrian yang sama, pasien normal dilayani berurut berdasarkan waktu daftar setelah semua prioritas tinggi terlayani.

**Alternatif dipertimbangkan**: Separate queue per priority level. Ditolak karena UI nurse panel menjadi lebih kompleks dan tidak perlu untuk sandbox ini.

---

### D3: SSE Stream dengan Query Parameter `?poli=`

**Keputusan**: Endpoint SSE tunggal `GET /sse/hospital-queue-stream?poli={poli_id}` melayani stream untuk satu poli. Papan board multi-poli membuka **N EventSource connections** (satu per poli aktif).

**Rationale**: Mempertahankan pendekatan `while(true)` yang terbukti bekerja dari modul antrian generik. Multiple EventSource connections pada board adalah trade-off yang dapat diterima untuk sandbox dengan jumlah poli kecil (≤6).

**Alternatif dipertimbangkan**: Satu SSE endpoint yang mengirim semua poli dalam satu stream. Ditolak karena client harus parse state semua poli setiap cycle meski hanya satu poli berubah — lebih boros.

---

### D4: State Shape per Poliklinik

```json
{
  "poli_id": "umum",
  "poli_name": "Poli Umum",
  "version": 12,
  "next_number": 13,
  "current": {
    "number": 12,
    "name": "Budi Santoso",
    "priority": "lansia",
    "registered_at": "2026-05-28T09:30:00Z",
    "status": "called"
  },
  "waiting": [
    { "number": 13, "name": "Siti Aminah", "priority": "normal", "registered_at": "...", "status": "waiting" }
  ],
  "absent": [
    { "number": 9, "name": "Andi Pratama", "priority": "normal", "registered_at": "...", "status": "absent" }
  ]
}
```

SSE endpoint serialisasi struktur ini per poli sebagai JSON string di setiap `data:` line.

---

### D5: Papan Board Multi-Poli — Layout Grid

**Keputusan**: Board view menggunakan layout fullscreen mandiri (tidak extend `layouts.app`) dengan CSS Grid yang menampilkan card per poli. Setiap card menampilkan: nama poli, nomor yang sedang dipanggil, dan nama pasien.

**Desain visual**: Dark background `#0d1117`, card per poli dengan gradient berbeda berdasarkan index, nomor besar (8rem+), nama pasien (2rem), animasi pulse pada card yang baru update.

**Alternatif dipertimbangkan**: Carousel/slideshow berganti poli. Ditolak — rumah sakit butuh semua poli tampil bersamaan tanpa harus menunggu giliran tampilan.

---

### D6: Web Speech API — Format Pengumuman Rumah Sakit

**Keputusan**: Format utterance:
> *"Perhatian. Pasien nomor [N], [Nama]. Harap menuju [Nama Poli]. Terima kasih."*

Diucapkan dengan bahasa `id-ID`, rate `0.85` (lebih lambat untuk kejelasan), pitch `1.0`.

**Activation flow**: Sama dengan modul antrian generik — overlay "Aktifkan Papan Antrian" untuk memenuhi kebijakan autoplay browser.

---

### D7: Estimasi Waktu Tunggu

**Keputusan**: Tampilkan estimasi kasar pada halaman pendaftaran: `(posisi_antrian × 10)` menit. Nilai ini hanya indikatif dan tidak menggunakan perhitungan kompleks.

**Rationale**: Pasien butuh ekspektasi waktu, meski perkiraan kasar. Implementasi sederhana menghindari over-engineering untuk fitur sekunder.

## Risks / Trade-offs

| Risiko | Mitigasi |
|---|---|
| Race condition pada write concurrent (dua perawat poli sama klik "Panggil" bersamaan) | Gunakan `Cache::lock("hospital_queue:write:{poli_id}", 5)` pada setiap mutasi |
| Multiple EventSource connections di board membebani PHP-FPM | Acceptable untuk sandbox ≤6 poli; dokumentasikan bahwa produksi butuh Redis pub/sub |
| File cache tidak cocok untuk multi-server | Acceptable untuk localhost demo; tambahkan catatan bahwa Redis diperlukan untuk deployment cluster |
| SSE disconnect pada Nginx dengan output buffering | Header `X-Accel-Buffering: no` + dokumentasi `output_buffering = Off` |
| Web Speech API tidak konsisten antar browser | Graceful fallback: jika `window.speechSynthesis` tidak tersedia, hanya chime audio; warning badge ditampilkan |
| Pasien normal tertahan lama jika banyak pasien prioritas masuk terus | Acceptable untuk sandbox; produksi butuh aging algorithm |
