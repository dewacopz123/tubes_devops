<div class="modal-form-overlay">
    <div class="modal-form">
        <h3>Tambah Jobdesk</h3>
        <form id="formJobdesk" class="form-jobdesk">
            <div class="form-group">
                <label for="idJobdesk">ID Jobdesk</label>
                <input id="idJobdesk" name="idJobdesk" class="form-control" required />
            </div>
            <div class="form-group">
                <label for="namaJobdesk">Nama Jobdesk</label>
                <select id="namaJobdesk" name="namaJobdesk" class="form-control" required>
                    <option value="">Pilih Jobdesk</option>
                </select>
            </div>
            <div class="form-group">
                <label for="selectNamaKaryawan">Nama Karyawan</label>
                <select id="selectNamaKaryawan" name="namaKaryawan" class="form-control" required>
                    <option value="">Pilih Karyawan</option>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" class="btn-jobdesk">Simpan</button>
                <button type="button" class="btn-danger" data-close>Batal</button>
            </div>
        </form>
    </div>
</div>
