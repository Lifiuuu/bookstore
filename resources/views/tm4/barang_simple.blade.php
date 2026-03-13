@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h4>Tambah Barang (Simple Table)</h4>
        <div style="display:flex; gap:30px; align-items:flex-end; margin-bottom:20px;">
            <form id="barangForm" style="flex:1; max-width:800px;">
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:14px;">
                    <label style="width:150px;">Nama barang:</label>
                    <input id="nama" name="nama" type="text" required style="flex:1; padding:8px; border:2px solid #1e6091; border-radius:4px;" />
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <label style="width:150px;">Harga barang:</label>
                    <input id="harga" name="harga" type="number" required style="flex:1; padding:8px; border:2px solid #1e6091; border-radius:4px;" />
                </div>
            </form>

            <!-- Button placed outside the <form> to ensure JS triggers submit -->
            <div>
                <button id="submitBtn" type="button" style="background:#10a24a;color:#fff;border:none;padding:12px 26px;border-radius:10px;font-size:16px;cursor:pointer;">submit</button>
            </div>
        </div>

        <table id="barangTableSimple" style="width:100%; border-collapse:collapse; border:1px solid #000;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="border:1px solid #000; padding:8px;">ID barang</th>
                    <th style="border:1px solid #000; padding:8px;">Nama</th>
                    <th style="border:1px solid #000; padding:8px;">Harga</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<style>
    .spinner {
        border: 3px solid rgba(255,255,255,0.3);
        border-top: 3px solid #fff;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        display: inline-block;
        vertical-align: middle;
        margin-right:8px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Make rows look clickable */
    #barangTableSimple tbody tr.clickable-row { cursor: pointer; }

    /* Simple modal styles */
    #modalOverlay { position: fixed; inset:0; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:2000; }
    #editModal { background:#fff; padding:22px; border-radius:8px; width:420px; box-shadow:0 6px 30px rgba(0,0,0,0.2); }
    #editModal label { display:block; margin-bottom:6px; font-weight:600; }
    #editModal input { width:100%; padding:8px; border:2px solid #1e6091; border-radius:4px; margin-bottom:12px; }
    .modal-actions { display:flex; justify-content:space-between; gap:12px; }
    .btn-red { background:#e53935; color:#fff; border:none; padding:12px 24px; border-radius:12px; cursor:pointer; }
    .btn-green { background:#10a24a; color:#fff; border:none; padding:12px 24px; border-radius:12px; cursor:pointer; }
</style>

<div id="modalOverlay">
    <div id="editModal" role="dialog" aria-modal="true">
        <h4 style="margin-top:0;">Edit / Hapus</h4>
        <form id="modalForm" novalidate>
            <div>
                <label for="modal_id">ID barang:</label>
                <input id="modal_id" name="modal_id" type="text" readonly />
            </div>
            <div>
                <label for="modal_nama">Nama barang:</label>
                <input id="modal_nama" name="modal_nama" type="text" required />
            </div>
            <div>
                <label for="modal_harga">Harga barang:</label>
                <input id="modal_harga" name="modal_harga" type="number" required />
            </div>
        </form>
        <div class="modal-actions">
            <button id="deleteBtn" type="button" class="btn-red">Hapus</button>
            <button id="updateBtn" type="button" class="btn-green">Ubah</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('barangForm');
    const submitBtn = document.getElementById('submitBtn');
    const tbody = document.querySelector('#barangTableSimple tbody');
    let idCounter = 1;

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

    submitBtn.addEventListener('click', function () {
        // HTML5 validity check
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // valid -> show spinner
        setBtnLoading(submitBtn, true);

        // gather values
        const nama = form.nama.value.trim();
        const harga = form.harga.value.trim();

        // simulate asynchronous submit (no DB save required)
        setTimeout(() => {
            const id = idCounter++;
            const tr = document.createElement('tr');
            tr.classList.add('clickable-row');
            tr.dataset.itemId = id;
            tr.innerHTML = `<td style="border:1px solid #000; padding:6px;">${id}</td><td style="border:1px solid #000; padding:6px;">${escapeHtml(nama)}</td><td style="border:1px solid #000; padding:6px;">${escapeHtml(harga)}</td>`;
            tbody.appendChild(tr);

            // reset form
            form.reset();

            // restore button
            setBtnLoading(submitBtn, false);
            // focus first input again
            form.nama.focus();
        }, 700);
    });

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]); });
    }

    // Modal elements
    const modalOverlay = document.getElementById('modalOverlay');
    const modalForm = document.getElementById('modalForm');
    const modalId = document.getElementById('modal_id');
    const modalNama = document.getElementById('modal_nama');
    const modalHarga = document.getElementById('modal_harga');
    const updateBtn = document.getElementById('updateBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    let selectedRow = null;

    // Open modal for a given table row
    function openModalForRow(tr) {
        selectedRow = tr;
        const cells = tr.children;
        const id = tr.dataset.itemId || cells[0].textContent.trim();
        modalId.value = id;
        modalNama.value = cells[1].textContent.trim();
        modalHarga.value = cells[2].textContent.trim();
        modalOverlay.style.display = 'flex';
        modalNama.focus();
    }

    function closeModal() {
        modalOverlay.style.display = 'none';
        selectedRow = null;
        // clear validation state
        modalForm.querySelectorAll(':invalid').forEach(i => i.classList.remove('invalid'));
    }

    // delegated click handler for rows
    document.querySelector('#barangTableSimple tbody').addEventListener('click', function(e) {
        const tr = e.target.closest('tr');
        if (!tr) return;
        openModalForRow(tr);
    });

    // close modal when clicking outside
    modalOverlay.addEventListener('click', function(e){ if (e.target === modalOverlay) closeModal(); });

    // Update
    updateBtn.addEventListener('click', function(){
        if (!modalForm.checkValidity()) { modalForm.reportValidity(); return; }
        setBtnLoading(updateBtn, true);
        setTimeout(() => {
            if (selectedRow) {
                selectedRow.children[1].textContent = modalNama.value.trim();
                selectedRow.children[2].textContent = modalHarga.value.trim();
            }
            setBtnLoading(updateBtn, false);
            closeModal();
        }, 700);
    });

    // Delete
    deleteBtn.addEventListener('click', function(){
        if (!selectedRow) return;
        setBtnLoading(deleteBtn, true);
        setTimeout(() => {
            selectedRow.remove();
            setBtnLoading(deleteBtn, false);
            closeModal();
        }, 500);
    });
});
</script>
@endsection
