document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("popupContainer");
    const dialog = document.getElementById("dialogContainer");
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const notify = window.SEKNotify;

    function openModal(html) {
        popup.innerHTML = html;
        popup.querySelectorAll("[data-close]").forEach(btn => {
            btn.onclick = () => popup.innerHTML = "";
        });
        handleSubmit();
    }

    function openDialog(html) {
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

        const confirmButton = dialog.querySelector("[data-dialog-confirm]");
        if (confirmButton) {
            confirmButton.onclick = async () => {
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

    function validationList(errors) {
        return Object.values(errors || {}).flat().join("<br>");
    }

    const btnAddKaryawan = document.getElementById("btnAddKaryawan");
    if (btnAddKaryawan) {
        btnAddKaryawan.onclick = async () => {
            const html = await fetch("/karyawan/form").then(r => r.text());
            openModal(html);

            document.getElementById("modalTitle").innerText = "Tambah Data Karyawan";
            document.getElementById("btnSubmit").innerText = "Simpan";
            document.getElementById("mode").value = "create";
            document.getElementById("formKaryawan").reset();
        };
    }

    document.querySelectorAll(".btnEdit").forEach(btn => {
        btn.onclick = async () => {
            const id = btn.dataset.id;
            const dataRes = await fetch(`/karyawan/${id}`, { headers: { Accept: "application/json" } });

            if (!dataRes.ok) {
                notify?.error("Gagal mengambil data karyawan.");
                return;
            }

            const data = await dataRes.json();
            const formHtml = await fetch("/karyawan/form").then(r => r.text());

            openModal(formHtml);

            document.getElementById("modalTitle").innerText = "Edit Data Karyawan";
            document.getElementById("btnSubmit").innerText = "Update";
            document.getElementById("mode").value = "edit";
            document.getElementById("karyawanId").value = id;
            document.getElementById("namaKaryawan").value = data.nama || "";
            document.getElementById("emailKaryawan").value = data.email || "";
            document.getElementById("teleponKaryawan").value = data.telepon || "";
            document.getElementById("roleKaryawan").value = data.role || "";
            document.getElementById("statusKaryawan").value = data.status || "";
        };
    });

    document.querySelectorAll(".btnDelete").forEach(btn => {
        btn.onclick = () => {
            showConfirmDialog("Hapus Data", "Yakin hapus data karyawan?", async () => {
                const res = await fetch(`/karyawan/${btn.dataset.id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                });
                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    showErrorDialog("Gagal", data.message || "Gagal menghapus data");
                    notify?.error(data.message || "Gagal menghapus data.");
                    return;
                }

                notify?.flash("success", data.message || "Data karyawan berhasil dihapus.");
                location.reload();
            });
        };
    });

    function handleSubmit() {
        const form = document.getElementById("formKaryawan");
        if (!form) return;

        const mode = document.getElementById("mode");
        const karyawanId = document.getElementById("karyawanId");
        const namaKaryawan = document.getElementById("namaKaryawan");
        const emailKaryawan = document.getElementById("emailKaryawan");
        const teleponKaryawan = document.getElementById("teleponKaryawan");
        const roleKaryawan = document.getElementById("roleKaryawan");
        const statusKaryawan = document.getElementById("statusKaryawan");

        function clearValidationErrors() {
            popup.querySelectorAll(".input-error").forEach(el => el.textContent = "");
            popup.querySelectorAll(".form-control").forEach(el => el.classList.remove("input-invalid"));
        }

        function showValidationErrors(errors) {
            const fieldMap = {
                nama: "error-namaKaryawan",
                email: "error-emailKaryawan",
                telepon: "error-teleponKaryawan",
                role: "error-roleKaryawan",
                status: "error-statusKaryawan",
            };

            Object.entries(errors || {}).forEach(([field, messages]) => {
                const errorElement = document.getElementById(fieldMap[field]);
                if (errorElement) errorElement.textContent = messages[0];

                const inputElement = document.getElementById(`${field}Karyawan`);
                if (inputElement) inputElement.classList.add("input-invalid");
            });
        }

        async function submitForm() {
            const isEdit = mode.value === "edit";
            const payload = {
                nama: namaKaryawan.value,
                email: emailKaryawan.value,
                telepon: teleponKaryawan.value,
                role: roleKaryawan.value,
                status: statusKaryawan.value,
            };

            const res = await fetch(isEdit ? `/karyawan/${karyawanId.value}` : "/karyawan", {
                method: isEdit ? "PUT" : "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (res.status === 422) {
                    showValidationErrors(data.errors);
                    showErrorDialog("Validasi Gagal", validationList(data.errors));
                    notify?.error("Validasi gagal. Periksa kembali data.");
                    return;
                }

                showErrorDialog("Gagal", data.message || "Gagal menyimpan data");
                notify?.error(data.message || "Gagal menyimpan data.");
                return;
            }

            notify?.flash(
                "success",
                data.message || (isEdit ? "Data karyawan berhasil diperbarui." : "Data karyawan berhasil ditambahkan.")
            );
            location.reload();
        }

        form.onsubmit = function (e) {
            e.preventDefault();
            clearValidationErrors();

            if (mode.value === "create") {
                showConfirmDialog("Tambah Karyawan", "Apakah Anda yakin ingin menyimpan data karyawan?", submitForm);
                return;
            }

            if (mode.value === "edit") {
                showConfirmDialog("Update Karyawan", "Apakah Anda yakin ingin memperbarui data karyawan?", submitForm);
            }
        };
    }

    const filterNama = document.getElementById("filterNama");
    const filterRole = document.getElementById("filterRole");

    function filterTable() {
        const nama = filterNama.value.toLowerCase();
        const role = filterRole.value.toLowerCase();

        document.querySelectorAll("tbody tr").forEach(row => {
            const rowNama = row.dataset.nama || "";
            const rowRole = row.dataset.role || "";

            row.style.display = (!nama || rowNama === nama) && (!role || rowRole === role) ? "" : "none";
        });
    }

    if (filterNama) filterNama.addEventListener("change", filterTable);
    if (filterRole) filterRole.addEventListener("change", filterTable);
});
