@extends('layouts.app')

@section('content')
    <div class="preview-wrap" style="padding:16px">
      <style>
        .preview-actions{ display:flex; gap:8px; align-items:center; margin-bottom:12px; flex-wrap: wrap; }
        .preview-frame{ width:100%; height:90vh; border:1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn { display:inline-block; padding:8px 12px; border-radius:4px; text-decoration:none; border: none; cursor: pointer; }
        .btn-primary{ background:#007bff; color:#fff }
        .btn-secondary{ background:#6c757d; color:#fff }
        .form-control { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; width: 250px; }
      </style>

      <form method="GET" action="{{ route('pdf.book_catalog_preview_landscape') }}" class="preview-actions">
        <h4 style="margin:0; margin-right: 16px;">Generate Sertifikat</h4>
        
        <label for="nama" style="font-weight: 500;">Nama Penerima:</label>
        <input type="text" id="nama" name="nama" class="form-control" value="{{ request('nama', 'Nama Peserta') }}" placeholder="Masukkan nama..." required>
        <button type="submit" class="btn btn-primary">Generate Preview</button>
        
        <div style="flex:1"></div>
        <a class="btn btn-secondary" href="{{ route('dashboard') }}">Kembali</a>
        <a class="btn btn-primary" style="background:#28a745" href="{{ route('pdf.book_catalog_landscape_download', ['nama' => request('nama', 'Nama Peserta')]) }}">Download Sertifikat</a>
      </form>

      <iframe class="preview-frame" src="{{ route('pdf.book_catalog_landscape_pdf', ['nama' => request('nama', 'Nama Peserta')]) }}"></iframe>
    </div>
@endsection
