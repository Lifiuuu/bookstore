@extends('layouts.app')

@section('content')

<div class="container">
    <h4>Edit Barang</h4>

    <form action="{{ route('barang.update', $barang->id_barang) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $barang->nama) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Harga</label>
            <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga', $barang->harga) }}">
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

@endsection
