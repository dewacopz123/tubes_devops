document.addEventListener("DOMContentLoaded", () => {
    const popup  = document.getElementById("popupContainer");
    const dialog = document.getElementById("dialogContainer");
    const csrf   = document.querySelector('meta[name="csrf-token"]').content;
    const notify = window.SEKNotify;

    // ================= DIALOG HELPERS =================

    function openDialog(html) {
        if (!dialog) return;
        dialog.innerHTML = html;
        dialog.querySelectorAll("[data-dialog-close]").forEach(btn => {
            btn.onclick = () => dialog.innerHTML = "";
        });
    }

    function showConfirmDialog(title, message, onConfirm) {
        openDialog(`
            <div class="dialog-overlay">
                <div class="dialog-box">
                    <h3>${title}</h3>
                    <p class="dialog-text">${message}</p>
                    <div class="dialog-actions">
                        <button type="button" class="btn btn-jobdesk" data-dialog-confirm>Ya</button>
                        <button type="button" class="btn btn-danger" data-dialog-close>Batal</button>
                    </div>
                </div>
            </div>
        `);
        const confirmBtn = dialog.querySelector("[data-dialog-confirm]");
        if (confirmBtn) {
            confirmBtn.onclick = async () => {
                dialog.innerHTML = "";
                await onConfirm();
            };
        }
    }

    function showErrorDialog(title, message) {
        openDialog(`
            <div class="dialog-overlay">
                <div class="dialog-box">
                    <h3>${title}</h3>
                    <div class="dialog-error-list">${message}</div>
                    <div class="dialog-actions">
                        <button type="button" class="btn btn-jobdesk" data-dialog-close>Tutup</button>
                    </div>
                </div>
            </div>
        `);
    }

    // ================= MODAL =================

    function openModal(html) {
        popup.innerHTML = html;
        popup.querySelectorAll('[data-close]').forEach(btn => {
            btn.onclick = () => popup.innerHTML = '';
        });
        handleSubmit();
    }

    // ================= TAMBAH =================

    const btnAdd = document.getElementById("btnAddPenggajian");
    if (btnAdd) {
        btnAdd.onclick = async () => {
            const html = await fetch('/penggajian/create').then(r => r.text());
            openModal(html);
            document.getElementById("modalTitle").innerText = "Tambah Penggajian";
            document.getElementById("mode").value = "create";
            document.getElementById("formPenggajian").reset();
        };
    }

    // ================= EDIT =================

    document.querySelectorAll(".btnEdit").forEach(btn => {
        btn.onclick = async () => {
            const id = btn.dataset.id;
            const dataRes = await fetch(`/penggajian/${id}`, { headers: { "Accept": "application/json" } });
            if (!dataRes.ok) {
                notify?.error("Gagal mengambil data penggajian.");
                return;
            }
            const data = await dataRes.json();
            const html = await fetch('/penggajian/create').then(r => r.text());
            openModal(html);
            document.getElementById("modalTitle").innerText = "Edit Penggajian";
            document.getElementById("mode").value = "edit";
            document.getElementById("penggajian_id").value = id;
            document.getElementById("karyawan_id").value = data.karyawan_id;
            document.getElementById("tanggal").value = data.tanggal;
            document.getElementById("gaji_pokok").value = data.gaji_pokok;
        };
    });

    // ================= DELETE =================

    document.querySelectorAll(".btnDelete").forEach(btn => {
        btn.onclick = () => {
            showConfirmDialog(
                "Hapus Data",
                "Apakah Anda yakin ingin menghapus data penggajian ini?",
                async () => {
                    const res = await fetch(`/penggajian/${btn.dataset.id}`, {
                        method: "DELETE",
                        headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        showErrorDialog("Gagal", data.message || "Gagal menghapus data penggajian.");
                        notify?.error(data.message || "Gagal menghapus data penggajian.");
                        return;
                    }
                    notify?.flash("success", data.message || "Data penggajian berhasil dihapus.");
                    location.reload();
                }
            );
        };
    });

    // ================= SUBMIT (dengan konfirmasi) =================

    function handleSubmit() {
        const form = document.getElementById("formPenggajian");
        if (!form) return;

        function clearValidationErrors() {
            popup.querySelectorAll(".input-error").forEach(el => el.textContent = "");
            popup.querySelectorAll(".form-control").forEach(el => el.classList.remove("input-invalid"));
        }

        function showValidationErrors(errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = document.getElementById(field);
                const error = document.getElementById(`error-${field}`);
                if (input) input.classList.add("input-invalid");
                if (error) error.textContent = messages[0];
            });
        }

        async function doSubmit() {
            const mode = document.getElementById("mode").value;
            const id   = document.getElementById("penggajian_id").value;

            const data = new FormData();
            data.append("karyawan_id", document.getElementById("karyawan_id").value);
            data.append("tanggal",     document.getElementById("tanggal").value);
            data.append("gaji_pokok",  document.getElementById("gaji_pokok").value);

            let url = "/penggajian";
            if (mode === "edit") {
                url += "/" + id;
                data.append("_method", "PUT");
            }

            const res = await fetch(url, {
                method: "POST",
                headers: { "x-csrf-token": csrf, "Accept": "application/json" },
                body: data
            });
            const result = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (res.status === 422) {
                    showValidationErrors(result.errors);
                    showErrorDialog("Validasi Gagal",
                        Object.values(result.errors || {}).flat().join("<br>"));
                    notify?.error("Validasi gagal. Periksa kembali data penggajian.");
                    return;
                }
                showErrorDialog("Gagal", result.message || "Gagal menyimpan data.");
                notify?.error(result.message || "Gagal menyimpan data penggajian.");
                return;
            }

            notify?.flash("success", result.message || "Data penggajian berhasil disimpan.");
            location.reload();
        }

        form.onsubmit = (e) => {
            e.preventDefault();
            clearValidationErrors();

            const mode = document.getElementById("mode").value;
            const title   = mode === "edit" ? "Update Penggajian" : "Tambah Penggajian";
            const message = mode === "edit"
                ? "Apakah Anda yakin ingin memperbarui data penggajian ini?"
                : "Apakah Anda yakin ingin menyimpan data penggajian baru?";

            showConfirmDialog(title, message, doSubmit);
        };
    }

    // ================= FILTER =================

    const filterNama    = document.getElementById("filterNama");
    const filterTanggal = document.getElementById("filterTanggal");
    const rows          = document.querySelectorAll(".table-absensi tbody tr");

    function filterTable() {
        const namaVal    = filterNama?.value.toLowerCase() || "";
        const tanggalVal = filterTanggal?.value || "";

        rows.forEach(row => {
            const rowNama    = row.dataset.nama    || "";
            const rowTanggal = row.dataset.tanggal || "";
            const okNama     = !namaVal    || rowNama.includes(namaVal);
            const okTanggal  = !tanggalVal || rowTanggal === tanggalVal;
            row.style.display = (okNama && okTanggal) ? "" : "none";
        });
    }

    if (filterNama)    filterNama.addEventListener("change", filterTable);
    if (filterTanggal) filterTanggal.addEventListener("change", filterTable);
});
