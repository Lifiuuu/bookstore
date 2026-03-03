@extends('layouts.app')

@section('content')

<link href="/assets/css/DataTables/datatables.min.css" rel="stylesheet">

<div>
    <div>
        <div>
            <div>
                <h4>Daftar Barang</h4>
                <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm">+ Tambah Barang</a>
                <a href="{{ route('barang.labels') }}" class="btn btn-secondary btn-sm ms-2">Cetak Label</a>
            </div>
            <div>
                @if($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <table id="myTable" class="display table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>id_barang</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $barang)
                        <tr>
                            <td>{{ $barang->id_barang }}</td>
                            <td>{{ $barang->nama }}</td>
                            <td>{{ $barang->harga }}</td>
                            <td>{{ $barang->timestamp }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="/assets/js/DataTables/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#myTable').DataTable();
    });
</script>
@endpush
@endsection