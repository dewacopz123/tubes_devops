<div class="modal-form-overlay">
    <div class="modal-form">
        <h3 id="modalTitle">Jobdesk</h3>
        <form id="formJobdesk">
            <input type="hidden" id="jobdesk_id" name="jobdesk_id" value="">

            <div class="group">
                <label for="nama_jobdesk">Nama Jobdesk</label>
                <input type="text" name="nama_jobdesk" id="nama_jobdesk" class="form-control" required>
                <div class="input-error" id="error-nama_jobdesk"></div>
            </div>

            <div class="group">
                <label for="tugas_utama">Tugas Utama</label>
                <input type="text" name="tugas_utama" id="tugas_utama" class="form-control" required>
                <div class="input-error" id="error-tugas_utama"></div>
            </div>

            <div class="group" id="assignedSection" style="display: none;">
                <span class="form-section-title" style="display:block; font-weight:600; margin-bottom:6px; color:#2d3748;">Daftar Karyawan yang Ditugaskan</span>
                <div id="assignedKaryawansEdit" class="assigned-list"></div>
                <div class="input-error" id="error-assigned_karyawans"></div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-jobdesk" id="btnSubmit">Simpan</button>
                <button type="button" class="btn-danger" data-close>Batal</button>
            </div>
        </form>
    </div>
</div>
