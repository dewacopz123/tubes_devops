document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formProfile");
    const btnEdit = document.getElementById("btnEdit");
    const btnSave = document.getElementById("btnSave");
    const btnCancel = document.getElementById("btnCancel");
    const dialog = document.getElementById("dialogContainer");
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const notify = window.SEKNotify;

    if (!form) return;

    // Semua input yang bisa diedit
    const editableInputs = form.querySelectorAll("input[name]");

    // Simpan nilai awal untuk reset saat batal
    let originalValues = {};
    editableInputs.forEach(input => {
        originalValues[input.name] = input.value;
    });

    // ================= MODE DISPLAY (default) =================

    function setDisplayMode() {
        editableInputs.forEach(input => {
            input.disabled = true;
            input.classList.remove("input-invalid");
        });

        clearErrors();

        btnEdit.style.display = "inline-block";
        btnSave.style.display = "none";
        btnCancel.style.display = "none";

        const btnUploadPhoto =
            document.getElementById("btnUploadPhoto");

        if (btnUploadPhoto) {
            btnUploadPhoto.style.display = "none";
        }
    }

    function setEditMode() {
        editableInputs.forEach(input => {
            input.disabled = false;
        });

        btnEdit.style.display = "none";
        btnSave.style.display = "inline-block";
        btnCancel.style.display = "inline-block";

        const btnUploadPhoto =
            document.getElementById("btnUploadPhoto");

        if (btnUploadPhoto) {
            btnUploadPhoto.style.display = "inline-block";
        }
    }

    // Mulai di mode display
    setDisplayMode();

    // ================= TOMBOL EDIT =================

    btnEdit.addEventListener("click", setEditMode);

    // ================= TOMBOL BATAL =================

    btnCancel.addEventListener("click", function () {
        // Kembalikan nilai ke semula
        editableInputs.forEach(input => {
            if (originalValues[input.name] !== undefined) {
                input.value = originalValues[input.name];
            }
        });
        setDisplayMode();
    });

    // ================= DIALOG HELPERS =================

    function openDialog(html) {
        if (!dialog) return;
        dialog.innerHTML = html;
        dialog.querySelectorAll("[data-dialog-close]").forEach(btn => {
            btn.onclick = () => dialog.innerHTML = "";
        });
    }

    function closeDialog() {
        if (dialog) dialog.innerHTML = "";
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
                closeDialog();
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

    // ================= VALIDASI ERROR INLINE =================

    function clearErrors() {
        form.querySelectorAll(".input-error").forEach(el => el.textContent = "");
        form.querySelectorAll(".input-invalid").forEach(el => el.classList.remove("input-invalid"));
    }

    function showValidationErrors(errors) {
        const fieldMap = {
            nama: "error-nama",
            email: "error-email",
            telepon: "error-telepon",
        };

        Object.entries(errors || {}).forEach(([field, messages]) => {
            const errorEl = document.getElementById(fieldMap[field]);
            if (errorEl) errorEl.textContent = messages[0];

            const inputEl = document.getElementById("input" + field.charAt(0).toUpperCase() + field.slice(1));
            if (inputEl) inputEl.classList.add("input-invalid");
        });
    }

    // ================= SUBMIT =================

    async function doSave() {
        const payload = {
            nama: form.querySelector("[name='nama']").value,
            email: form.querySelector("[name='email']").value,
            telepon: form.querySelector("[name='telepon']").value,
        };

        const res = await fetch(form.action, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json",
            },
            body: JSON.stringify({ _method: "POST", ...payload }),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            if (res.status === 422) {
                showValidationErrors(data.errors);
                showErrorDialog(
                    "Validasi Gagal",
                    Object.values(data.errors || {}).flat().join("<br>")
                );
                notify?.error("Validasi gagal. Periksa kembali data.");
                return;
            }
            showErrorDialog("Gagal", data.message || "Gagal menyimpan profile.");
            notify?.error(data.message || "Gagal menyimpan profile.");
            return;
        }

        // Update nilai original setelah berhasil
        editableInputs.forEach(input => {
            originalValues[input.name] = input.value;
        });

        setDisplayMode();
        notify?.flash("success", data.message || "Profile berhasil diperbarui.");
        location.reload();
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearErrors();

        showConfirmDialog(
            "Simpan Profile",
            "Apakah Anda yakin ingin menyimpan perubahan profile?",
            doSave
        );
    });

    // ================= UPLOAD FOTO =================

    const photoInput = document.getElementById("photoInput");
    const profilePhoto = document.getElementById("profilePhoto");
    const btnUploadPhoto = document.getElementById("btnUploadPhoto");

    if (btnUploadPhoto && photoInput && profilePhoto) {

        btnUploadPhoto.addEventListener("click", () => {
            photoInput.click();
        });

        photoInput.addEventListener("change", async function () {

            const file = this.files[0];

            if (!file) return;

            // Validasi client-side
            const allowedTypes = [
                "image/jpeg",
                "image/jpg",
                "image/png"
            ];

            if (!allowedTypes.includes(file.type)) {
                notify?.error(
                    "File harus berupa JPG, JPEG, atau PNG."
                );
                this.value = "";
                return;
            }

            // Maksimal 2 MB
            if (file.size > 2 * 1024 * 1024) {
                notify?.error(
                    "Ukuran foto maksimal 2 MB."
                );
                this.value = "";
                return;
            }

            const formData = new FormData();
            formData.append("foto", file);

            try {

                btnUploadPhoto.disabled = true;
                btnUploadPhoto.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Uploading...';

                const response = await fetch(
                    "/profile/upload-photo",
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            "Accept": "application/json"
                        },
                        body: formData
                    }
                );

                let result = {};

                try {
                    result = await response.json();
                } catch {
                    result = {};
                }

                if (!response.ok) {

                    if (response.status === 422) {

                        const errors = Object.values(
                            result.errors || {}
                        ).flat();

                        notify?.error(
                            errors.join("\n") ||
                            "Validasi upload gagal."
                        );

                    } else {

                        notify?.error(
                            result.message ||
                            "Upload foto gagal."
                        );
                    }

                    return;
                }

                if (result.success) {

                    const photoUrl =
                        result.foto_url +
                        "?t=" +
                        Date.now();

                    profilePhoto.src = photoUrl;

                    const navbarProfileImage =
                        document.getElementById("navbarProfileImage");

                    if (navbarProfileImage) {
                        navbarProfileImage.src = photoUrl;
                    }

                    notify?.flash(
                        "success",
                        "Foto profil berhasil diperbarui"
                    );
                } else {

                    notify?.error(
                        result.message ||
                        "Upload foto gagal."
                    );
                }

            } catch (error) {

                console.error(error);

                notify?.error(
                    "Terjadi kesalahan saat mengupload foto."
                );

            } finally {

                btnUploadPhoto.disabled = false;
                btnUploadPhoto.innerHTML =
                    '<i class="fas fa-camera"></i> Ganti Foto';

                photoInput.value = "";
            }
        });
    }
});
