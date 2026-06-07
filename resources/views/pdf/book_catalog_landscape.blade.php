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
    font-family: 'Helvetica', 'Arial', sans-serif;
  }

  .page {
    width: 297mm;
    height: 210mm;
    position: relative;
    background: #ffffff;
  }

  /* Main decorative border */
  .card {
    position: absolute;
    top: 10mm;
    left: 10mm;
    right: 10mm;
    bottom: 10mm;
    border: 2px solid #d4af37; /* Gold border */
    background-color: #fcfcfc;
    box-sizing: border-box;
  }



  /* Header using table/absolute-friendly layout */
  .header-table { width:100%; border-collapse:collapse; margin-top:15px; padding-left: 20px;}
  .logo-cell { width:86px; padding-right:15px; vertical-align:middle; text-align: center; }
  .logo { width:70px; height:70px; display:inline-block; vertical-align:middle; }
  .org { font-size:22px; font-weight:bold; color:#1a365d; letter-spacing: 2px; text-transform: uppercase; }
  .sub { font-size:12px; color:#4a5568; letter-spacing: 1px; margin-top: 4px; text-transform: uppercase; }

  /* Center main text block */
  .centered { position: absolute; left: 50%; top: 48%; transform: translate(-50%, -50%); width: 80%; text-align: center; }

  /* Typography */
  .title { text-align:center; width:100%; font-size:42pt; color:#d4af37; font-family: 'Times New Roman', serif; font-weight:normal; letter-spacing: 4px; margin-bottom: 20px; }
  .presented { text-align:center; font-size:14pt; color:#4a5568; margin-bottom: 25px; font-style: italic; }
  
  .name-container { border-bottom: 2px solid #d4af37; width: 80%; margin: 0 auto 20px auto; padding-bottom: 5px; }
  .name { text-align:center; font-size:36pt; color:#1a365d; font-family: 'Times New Roman', serif; font-weight:bold; text-transform: capitalize; }
  
  .desc { text-align:center; font-size:14pt; color:#2d3748; line-height: 1.6; max-width:85%; margin-left:auto; margin-right:auto; }

  /* Signatures positioned absolutely for Dompdf */
  .sign-row { position:absolute; bottom:30px; width:100% }
  .sign-left { position:absolute; left:80px; text-align:center }
  .sign-right { position:absolute; right:80px; text-align:center }
  .sigline { height:12mm; width:70mm; border-bottom:1px solid #1a365d; margin:0 auto 6mm auto }
  .signame { font-weight:bold; color:#1a365d; font-size: 14pt; font-family: 'Times New Roman', serif; }
  .sigtitle { color:#4a5568; font-size:11pt; margin-top: 4px; }

  /* Avoid page breaks inside a single certificate */
  .page, .card { page-break-inside: avoid }

</style>
@endpush

@section('content')
  <div class="page">
    <div class="card">

        <table class="header-table">
          <tr>
            <td class="logo-cell">
              @php $logoPath = public_path('assets/images/favicon.png'); @endphp
              @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Logo" class="logo" style="width:70px;height:70px;object-fit:contain;" />
              @endif
            </td>
            <td>
              <div class="org">Universitas Airlangga</div>
              <div class="sub">Fakultas Vokasi</div>
            </td>
          </tr>
        </table>

        <div class="centered">
          <div class="title">SERTIFIKAT PENGHARGAAN</div>
          <div class="presented">Dengan bangga diberikan kepada:</div>
          
          <div class="name-container">
            <div class="name">{{ $nama ?? 'Nama Peserta' }}</div>
          </div>
          
          <div class="desc">Atas partisipasi dan dedikasinya yang luar biasa serta telah menyelesaikan seluruh program dengan predikat <b>Sangat Memuaskan</b>.</div>
        </div>
        
        <div class="sign-row">
          <div class="sign-left">
            <div style="display:inline-block; text-align:center">
              <div class="sigline"></div>
              <div class="signame">Prof. Dr. Anwar Ma'ruf, drh., M.Kes.</div>
              <div class="sigtitle">Dekan Fakultas Vokasi</div>
            </div>
          </div>
          <div class="sign-right">
            <div style="display:inline-block; text-align:center">
              <div style="height:12mm; margin:0 auto 6mm auto; display: flex; align-items: flex-end; justify-content: center;">
                <span style="font-size: 12pt; color: #4a5568; font-style: italic;">Surabaya, {{ date('d F Y') }}</span>
              </div>
              <div class="signame">Ketua Panitia</div>
              <div class="sigtitle">Bina Bakti</div>
            </div>
          </div>
        </div>
    </div>
  </div>
@endsection
