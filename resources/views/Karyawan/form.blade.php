<div class="modal-form-overlay">
    <div class="modal-form">
        <h3 id="modalTitle">Tambah Data Karyawan</h3>

        <form id="formKaryawan" class="form-karyawan">
            <!-- hidden untuk mode & id -->
            <input type="hidden" id="mode" value="create">
            <input type="hidden" id="karyawanId">

            <div class="form-group">
                <label for="namaKaryawan">Nama</label>
                <input type="text" id="namaKaryawan" class="form-control" required />
                <div class="input-error" id="error-namaKaryawan"></div>
            </div>

            <div class="form-group">
                <label for="emailKaryawan">Email</label>
                <input type="email" id="emailKaryawan" class="form-control" required />
                <div class="input-error" id="error-emailKaryawan"></div>
            </div>

            <div class="form-group">
                <label for="teleponKaryawan">Telepon</label>
                <input type="number" id="teleponKaryawan" class="form-control" />
                <div class="input-error" id="error-teleponKaryawan"></div>
            </div>

            <div class="form-group">
                <label for="roleKaryawan">Role</label>
                <select id="roleKaryawan" class="form-control" required>
                    <option value="">Pilih Role</option>
                    <option value="karyawan">Karyawan</option>
                    <option value="admin">Admin</option>
                </select>
                <div class="input-error" id="error-roleKaryawan"></div>
            </div>

            <div class="form-group">
                <label for="statusKaryawan">Status</label>
                <select id="statusKaryawan" class="form-control" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
                <div class="input-error" id="error-statusKaryawan"></div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-jobdesk" id="btnSubmit">Simpan</button>
                <button type="button" class="btn-danger" data-close>Batal</button>
            </div>
        </form>
    </div>
</div>