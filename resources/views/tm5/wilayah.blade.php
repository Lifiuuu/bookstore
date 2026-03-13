@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="container">
        <h2 class="mb-4">Cascading Select Wilayah Administrasi Indonesia</h2>

        <style>
            /* Page-specific adjustments */
            .card .form-label { font-weight: 600; }
            .form-control { min-width: 220px; }
            @media (max-width: 576px) { .form-control { min-width: 100%; } }
        </style>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Versi Ajax (jQuery)</h5>
                <p class="card-text">Event yang digunakan: <strong>change</strong></p>

                <form class="row g-3">
                    <div class="col-md-6">
                        <label for="provinsi_ajax" class="form-label">Provinsi</label>
                        <select id="provinsi_ajax" class="form-control"><option value="0">Pilih Provinsi</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kota_ajax" class="form-label">Kota / Kabupaten</label>
                        <select id="kota_ajax" class="form-control"><option value="0">Pilih Kota</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kecamatan_ajax" class="form-label">Kecamatan</label>
                        <select id="kecamatan_ajax" class="form-control"><option value="0">Pilih Kecamatan</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kelurahan_ajax" class="form-label">Kelurahan / Desa</label>
                        <select id="kelurahan_ajax" class="form-control"><option value="0">Pilih Kelurahan</option></select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Versi Axios</h5>
                <p class="card-text">Event yang digunakan: <strong>change</strong></p>

                <form class="row g-3">
                    <div class="col-md-6">
                        <label for="provinsi_axios" class="form-label">Provinsi</label>
                        <select id="provinsi_axios" class="form-control"><option value="0">Pilih Provinsi</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kota_axios" class="form-label">Kota / Kabupaten</label>
                        <select id="kota_axios" class="form-control"><option value="0">Pilih Kota</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kecamatan_axios" class="form-label">Kecamatan</label>
                        <select id="kecamatan_axios" class="form-control"><option value="0">Pilih Kecamatan</option></select>
                    </div>
                    <div class="col-md-6">
                        <label for="kelurahan_axios" class="form-label">Kelurahan / Desa</label>
                        <select id="kelurahan_axios" class="form-control"><option value="0">Pilih Kelurahan</option></select>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Helper: populate select with items [{id,name}]
    function populate($sel, items, placeholderText) {
        $sel.empty();
        $sel.append($('<option>').val('0').text('Pilih ' + placeholderText));
        items.forEach(function(it){
            $sel.append($('<option>').val(it.id).text(it.name));
        });
    }

    // -------------------- AJAX (jQuery) --------------------
    $(function(){
        // load provinces
        $.getJSON('/api/wilayah/provinces', function(data){
            populate($('#provinsi_ajax'), data, 'Provinsi');
        });

        $('#provinsi_ajax').on('change', function(){
            var prov = $(this).val();
            // clear downstream
            populate($('#kecamatan_ajax'), [], 'Kecamatan');
            populate($('#kelurahan_ajax'), [], 'Kelurahan');
            if (!prov || prov === '0') { populate($('#kota_ajax'), [], 'Kota'); return; }
            $.getJSON('/api/wilayah/regencies', { province_id: prov }, function(kotas){
                populate($('#kota_ajax'), kotas, 'Kota');
            });
        });

        $('#kota_ajax').on('change', function(){
            var reg = $(this).val();
            populate($('#kelurahan_ajax'), [], 'Kelurahan');
            if (!reg || reg === '0') { populate($('#kecamatan_ajax'), [], 'Kecamatan'); return; }
            $.getJSON('/api/wilayah/districts', { regency_id: reg }, function(kecs){
                populate($('#kecamatan_ajax'), kecs, 'Kecamatan');
            });
        });

        $('#kecamatan_ajax').on('change', function(){
            var kec = $(this).val();
            if (!kec || kec === '0') { populate($('#kelurahan_ajax'), [], 'Kelurahan'); return; }
            $.getJSON('/api/wilayah/villages', { district_id: kec }, function(vils){
                populate($('#kelurahan_ajax'), vils, 'Kelurahan');
            });
        });
    });

    // -------------------- Axios --------------------
    (function(){
        axios.get('/api/wilayah/provinces').then(function(resp){
            populate($('#provinsi_axios'), resp.data, 'Provinsi');
        }).catch(console.error);

        $('#provinsi_axios').on('change', function(){
            var prov = $(this).val();
            populate($('#kecamatan_axios'), [], 'Kecamatan');
            populate($('#kelurahan_axios'), [], 'Kelurahan');
            if (!prov || prov === '0') { populate($('#kota_axios'), [], 'Kota'); return; }
            axios.get('/api/wilayah/regencies', { params: { province_id: prov } }).then(function(resp){
                populate($('#kota_axios'), resp.data, 'Kota');
            }).catch(console.error);
        });

        $('#kota_axios').on('change', function(){
            var reg = $(this).val();
            populate($('#kelurahan_axios'), [], 'Kelurahan');
            if (!reg || reg === '0') { populate($('#kecamatan_axios'), [], 'Kecamatan'); return; }
            axios.get('/api/wilayah/districts', { params: { regency_id: reg } }).then(function(resp){
                populate($('#kecamatan_axios'), resp.data, 'Kecamatan');
            }).catch(console.error);
        });

        $('#kecamatan_axios').on('change', function(){
            var kec = $(this).val();
            if (!kec || kec === '0') { populate($('#kelurahan_axios'), [], 'Kelurahan'); return; }
            axios.get('/api/wilayah/villages', { params: { district_id: kec } }).then(function(resp){
                populate($('#kelurahan_axios'), resp.data, 'Kelurahan');
            }).catch(console.error);
        });
    })();
</script>
@endpush

