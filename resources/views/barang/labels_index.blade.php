@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Cetak Label (TnJ No 108)</h3>

    <form method="POST" action="{{ route('labels.print') }}">
        @csrf

        <div class="mb-3">
            <label>Mulai pada kolom X (1..8)</label>
            <input type="number" name="start_x" min="1" max="8" value="1" class="form-control" style="width:120px;" />
        </div>

        <div class="mb-3">
            <label>Mulai pada baris Y (1..5)</label>
            <input type="number" name="start_y" min="1" max="5" value="1" class="form-control" style="width:120px;" />
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check_all" /></th>
                    <th>Nama</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barangs as $barang)
                <tr>
                    <td><input type="checkbox" name="items[]" value="{{ $barang->id_barang }}" class="item_checkbox" /></td>
                    <td>{{ $barang->nama }}</td>
                    <td>{{ number_format($barang->harga,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button class="btn btn-primary">Cetak PDF</button>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('check_all').addEventListener('change', function(e){
    document.querySelectorAll('.item_checkbox').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endpush

@endsection
