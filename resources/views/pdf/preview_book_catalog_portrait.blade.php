@extends('layouts.app')

@section('content')

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview - Undangan Rapat Dosen</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <style>
    .preview-wrap{ padding:16px; }
    .preview-actions{ display:flex; gap:8px; align-items:center; margin-bottom:12px; flex-wrap: wrap; }
    .preview-frame{ width:100%; height:90vh; border:1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn { display:inline-block; padding:8px 12px; border-radius:4px; text-decoration:none; cursor: pointer; border: none; }
    .btn-primary{ background:#007bff; color:#fff }
    .btn-secondary{ background:#6c757d; color:#fff }
  </style>
</head>
<body>
  <div class="preview-wrap">
    <div class="preview-actions">
      <h4 style="margin:0">Preview: Undangan Rapat Dosen — Portrait</h4>
      <div style="flex:1"></div>
      <a class="btn btn-secondary" href="{{ route('dashboard') }}">Kembali</a>
      <a class="btn btn-primary" style="background:#28a745" href="{{ route('pdf.book_catalog_download') }}">Download Undangan</a>
    </div>

    <iframe class="preview-frame" src="{{ route('pdf.book_catalog_pdf') }}"></iframe>
  </div>
</body>
@endsection
