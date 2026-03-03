@extends('layouts.pdf')

@section('content')
<style>
  @page { size: A4 portrait; margin: 20mm; }
  
  body {
    margin: 0;
    padding: 0;
    width: 297mm;
    height: 210mm;
    font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
  }


  .pdf-body {
    width: 100%;
    height: 100%;
    background: #fffdf7;
    padding: 20mm;
    box-sizing: border-box;
  }
  .header {
    display: flex;
    align-items: center;
    margin-bottom: 20mm;
  }
  .logo {
    width: 86px;
    height: 86px;
    border-radius: 10px;
    background: #fff;
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    border: 2px solid rgba(0,0,0,0.06);
    margin-right: 12px;
  }
  .title {
    font-size: 27pt;
    color: #b57b18;
    font-weight: 800;
  }
  .subtitle {
    font-size: 14pt;
    color: #0b3d91;
    font-weight: 600;
  }

</style>

<div class="pdf-body">
  <div class="header">
    @php $logoPath = public_path('assets/images/favicon.png'); @endphp
    <div>
      @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" class="logo" alt="logo">
      @endif
    </div>
    <div>
      <div class="title">Library Catalog</div>
      <div class="subtitle">Daftar Buku per Kategori</div>
    </div>
  </div>

  <div class="content">
    <!-- Static sample content (manual, non-database) -->
    <h3 style="margin-bottom:6px; margin-top:18px">Fiksi Populer (3)</h3>
    <table>
      <thead>
        <tr>
          <th style="width:6%">No</th>
          <th>Judul</th>
          <th style="width:20%">Pengarang</th>
          <th style="width:12%">Kode</th>
          <th style="width:12%">Diterbitkan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Petualangan di Negeri Awan</td>
          <td>Andi Prasetyo</td>
          <td>FIK-001</td>
          <td>2020-05-10</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Misteri Rumah Tua</td>
          <td>Sari Melati</td>
          <td>FIK-002</td>
          <td>2018-11-02</td>
        </tr>
        <tr>
          <td>3</td>
          <td>Cerita Senja</td>
          <td>Budi Santoso</td>
          <td>FIK-003</td>
          <td>2021-07-18</td>
        </tr>
      </tbody>
    </table>

    <h3 style="margin-bottom:6px; margin-top:18px">Non-Fiksi (2)</h3>
    <table>
      <thead>
        <tr>
          <th style="width:6%">No</th>
          <th>Judul</th>
          <th style="width:20%">Pengarang</th>
          <th style="width:12%">Kode</th>
          <th style="width:12%">Diterbitkan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Sejarah Kota Kita</td>
          <td>Rina Kusuma</td>
          <td>NON-101</td>
          <td>2015-03-22</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Dasar Pemrograman</td>
          <td>Irwan Putra</td>
          <td>NON-102</td>
          <td>2019-09-05</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

@endsection