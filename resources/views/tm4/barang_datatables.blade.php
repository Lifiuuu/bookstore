@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h4>Tambah Barang (DataTables)</h4>

        <div style="display:flex; gap:30px; align-items:flex-end; margin-bottom:20px;">
            <form id="barangFormDT" style="flex:1; max-width:800px;">
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:14px;">
                    <label style="width:150px;">Nama barang:</label>
                    <input id="nama_dt" name="nama" type="text" required style="flex:1; padding:8px; border:2px solid #1e6091; border-radius:4px;" />
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <label style="width:150px;">Harga barang:</label>
                    <input id="harga_dt" name="harga" type="number" required style="flex:1; padding:8px; border:2px solid #1e6091; border-radius:4px;" />
                </div>
            </form>

            <!-- Button outside form -->
            <div>
                <button id="submitBtnDT" type="button" style="background:#10a24a;color:#fff;border:none;padding:12px 26px;border-radius:10px;font-size:16px;cursor:pointer;">submit</button>
            </div>
        </div>

        <!-- DataTables CSS (CDN) -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

        <table id="barangTableDT" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID barang</th>
                    <th>Nama</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<style>
    .spinner { border: 3px solid rgba(255,255,255,0.3); border-top: 3px solid #fff; border-radius: 50%; width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right:8px; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* pointer on hover for rows */
    #barangTableDT tbody tr { cursor: pointer; }

    /* modal styles */
    #modalOverlayDT { position: fixed; inset:0; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:2000; }
    #editModalDT { background:#fff; padding:22px; border-radius:8px; width:420px; box-shadow:0 6px 30px rgba(0,0,0,0.2); }
    #editModalDT label { display:block; margin-bottom:6px; font-weight:600; }
    #editModalDT input { width:100%; padding:8px; border:2px solid #1e6091; border-radius:4px; margin-bottom:12px; }
    .modal-actions-dt { display:flex; justify-content:space-between; gap:12px; }
    .btn-red { background:#e53935; color:#fff; border:none; padding:12px 24px; border-radius:12px; cursor:pointer; }
    .btn-green { background:#10a24a; color:#fff; border:none; padding:12px 24px; border-radius:12px; cursor:pointer; }
</style>

<div id="modalOverlayDT">
    <div id="editModalDT" role="dialog" aria-modal="true">
        <h4 style="margin-top:0;">Edit / Hapus</h4>
        <form id="modalFormDT" novalidate>
            <div>
                <label for="modal_id_dt">ID barang:</label>
                <input id="modal_id_dt" name="modal_id_dt" type="text" readonly />
            </div>
            <div>
                <label for="modal_nama_dt">Nama barang:</label>
                <input id="modal_nama_dt" name="modal_nama_dt" type="text" required />
            </div>
            <div>
                <label for="modal_harga_dt">Harga barang:</label>
                <input id="modal_harga_dt" name="modal_harga_dt" type="number" required />
            </div>
        </form>
        <div class="modal-actions-dt">
            <button id="deleteBtnDT" type="button" class="btn-red">Hapus</button>
            <button id="updateBtnDT" type="button" class="btn-green">Ubah</button>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(function(){
        const form = document.getElementById('barangFormDT');
        const submitBtn = document.getElementById('submitBtnDT');
        let idCounter = 1;

        // initialize DataTable
        const table = $('#barangTableDT').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false
        });

        function setBtnLoading(btn, isLoading) {
            if (isLoading) {
                btn.disabled = true;
                btn.dataset.label = btn.innerHTML;
                btn.innerHTML = '<span class="spinner"></span>Processing...';
                btn.style.opacity = '0.9';
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.label || 'submit';
                btn.style.opacity = '1';
            }
        }

        submitBtn.addEventListener('click', function(){
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            setBtnLoading(submitBtn, true);

            const nama = form.nama.value.trim();
            const harga = form.harga.value.trim();

            setTimeout(() => {
                const id = idCounter++;
                table.row.add([id, escapeHtml(nama), escapeHtml(harga)]).draw(false);

                // mark the last added row with itemId (so modal can read it)
                const lastRow = $('#barangTableDT tbody tr').last()[0];
                if (lastRow) lastRow.dataset.itemId = id;

                form.reset();
                setBtnLoading(submitBtn, false);
                form.nama.focus();
            }, 700);
        });

        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); });
        }

        // Modal elements
        const modalOverlayDT = document.getElementById('modalOverlayDT');
        const modalFormDT = document.getElementById('modalFormDT');
        const modalIdDT = document.getElementById('modal_id_dt');
        const modalNamaDT = document.getElementById('modal_nama_dt');
        const modalHargaDT = document.getElementById('modal_harga_dt');
        const updateBtnDT = document.getElementById('updateBtnDT');
        const deleteBtnDT = document.getElementById('deleteBtnDT');
        let selectedRowDT = null;

        function openModalForRowDT(tr) {
            selectedRowDT = tr;
            const $tr = $(tr);
            const id = $tr.find('td').eq(0).text().trim();
            modalIdDT.value = id;
            modalNamaDT.value = $tr.find('td').eq(1).text().trim();
            modalHargaDT.value = $tr.find('td').eq(2).text().trim();
            modalOverlayDT.style.display = 'flex';
            modalNamaDT.focus();
        }

        function closeModalDT() {
            modalOverlayDT.style.display = 'none';
            selectedRowDT = null;
        }

        // delegated click handler for DataTable rows
        $('#barangTableDT tbody').on('click', 'tr', function(){
            openModalForRowDT(this);
        });

        // close when clicking outside
        modalOverlayDT.addEventListener('click', function(e){ if (e.target === modalOverlayDT) closeModalDT(); });

        // Update
        updateBtnDT.addEventListener('click', function(){
            if (!modalFormDT.checkValidity()) { modalFormDT.reportValidity(); return; }
            setBtnLoading(updateBtnDT, true);
            setTimeout(() => {
                if (selectedRowDT) {
                    const id = modalIdDT.value;
                    table.row(selectedRowDT).data([id, escapeHtml(modalNamaDT.value.trim()), escapeHtml(modalHargaDT.value.trim())]).draw(false);
                }
                setBtnLoading(updateBtnDT, false);
                closeModalDT();
            }, 700);
        });

        // Delete
        deleteBtnDT.addEventListener('click', function(){
            if (!selectedRowDT) return;
            setBtnLoading(deleteBtnDT, true);
            setTimeout(() => {
                table.row(selectedRowDT).remove().draw(false);
                setBtnLoading(deleteBtnDT, false);
                closeModalDT();
            }, 500);
        });

    });
    </script>
@endpush

@endsection
