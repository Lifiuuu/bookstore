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
        padding-top: 3mm; 
        padding-left: 1mm;
        padding-right: 1mm;
        text-align: center;
        overflow: hidden; 
    }
    .name { 
        font-weight: 600; 
        font-size: 6pt; 
        margin-bottom: 2px;
        line-height: 1;
    }
    .price { 
        font-weight: 700; 
        font-size: 7.5pt; 
        line-height: 1;
    }
    .slot-number { 
        position: absolute; 
        top: 1mm; 
        left: 1mm; 
        font-size: 5pt; 
        color: rgba(0,0,0,0.4); 
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