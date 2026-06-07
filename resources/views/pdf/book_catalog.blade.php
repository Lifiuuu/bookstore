@extends('layouts.pdf')

@section('content')
<style>
  @page { size: A4 portrait; margin:0; }
  
  body {
    margin: 0;
    padding: 10mm 10mm 10mm 12mm;
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000;
  }

  /* Kop Surat */
  .kop-surat {
    width: 100%;
    border-bottom: 3px solid #000;
    padding-bottom: 2mm;
    margin-bottom: 1mm;
    display: table;
  }
  .kop-surat-inner {
    border-bottom: 1px solid #000;
    padding-bottom: 3px;
    display: table;
    width: 100%;
  }
  .kop-logo {
    display: table-cell;
    width: 25mm;
    vertical-align: middle;
  }
  .kop-logo img {
    width: 80px;
    height: auto;
  }
  .kop-text {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
  }
  .kop-univ {
    font-size: 14pt;
    font-weight: normal;
    letter-spacing: 1px;
    margin: 0;
    line-height: 1.2;
  }
  .kop-fakultas {
    font-size: 16pt;
    font-weight: bold;
    margin: 0;
    line-height: 1.2;
  }
  .kop-alamat {
    font-size: 10pt;
    margin: 4px 0 0 0;
    line-height: 1.2;
  }

  /* Info Surat */
  .info-surat {
    width: 100%;
    margin-top: 10mm;
    margin-bottom: 10mm;
  }
  .info-surat table {
    width: 100%;
    border: none;
  }
  .info-surat td {
    padding: 2px 0;
    vertical-align: top;
  }

  /* Isi Surat */
  .isi-surat {
    text-align: justify;
    margin-bottom: 10mm;
  }
  .isi-surat p {
    margin: 0 0 10px 0;
    text-indent: 10mm;
  }
  
  .rincian-acara {
    margin: 10px 0 15px 10mm;
  }
  .rincian-acara table {
    width: 100%;
  }
  .rincian-acara td {
    padding: 3px 0;
    vertical-align: top;
  }
  .rincian-label {
    width: 25mm;
  }
  .rincian-titik {
    width: 5mm;
  }

  /* Tanda Tangan */
  .ttd-container {
    width: 100%;
    margin-top: 20mm;
  }
  .ttd-box {
    float: right;
    width: 65mm;
    text-align: left;
  }
  .ttd-jabatan {
    font-weight: bold;
    margin-bottom: 25mm;
  }
  .ttd-nama {
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 0;
  }
  .ttd-nip {
    margin-top: 0;
  }

  /* Clearfix */
  .clearfix::after {
    content: "";
    clear: both;
    display: table;
  }

</style>

<div class="pdf-body">
  <!-- Kop Surat -->
  <div class="kop-surat">
    <div class="kop-surat-inner">
      <div class="kop-logo">
        @php $logoPath = public_path('assets/images/favicon.png'); @endphp
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" alt="Logo UNAIR">
        @endif
      </div>
      <div class="kop-text">
        <h2 class="kop-univ">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,<br>RISET, DAN TEKNOLOGI<br>UNIVERSITAS AIRLANGGA</h2>
        <h1 class="kop-fakultas">FAKULTAS VOKASI</h1>
        <p class="kop-alamat">Kampus B, Jl. Srikana 65 Surabaya 60286 Telp. (031) 5033869, 5053156, Fax. (031) 5053156<br>Laman: http://vokasi.unair.ac.id; e-mail: info@vokasi.unair.ac.id</p>
      </div>
    </div>
  </div>

  <!-- Info Surat -->
  <div class="info-surat">
    <table>
      <tr>
        <td style="width: 20mm;">Nomor</td>
        <td style="width: 5mm;">:</td>
        <td style="width: 110mm;">1234/UN3.15/KP/{{ date('Y') }}</td>
        <td style="text-align: right;">Surabaya, {{ date('d F Y') }}</td>
      </tr>
      <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td colspan="2">-</td>
      </tr>
      <tr>
        <td>Perihal</td>
        <td>:</td>
        <td colspan="2"><b>Undangan Rapat Dosen</b></td>
      </tr>
    </table>
  </div>

  <div style="margin-bottom: 10mm;">
    Yth. Bapak/Ibu Dosen<br>
    Fakultas Vokasi<br>
    Universitas Airlangga<br>
    Surabaya
  </div>

  <!-- Isi Surat -->
  <div class="isi-surat">
    <p>Sehubungan dengan akan dimulainya perkuliahan semester ganjil tahun akademik {{ date('Y') }}/{{ date('Y', strtotime('+1 year')) }}, dengan hormat kami mengundang Bapak/Ibu Dosen Fakultas Vokasi Universitas Airlangga untuk hadir pada Rapat Persiapan Perkuliahan yang akan diselenggarakan pada:</p>

    <div class="rincian-acara">
      <table>
        <tr>
          <td class="rincian-label">Hari, tanggal</td>
          <td class="rincian-titik">:</td>
          <td>Senin, 15 Juli 2026</td>
        </tr>
        <tr>
          <td class="rincian-label">Waktu</td>
          <td class="rincian-titik">:</td>
          <td>09.00 WIB s.d. selesai</td>
        </tr>
        <tr>
          <td class="rincian-label">Tempat</td>
          <td class="rincian-titik">:</td>
          <td>Ruang Rapat Utama, Gedung Fakultas Vokasi Kampus B UNAIR</td>
        </tr>
        <tr>
          <td class="rincian-label">Acara</td>
          <td class="rincian-titik">:</td>
          <td>Persiapan Perkuliahan Semester Ganjil dan Evaluasi Kurikulum</td>
        </tr>
      </table>
    </div>

    <p>Mengingat pentingnya acara tersebut, kami mohon kehadiran Bapak/Ibu tepat pada waktunya. Demikian undangan ini kami sampaikan, atas perhatian dan kehadiran Bapak/Ibu kami ucapkan terima kasih.</p>
  </div>

  <!-- Tanda Tangan -->
  <div class="ttd-container clearfix">
    <div class="ttd-box">
      <div class="ttd-jabatan">Dekan,</div>
      <div class="ttd-nama">Prof. Dr. Anwar Ma'ruf, drh., M.Kes.</div>
      <div class="ttd-nip">NIP. 196605151993031003</div>
    </div>
  </div>

</div>
@endsection