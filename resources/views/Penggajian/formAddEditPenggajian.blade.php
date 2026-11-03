<div class="modal-form-overlay">
    <div class="modal-form">

        <h3 id="modalTitle">Tambah Penggajian</h3>

        <form id="formPenggajian">
            @csrf

            <input type="hidden" id="mode" value="create">
            <input type="hidden" id="penggajian_id">

            <div class="form-group">
                <label>Nama Karyawan</label>
                <select id="karyawan_id" class="form-control">
                    <option value="">Pilih Karyawan</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
                <div class="input-error" id="error-karyawan_id"></div>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input
        type="date"
        id="tanggal"
        class="form-control"
    >
                <div class="input-error" id="error-tanggal"></div>
            </div>

            <div class="form-group">
                <label>Gaji Pokok</label>
                <input type="number" id="gaji_pokok" class="form-control">
                <div class="input-error" id="error-gaji_pokok"></div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-jobdesk">Simpan</button>
                <button type="button" class="btn-danger" data-close>Batal</button>
            </div>
        </form>

    </div>
</div>
