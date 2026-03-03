@extends('layouts.pdf')

@push('pdf-style')
<style>
  /* Update CSS following user's recommendation for Dompdf */
  @page { size: a4 landscape; margin: 0; }

  body {
    margin: 0;
    padding: 0;
    width: 297mm;
    height: 210mm;
    font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
  }

  .page {
    width: 297mm;
    height: 210mm;
    position: relative;
    background: #ffffff;
  }

  .card {
    position: absolute;
    top: 10mm;
    left: 10mm;
    right: 10mm;
    bottom: 10mm;
    border: 6px solid #890f94;
    background-color: #c4bcf7;
  }

  /* Header using table/absolute-friendly layout */
  .header-table { width:100%; border-collapse:collapse; margin-top:8px;padding-left: 8px;}
  .logo-cell { width:86px; padding-right:12px; vertical-align:middle }
  .logo { width:86px; height:86px; border-radius:10px; background:#fff; display:inline-block; vertical-align:middle; text-align:center; border:2px solid rgba(0,0,0,0.06) }
  .org { font-size:18px; font-weight:800; color:#0b3d91 }
  .sub { font-size:11px; color:#6a6a6a }

  /* Use print-friendly units (pt) to avoid DPI scaling issues */
  .title { text-align:center; width:100%; margin-top:30px; font-size:27pt; color:#b57b18; font-weight:800 }
  .presented { text-align:center; margin-top:6mm; font-size:11pt; color:#444 }
  .name { text-align:center; font-size:24pt; color:#0b2f6b; font-weight:800; margin-top:6mm }
  .desc { text-align:center; margin-top:6mm; font-size:11pt; color:#333; max-width:76%; margin-left:auto; margin-right:auto }

  /* Signatures positioned absolutely for Dompdf */
  .sign-row { position:absolute; bottom:40px; width:100% }
  .sign-left { position:absolute; left:50px; text-align:center }
  .sign-right { position:absolute; right:50px; text-align:center }
  .sigline { height:12mm; width:60mm; border-bottom:2px solid #d8c39a; margin:0 auto 6mm auto }
  .signame { font-weight:700; color:#222 }
  .sigtitle { color:#666; font-size:12px }

  .qr { width:22mm; height:22mm; background:#fff; border:2px solid #eee; padding:2mm; box-sizing:border-box; border-radius:8px; position:absolute; right:50px; bottom:50px }

  /* Avoid page breaks inside a single certificate */
  .page, .card { page-break-inside: avoid }

  /* Center main text block */
  .centered { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 72%; text-align: center }

</style>
@endpush

@section('content')
  <div class="page">
    <div class="card">
      <div class="ribbon"></div>

      <table class="header-table">
        <tr>
          <td class="logo-cell">
                @php $logoPath = public_path('assets/images/favicon.png'); @endphp
            <div class="logo">
                <img src="{{ $logoPath }}" alt="Logo" style="width:64px;height:64px;object-fit:cover;border-radius:8px;display:block;margin:6px;" />
              </div>
          </td>
          <td>
            <div class="org">YAYASAN PENGEMBANGAN KOMPETENSI</div>
            <div class="sub">Sertifikat Penghargaan & Pelatihan</div>
          </td>
        </tr>
      </table>

      <div class="centered">
        <div class="title">SERTIFIKAT PENGHARGAAN</div>
        <div class="presented">Diberikan kepada</div>
        <div class="name">Choi San</div>
        <div class="desc">Telah menyelesaikan program pelatihan dengan predikat sangat baik.</div>
      

      <div class="sign-row">
        <div class="sign-left">
          <div style="display:inline-block; margin-right:18mm; text-align:center">
            <div class="sigline"></div>
            <div class="signame">Dr. Ahmad Subandi</div>
            <div class="sigtitle">Direktur Program</div>
          </div>
          <div style="display:inline-block; text-align:center">
            <div class="sigline"></div>
            <div class="signame">Siti Nurhaliza</div>
            <div class="sigtitle">Koordinator Pelatihan</div>
          </div>
        </div>
    </div>
@endsection
