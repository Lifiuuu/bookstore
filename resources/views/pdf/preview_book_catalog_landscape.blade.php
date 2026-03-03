@extends('layouts.app')

@section('content')
    <div class="preview-wrap" style="padding:16px">
      <style>
        .preview-actions{ display:flex; gap:8px; align-items:center; margin-bottom:12px }
        .preview-frame{ width:100%; height:90vh; border:1px solid #ddd }
        .btn { display:inline-block; padding:8px 12px; border-radius:4px; text-decoration:none }
        .btn-primary{ background:#007bff; color:#fff }
        .btn-secondary{ background:#6c757d; color:#fff }
      </style>

      <div class="preview-actions">
        <h4 style="margin:0">Preview: Book Catalog — Landscape</h4>
        <div style="flex:1"></div>
        <a class="btn btn-secondary" href="{{ route('dashboard') }}">Back</a>
        <a class="btn btn-primary" style="background:#28a745" href="{{ route('pdf.book_catalog_landscape_download') }}">Download Landscape</a>
      </div>

      <iframe class="preview-frame" src="{{ route('pdf.book_catalog_landscape_pdf') }}"></iframe>
    </div>
@endsection
