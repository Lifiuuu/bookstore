@extends('layouts.pdf')
@section('content')
<style>
    /* Hapus 'size:' dari sini agar tidak bentrok dengan setPaper() di Controller */
    @page { 
        margin: 0mm; 
    }
    body { 
        font-family: DejaVu Sans, Arial, sans-serif; 
        margin: 0; 
        padding: 0; 
        color: #000000;
    }
    /* Biarkan page mengikuti ukuran yang di-set dari Controller (100%) */
    .page { 
        position: relative; 
        width: 100%; 
        height: 100%; 
    }
    .label { 
        position: absolute; 
        box-sizing: border-box; 
        text-align: center;
        overflow: hidden; 
        color: #000000; 
    }
    .name { 
        font-weight: 600; 
        font-size: 5pt; 
        margin-bottom: 1px;
        line-height: 1;
    }
    .price { 
        font-weight: 700; 
        font-size: 6.5pt; 
        line-height: 1;
        margin-top: 1mm;
    }
    .slot-number { 
        position: absolute; 
        top: 1mm; 
        left: 1mm; 
        font-size: 5pt; 
        color: #000000; 
    }
    .barcode-image {
        display: block;
        height: auto;
        margin: 0.3mm auto 0.3mm;
        max-height: 6mm;
        object-fit: contain;
    }
    .id-number {
        font-size: 5pt;
        font-weight: 600;
        margin-bottom: 2px;
    }
    
    @if(!empty($calibrate))
    /* Garis merah ini sangat penting untuk test kalibrasi */
    .label { border: 0.5pt dashed red; }
    @endif
</style>

@foreach($pages as $pageIndex => $page)
    <div class="page" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
        @foreach($page as $i => $slot)
            @if(!empty($slot) && isset($slot['item']))
                <?php $it = $slot['item']; ?>
                <div class="label" style="left: {{ $slot['x'] }}mm; top: {{ $slot['y'] }}mm; width: {{ $labelWidth }}mm; height: {{ $labelHeight }}mm;">
                    <div class="name">{{ substr($it->nama_barang ?? $it->nama, 0, 20) }}</div>

                    {{-- Barcode image (if generated) above the id number --}}
                    @if(!empty($slot['barcode']))
                        <img src="{{ $slot['barcode'] }}" class="barcode-image" alt="barcode" style="width: {{ max(0, $labelWidth - 8) }}mm; max-height:6mm;">
                        <div class="id-number">{{ $slot['id_value'] ?? ($it->id_barang ?? $it->id) }}</div>
                    @else
                        <div class="id-number">{{ $it->id_barang ?? $it->id }}</div>
                    @endif

                    <div class="price">Rp {{ number_format($it->harga ?? 0,0,',','.') }}</div>
                    
                    @if(!empty($calibrate))
                        <div class="slot-number">{{ $i + 1 }}</div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
@endforeach

@endsection