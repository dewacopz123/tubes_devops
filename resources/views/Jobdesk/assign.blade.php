<div class="modal-form-overlay">
    <div class="modal-form">
        <h3 id="modalTitle">Assign Jobdesk</h3>
        <form id="formAssignJobdesk">
            <div class="group">
                <label for="jobdesk_id">Pilih Jobdesk</label>
                <select id="jobdesk_id" name="jobdesk_id" class="form-control" required>
                    <option value="">Pilih Jobdesk</option>
                    @foreach ($jobdesks as $jobdesk)
                        <option value="{{ $jobdesk->id }}">{{ $jobdesk->kode_jobdesk }} - {{ $jobdesk->nama_jobdesk }}</option>
                    @endforeach
                </select>
                <div class="input-error" id="error-jobdesk_id"></div>
            </div>

            <div class="group">
                <label for="karyawan_select">Pilih Karyawan</label>
                <select id="karyawan_select" class="form-control">
                    <option value="">Pilih Karyawan</option>
                    @foreach ($karyawans as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
                <div class="input-error" id="error-karyawan_id"></div>
            </div>

            <div class="group">
                <span class="form-section-title" style="display:block; font-weight:600; margin-bottom:6px; color:#2d3748;">Karyawan yang Sudah Ditugaskan</span>
                <div id="existingAssignedKaryawans" class="assigned-list"></div>
            </div>

            <div class="group">
                <span class="form-section-title" style="display:block; font-weight:600; margin-bottom:6px; color:#2d3748;">Karyawan Baru untuk Ditambahkan</span>
                <div id="assignedKaryawans" class="assigned-list"></div>
                <div id="assignedKaryawanInputs"></div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-jobdesk" id="btnSubmit">Simpan</button>
                <button type="button" class="btn-danger" data-close>Batal</button>
            </div>
        </form>
    </div>
</div>
