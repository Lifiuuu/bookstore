@extends('layouts.app')

@section('content')
<div class="py-4">
  <div class="container">
    <h2 class="mb-4">Point Of Sales (POS) - Kasir</h2>

    <div class="row">
      <div class="col-lg-6">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Versi Ajax (jQuery)</h5>

            <div class="mb-3">
              <label class="form-label">Kode barang</label>
              <input id="kode_ajax" class="form-control" placeholder="Masukkan kode lalu tekan Enter">
            </div>

            <div class="mb-3">
              <label class="form-label">Nama barang</label>
              <input id="nama_ajax" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Harga barang</label>
              <input id="harga_ajax" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Jumlah</label>
              <input id="jumlah_ajax" type="number" min="1" class="form-control" value="1">
            </div>

            <div class="d-flex justify-content-end">
              <button id="tambah_ajax" class="btn btn-success" disabled>Tambahkan</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Keranjang (Ajax)</h5>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="table_ajax_body"></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <strong>Total: Rp <span id="total_ajax">0</span></strong>
              <button id="bayar_ajax" class="btn btn-primary" disabled>Bayar</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Versi Axios</h5>

            <div class="mb-3">
              <label class="form-label">Kode barang</label>
              <input id="kode_axios" class="form-control" placeholder="Masukkan kode lalu tekan Enter">
            </div>

            <div class="mb-3">
              <label class="form-label">Nama barang</label>
              <input id="nama_axios" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Harga barang</label>
              <input id="harga_axios" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Jumlah</label>
              <input id="jumlah_axios" type="number" min="1" class="form-control" value="1">
            </div>

            <div class="d-flex justify-content-end">
              <button id="tambah_axios" class="btn btn-success" disabled>Tambahkan</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Keranjang (Axios)</h5>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="table_axios_body"></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <strong>Total: Rp <span id="total_axios">0</span></strong>
              <button id="bayar_axios" class="btn btn-primary" disabled>Bayar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// CSRF for ajax + axios
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

function formatNumber(n){ return Number(n).toLocaleString(); }

// ---------- AJAX implementation ----------
$(function(){
  var foundAjax = false;

  function updateTotalAjax(){
    var total = 0;
    $('#table_ajax_body tr').each(function(){
      total += Number($(this).find('.row-subtotal').text());
    });
    $('#total_ajax').text(formatNumber(total));
    $('#bayar_ajax').prop('disabled', total === 0);
  }

  function resetInputAjax(){
    $('#kode_ajax').val('');
    $('#nama_ajax').val('');
    $('#harga_ajax').val('');
    $('#jumlah_ajax').val(1);
    foundAjax = false;
    $('#tambah_ajax').prop('disabled', true);
    $('#kode_ajax').focus();
  }

  // lookup on Enter
  $('#kode_ajax').on('keydown', function(e){
    if (e.key === 'Enter'){
      var code = $(this).val().trim();
      if (!code) return;
      $.getJSON('/api/barang/lookup', { code: code })
        .done(function(data){
          if (!data){
            Swal.fire('Tidak ditemukan', 'Barang tidak ditemukan', 'warning');
            resetInputAjax();
            return;
          }
          $('#nama_ajax').val(data.nama);
          $('#harga_ajax').val(data.harga);
          $('#jumlah_ajax').val(1);
          foundAjax = true;
          $('#tambah_ajax').prop('disabled', false);
        }).fail(function(xhr){
          if (xhr.status === 204) {
            Swal.fire('Tidak ditemukan', 'Barang tidak ditemukan', 'warning');
            resetInputAjax();
          } else {
            Swal.fire('Error', 'Terjadi kesalahan saat lookup', 'error');
          }
        });
    }
  });

  $('#jumlah_ajax').on('input', function(){
    var v = Number($(this).val());
    $('#tambah_ajax').prop('disabled', !foundAjax || v <= 0);
  });

  $('#tambah_ajax').on('click', function(){
    var code = $('#kode_ajax').val().trim();
    var name = $('#nama_ajax').val();
    var price = Number($('#harga_ajax').val()) || 0;
    var qty = Math.max(1, parseInt($('#jumlah_ajax').val()) || 1);
    if (!foundAjax || !code) return;

    var $existing = $('#table_ajax_body').find('tr[data-code="'+code+'"]');
    if ($existing.length){
      var $qty = $existing.find('.row-qty');
      var newQty = Number($qty.val()) + qty;
      $qty.val(newQty);
      $existing.find('.row-subtotal').text(newQty * price);
    } else {
      var subtotal = qty * price;
      var row = '<tr data-code="'+code+'">'
        + '<td class="row-code">'+code+'</td>'
        + '<td class="row-name">'+name+'</td>'
        + '<td class="row-price">'+price+'</td>'
        + '<td><input type="number" min="1" class="form-control form-control-sm row-qty" value="'+qty+'"></td>'
        + '<td class="row-subtotal">'+subtotal+'</td>'
        + '<td><button class="btn btn-sm btn-danger btn-remove">Hapus</button></td>'
        + '</tr>';
      $('#table_ajax_body').append(row);
    }

    resetInputAjax();
    updateTotalAjax();
  });

  // delegated handlers for qty change and remove
  $('#table_ajax_body').on('change', '.row-qty', function(){
    var $tr = $(this).closest('tr');
    var price = Number($tr.find('.row-price').text()) || 0;
    var qty = Math.max(1, Number($(this).val()) || 1);
    $(this).val(qty);
    $tr.find('.row-subtotal').text(qty * price);
    updateTotalAjax();
  });

  $('#table_ajax_body').on('click', '.btn-remove', function(){
    $(this).closest('tr').remove();
    updateTotalAjax();
  });

  $('#bayar_ajax').on('click', function(){
    var items = []; var total = 0;
    $('#table_ajax_body tr').each(function(){
      var code = $(this).data('code');
      var name = $(this).find('.row-name').text();
      var price = Number($(this).find('.row-price').text()) || 0;
      var qty = Number($(this).find('.row-qty').val()) || 0;
      var subtotal = Number($(this).find('.row-subtotal').text()) || 0;
      items.push({ code: code, name: name, price: price, qty: qty, subtotal: subtotal });
      total += subtotal;
    });
    if (items.length === 0) return;

    $.ajax({
      url: '/api/pos/checkout',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ items: items, total: total }),
      success: function(resp){
        Swal.fire('Sukses', 'Transaksi berhasil disimpan', 'success');
        $('#table_ajax_body').empty();
        updateTotalAjax();
      },
      error: function(){
        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan transaksi', 'error');
      }
    });
  });
});

// ---------- AXIOS implementation ----------
(function(){
  var found = false;

  function updateTotalAxios(){
    var total = 0;
    $('#table_axios_body tr').each(function(){
      total += Number($(this).find('.row-subtotal').text());
    });
    $('#total_axios').text(formatNumber(total));
    $('#bayar_axios').prop('disabled', total === 0);
  }

  function resetInputAxios(){
    $('#kode_axios').val('');
    $('#nama_axios').val('');
    $('#harga_axios').val('');
    $('#jumlah_axios').val(1);
    found = false;
    $('#tambah_axios').prop('disabled', true);
    $('#kode_axios').focus();
  }

  $('#kode_axios').on('keydown', function(e){
    if (e.key === 'Enter'){
      var code = $(this).val().trim();
      if (!code) return;
      axios.get('/api/barang/lookup', { params: { code: code } })
        .then(function(res){
          var data = res.data;
          if (!data){ Swal.fire('Tidak ditemukan', 'Barang tidak ditemukan', 'warning'); resetInputAxios(); return; }
          $('#nama_axios').val(data.nama);
          $('#harga_axios').val(data.harga);
          $('#jumlah_axios').val(1);
          found = true; $('#tambah_axios').prop('disabled', false);
        }).catch(function(err){
          if (err.response && err.response.status === 204) { Swal.fire('Tidak ditemukan', 'Barang tidak ditemukan', 'warning'); resetInputAxios(); }
          else Swal.fire('Error', 'Terjadi kesalahan saat lookup', 'error');
        });
    }
  });

  $('#jumlah_axios').on('input', function(){
    var v = Number($(this).val());
    $('#tambah_axios').prop('disabled', !found || v <= 0);
  });

  $('#tambah_axios').on('click', function(){
    var code = $('#kode_axios').val().trim();
    var name = $('#nama_axios').val();
    var price = Number($('#harga_axios').val()) || 0;
    var qty = Math.max(1, parseInt($('#jumlah_axios').val()) || 1);
    if (!found || !code) return;

    var $existing = $('#table_axios_body').find('tr[data-code="'+code+'"]');
    if ($existing.length){
      var $qty = $existing.find('.row-qty');
      var newQty = Number($qty.val()) + qty;
      $qty.val(newQty);
      $existing.find('.row-subtotal').text(newQty * price);
    } else {
      var subtotal = qty * price;
      var row = '<tr data-code="'+code+'">'
        + '<td class="row-code">'+code+'</td>'
        + '<td class="row-name">'+name+'</td>'
        + '<td class="row-price">'+price+'</td>'
        + '<td><input type="number" min="1" class="form-control form-control-sm row-qty" value="'+qty+'"></td>'
        + '<td class="row-subtotal">'+subtotal+'</td>'
        + '<td><button class="btn btn-sm btn-danger btn-remove">Hapus</button></td>'
        + '</tr>';
      $('#table_axios_body').append(row);
    }

    resetInputAxios();
    updateTotalAxios();
  });

  // delegated handlers
  $('#table_axios_body').on('change', '.row-qty', function(){
    var $tr = $(this).closest('tr');
    var price = Number($tr.find('.row-price').text()) || 0;
    var qty = Math.max(1, Number($(this).val()) || 1);
    $(this).val(qty);
    $tr.find('.row-subtotal').text(qty * price);
    updateTotalAxios();
  });

  $('#table_axios_body').on('click', '.btn-remove', function(){
    $(this).closest('tr').remove();
    updateTotalAxios();
  });

  $('#bayar_axios').on('click', function(){
    var items = []; var total = 0;
    $('#table_axios_body tr').each(function(){
      var code = $(this).data('code');
      var name = $(this).find('.row-name').text();
      var price = Number($(this).find('.row-price').text()) || 0;
      var qty = Number($(this).find('.row-qty').val()) || 0;
      var subtotal = Number($(this).find('.row-subtotal').text()) || 0;
      items.push({ code: code, name: name, price: price, qty: qty, subtotal: subtotal });
      total += subtotal;
    });
    if (items.length === 0) return;

    axios.post('/api/pos/checkout', { items: items, total: total })
      .then(function(resp){
        Swal.fire('Sukses', 'Transaksi berhasil disimpan', 'success');
        $('#table_axios_body').empty(); updateTotalAxios();
      }).catch(function(){
        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan transaksi', 'error');
      });
  });

})();
</script>
@endpush
