@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h4>Kota — Select Demo</h4>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Select</div>
            <div class="card-body">
                <form id="kotaFormSimple" onsubmit="return false;">
                    <div style="display:flex; gap:12px; align-items:flex-end;">
                        <div style="flex:1;">
                            <label for="kotaInputSimple">Kota:</label>
                            <input id="kotaInputSimple" name="kota" type="text" required class="form-control" style="height:56px;" />
                        </div>
                        <div>
                            <button id="addKotaBtnSimple" type="button" class="btn btn-success" style="height:56px; min-width:140px;">Tambahkan</button>
                        </div>
                    </div>
                </form>

                <div class="mb-3 mt-3">
                    <label for="kotaSelectSimple">Select Kota:</label>
                    <select id="kotaSelectSimple" class="form-control">
                        <option value="">-- Pilih Kota --</option>
                    </select>
                </div>

                <div>
                    <strong>Kota Terpilih:</strong>
                    <div id="kotaTerpilihSimple" style="min-height:30px; padding-top:6px">(tidak ada)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">select 2</div>
            <div class="card-body">
                <form id="kotaForm2" onsubmit="return false;">
                    <div style="display:flex; gap:12px; align-items:flex-end;">
                        <div style="flex:1;">
                            <label for="kotaInput2">Kota:</label>
                            <input id="kotaInput2" name="kota" type="text" required class="form-control" style="height:56px;" />
                        </div>
                        <div>
                            <button id="addKotaBtn2" type="button" class="btn btn-success" style="height:56px; min-width:140px;">Tambahkan</button>
                        </div>
                    </div>
                </form>

                <div class="mb-3 mt-3">
                    <label for="kotaSelect2">Select Kota:</label>
                    <select id="kotaSelect2" class="form-control" style="width:100%">
                        <option value="">-- Pilih Kota --</option>
                    </select>
                </div>

                <div>
                    <strong>Kota Terpilih:</strong>
                    <div id="kotaTerpilih2" style="min-height:30px; padding-top:6px">(tidak ada)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CSS (include near the select so styling is applied) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Make native selects match the taller input and vertically center text */
    #kotaSelectSimple, #kotaSelect2 {
        height: 56px;
        padding: 10px 12px;
        box-sizing: border-box;
        line-height: 1.2;
    }

    /* Select2: force container and rendered text to match height */
    .select2-container--default .select2-selection--single {
        height: 56px !important;
        padding: 6px 12px;
        box-sizing: border-box;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important; /* centers text inside the 56px container */
        padding-left: 2px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 56px !important;
        top: 0 !important;
        right: 8px !important;
    }

    /* Small tweak so native select arrow aligns better */
    #kotaSelectSimple { padding-right: 32px; }
</style>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        // --- Simple select card ---
        const inputSimple = document.getElementById('kotaInputSimple');
        const addBtnSimple = document.getElementById('addKotaBtnSimple');
        const selectSimple = document.getElementById('kotaSelectSimple');
        const chosenSimple = document.getElementById('kotaTerpilihSimple');

        function updateChosenSimple(){
            const v = selectSimple.value;
            chosenSimple.textContent = v ? v : '(tidak ada)';
        }

        addBtnSimple.addEventListener('click', function(){
            if (!inputSimple.checkValidity()) { inputSimple.reportValidity(); return; }
            const val = inputSimple.value.trim();
            if (!val) return;

            // avoid duplicates
            for (let i=0;i<selectSimple.options.length;i++){
                if (selectSimple.options[i].value === val){
                    selectSimple.value = val;
                    updateChosenSimple();
                    inputSimple.value = '';
                    inputSimple.focus();
                    return;
                }
            }

            const option = document.createElement('option');
            option.value = val;
            option.textContent = val;
            selectSimple.appendChild(option);
            selectSimple.value = val;
            updateChosenSimple();
            inputSimple.value = '';
            inputSimple.focus();
        });

        selectSimple.addEventListener('change', updateChosenSimple);
        updateChosenSimple();

        // --- Select2 card ---
        $('#kotaSelect2').select2({ placeholder: '-- Pilih Kota --', allowClear: true });

        const input2 = document.getElementById('kotaInput2');
        const addBtn2 = document.getElementById('addKotaBtn2');

        function updateChosen2(){
            const val = $('#kotaSelect2').val();
            document.getElementById('kotaTerpilih2').textContent = val ? val : '(tidak ada)';
        }

        addBtn2.addEventListener('click', function(){
            if (!input2.checkValidity()) { input2.reportValidity(); return; }
            const val = input2.value.trim();
            if (!val) return;

            // duplicate check
            if ($('#kotaSelect2 option[value="'+val+'"]').length){
                $('#kotaSelect2').val(val).trigger('change');
                updateChosen2();
                input2.value = '';
                input2.focus();
                return;
            }

            const newOption = new Option(val, val, true, true);
            $('#kotaSelect2').append(newOption).trigger('change');
            updateChosen2();
            input2.value = '';
            input2.focus();
        });

        $('#kotaSelect2').on('change', updateChosen2);
        updateChosen2();
    });
    </script>
@endpush

@endsection
