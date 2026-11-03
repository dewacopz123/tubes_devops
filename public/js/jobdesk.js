document.addEventListener("DOMContentLoaded", function () {

    /* ===================== FILTER JOBDESK & KARYAWAN ===================== */
    const selectJobdesk = document.getElementById("searchJobdeskName");
    const selectKaryawan = document.getElementById("searchKaryawanName");
    const table = document.getElementById("jobdeskTable");
    if (table) {
        const rows = table.querySelectorAll("tbody tr");

        const jobdeskSet = new Set();
        const karyawanSet = new Set();

        rows.forEach(row => {
            const jobdeskCell = row.querySelector(".col-jobdesk");
            const karyawanCell = row.querySelector(".col-karyawan");

            if (jobdeskCell) jobdeskSet.add(jobdeskCell.textContent.trim());
            if (karyawanCell) {
                karyawanCell.textContent
                    .split(',')
                    .map(name => name.trim())
                    .filter(name => name)
                    .forEach(name => karyawanSet.add(name));
            }
        });

        jobdeskSet.forEach(name => {
            const opt = document.createElement("option");
            opt.value = name;
            opt.textContent = name;
            selectJobdesk.appendChild(opt);
        });

        karyawanSet.forEach(name => {
            const opt = document.createElement("option");
            opt.value = name;
            opt.textContent = name;
            selectKaryawan.appendChild(opt);
        });

        function filterTable() {
            const jobdeskVal = selectJobdesk.value;
            const karyawanVal = selectKaryawan.value;

            rows.forEach(row => {
                const jobdeskText = row.querySelector(".col-jobdesk")?.textContent.trim();
                const karyawanText = row.querySelector(".col-karyawan")?.textContent.trim();

                const karyawanNames = karyawanText
                    ? karyawanText.split(',').map(text => text.trim())
                    : [];

                const matchJobdesk = !jobdeskVal || jobdeskText === jobdeskVal;
                const matchKaryawan = !karyawanVal || karyawanNames.includes(karyawanVal);

                row.style.display = (matchJobdesk && matchKaryawan) ? "" : "none";
            });
        }

        selectJobdesk.addEventListener("change", filterTable);
        selectKaryawan.addEventListener("change", filterTable);
    }

    /* ===================== CRUD JOBDESK ===================== */
    const popupContainer = document.getElementById("popupContainer");
    const dialogContainer = document.getElementById("dialogContainer");
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const notify = window.SEKNotify;

    function removeModal(id) {
        const m = document.getElementById(id);
        if (m) m.remove();
    }

    function openDialog(html) {
        if (!dialogContainer) return;
        dialogContainer.innerHTML = html;
        dialogContainer.querySelectorAll("[data-dialog-close]").forEach(btn => {
            btn.addEventListener("click", () => dialogContainer.innerHTML = "");
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

        const confirmButton = dialogContainer.querySelector("[data-dialog-confirm]");
        if (confirmButton) {
            confirmButton.addEventListener("click", async () => {
                dialogContainer.innerHTML = "";
                await onConfirm();
            });
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

    function createModal(html, id) {
        removeModal(id);
        const modal = document.createElement("div");
        modal.id = id;
        modal.className = "modal";
        modal.innerHTML = html;
        popupContainer.appendChild(modal);

        modal.querySelectorAll("[data-close]").forEach(btn => {
            btn.addEventListener("click", () => modal.remove());
        });

        const jobdeskForm = modal.querySelector("#formJobdesk");
        const assignForm = modal.querySelector("#formAssignJobdesk");

        if (jobdeskForm) {
            const assignedSectionEdit = modal.querySelector("#assignedSection");
            const assignedKaryawansEdit = modal.querySelector("#assignedKaryawansEdit");

            const clearValidationErrors = () => {
                modal.querySelectorAll(".input-error").forEach(el => el.textContent = "");
                modal.querySelectorAll(".form-control").forEach(el => el.classList.remove("input-invalid"));
            };

            const showValidationErrors = (errors) => {
                Object.entries(errors || {}).forEach(([field, messages]) => {
                    const input = modal.querySelector(`[name="${field}"]`);
                    const error = modal.querySelector(`#error-${field.replace(/\./g, '_')}`);
                    if (input) input.classList.add("input-invalid");
                    if (error) error.textContent = messages[0];
                });
            };

            const renderAssignedKaryawansEdit = (karyawans) => {
                assignedKaryawansEdit.innerHTML = '';
                if (!karyawans || !karyawans.length) {
                    assignedSectionEdit.style.display = 'none';
                    return;
                }

                assignedSectionEdit.style.display = '';
                karyawans.forEach(karyawan => {
                    const badge = document.createElement('span');
                    badge.className = 'assigned-item';
                    badge.innerHTML = `${karyawan.nama} <button type="button" class="assigned-remove-edit" data-id="${karyawan.id}">&times;</button>`;
                    assignedKaryawansEdit.appendChild(badge);
                });
            };

            assignedKaryawansEdit?.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.assigned-remove-edit');
                if (!removeBtn) return;

                const jobdeskId = jobdeskForm.querySelector("#jobdesk_id").value;
                const karyawanId = removeBtn.dataset.id;
                if (!jobdeskId || !karyawanId) return;

                const removeAction = async () => {
                    try {
                        const res = await fetch(`/jobdesk/${jobdeskId}/karyawan/${karyawanId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            showErrorDialog('Gagal', data.message || 'Gagal menghapus karyawan dari jobdesk.');
                            notify?.error(data.message || 'Gagal menghapus karyawan dari jobdesk.');
                            return;
                        }

                        notify?.flash('success', data.message || 'Karyawan berhasil dihapus dari jobdesk.');
                        renderAssignedKaryawansEdit(data.data.karyawans || []);
                    } catch (err) {
                        console.error(err);
                        showErrorDialog('Gagal', 'Terjadi kesalahan saat menghapus karyawan dari jobdesk.');
                        notify?.error('Gagal menghapus karyawan dari jobdesk.');
                    }
                };

                showConfirmDialog('Hapus Karyawan', 'Apakah Anda yakin ingin menghapus karyawan ini dari jobdesk?', removeAction);
            });

            jobdeskForm.addEventListener("submit", function (e) {
                e.preventDefault();
                clearValidationErrors();

                const jobdeskId = jobdeskForm.querySelector("#jobdesk_id").value;
                const formData = new FormData(jobdeskForm);
                const url = jobdeskId ? `/jobdesk/${jobdeskId}` : "/jobdesk";

                if (jobdeskId) formData.append("_method", "PUT");

                const submitAction = async () => {
                    try {
                        const res = await fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Accept": "application/json",
                            },
                            body: formData
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            if (res.status === 422) {
                                showValidationErrors(data.errors);
                                notify?.error("Validasi gagal. Periksa kembali data jobdesk.");
                                return;
                            }

                            showErrorDialog("Gagal", data.message || "Gagal menyimpan jobdesk.");
                            notify?.error(data.message || "Gagal menyimpan jobdesk.");
                            return;
                        }

                        notify?.flash("success", data.message || "Jobdesk berhasil disimpan.");
                        location.reload();
                    } catch (err) {
                        console.error(err);
                        showErrorDialog("Gagal", "Terjadi kesalahan saat menyimpan jobdesk.");
                        notify?.error("Gagal menyimpan jobdesk.");
                    }
                };

                showConfirmDialog(jobdeskId ? "Update Jobdesk" : "Tambah Jobdesk", jobdeskId ? "Apakah Anda yakin ingin memperbarui data jobdesk?" : "Apakah Anda yakin ingin menyimpan jobdesk baru?", submitAction);
            });
        }

        if (assignForm) {
            const selectJobdesk = assignForm.querySelector("#jobdesk_id");
            const selectKaryawan = assignForm.querySelector("#karyawan_select");
            const assignedKaryawans = assignForm.querySelector("#assignedKaryawans");
            const assignedKaryawanInputs = assignForm.querySelector("#assignedKaryawanInputs");
            const existingAssignedKaryawans = assignForm.querySelector("#existingAssignedKaryawans");

            let existingAssignedIds = [];
            let currentJobdeskId = null;

            const getSelectedKaryawanIds = () => {
                return Array.from(assignedKaryawanInputs.querySelectorAll("input[name='karyawan_id[]']")).map(input => input.value);
            };

            const renderExistingAssignedKaryawans = (karyawans) => {
                existingAssignedKaryawans.innerHTML = '';
                if (!karyawans || !karyawans.length) {
                    existingAssignedKaryawans.innerHTML = '<p class="text-muted">Belum ada karyawan yang ditugaskan pada jobdesk ini.</p>';
                    return;
                }

                karyawans.forEach(karyawan => {
                    const badge = document.createElement('span');
                    badge.className = 'assigned-item assigned-existing';
                    badge.textContent = karyawan.nama;
                    existingAssignedKaryawans.appendChild(badge);
                });
            };

            const renderAssignedKaryawans = () => {
                assignedKaryawans.innerHTML = '';
                getSelectedKaryawanIds().forEach(id => {
                    const option = selectKaryawan.querySelector(`option[value="${id}"]`);
                    const name = option ? option.textContent : 'Unknown';
                    const badge = document.createElement('span');
                    badge.className = 'assigned-item';
                    badge.innerHTML = `${name} <button type="button" class="assigned-remove" data-id="${id}">&times;</button>`;
                    assignedKaryawans.appendChild(badge);
                });
            };

            const addKaryawanToJobdesk = () => {
                const selectedId = selectKaryawan.value;
                if (!selectedId) return;

                if (!currentJobdeskId) {
                    notify?.error('Pilih jobdesk terlebih dahulu.');
                    selectKaryawan.value = '';
                    return;
                }

                const currentIds = getSelectedKaryawanIds();
                if (currentIds.includes(selectedId) || existingAssignedIds.includes(selectedId)) {
                    notify?.error('Karyawan sudah ditugaskan pada jobdesk ini.');
                    selectKaryawan.value = '';
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'karyawan_id[]';
                input.value = selectedId;
                assignedKaryawanInputs.appendChild(input);
                renderAssignedKaryawans();
                selectKaryawan.value = '';
            };

            const loadJobdeskAssignments = async (jobdeskId) => {
                existingAssignedIds = [];
                renderExistingAssignedKaryawans([]);

                if (!jobdeskId) {
                    currentJobdeskId = null;
                    return;
                }

                currentJobdeskId = jobdeskId;
                try {
                    const res = await fetch(`/jobdesk/${jobdeskId}`);
                    if (!res.ok) return;
                    const data = await res.json();

                    existingAssignedIds = data.karyawans?.map(k => String(k.id)) || [];
                    renderExistingAssignedKaryawans(data.karyawans || []);
                } catch (err) {
                    console.error(err);
                }
            };

            selectJobdesk.addEventListener('change', function () {
                loadJobdeskAssignments(this.value);
            });

            selectKaryawan.addEventListener('change', addKaryawanToJobdesk);

            assignedKaryawans.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.assigned-remove');
                if (!removeBtn) return;

                const removeId = removeBtn.dataset.id;
                const input = assignedKaryawanInputs.querySelector(`input[value="${removeId}"]`);
                if (input) input.remove();
                renderAssignedKaryawans();
            });

            const clearValidationErrors = () => {
                assignForm.querySelectorAll(".input-error").forEach(el => el.textContent = "");
                assignForm.querySelectorAll(".form-control").forEach(el => el.classList.remove("input-invalid"));
            };

            const showValidationErrors = (errors) => {
                Object.entries(errors || {}).forEach(([field, messages]) => {
                    let input = assignForm.querySelector(`[name="${field}"]`);
                    if (!input && field.startsWith('karyawan_id')) {
                        input = selectKaryawan;
                    }

                    let error = assignForm.querySelector(`#error-${field.replace(/\./g, '_')}`);
                    if (!error && field.startsWith('karyawan_id')) {
                        error = assignForm.querySelector('#error-karyawan_id');
                    }

                    if (input) input.classList.add("input-invalid");
                    if (error) error.textContent = messages[0];
                });
            };

            assignForm.addEventListener("submit", function (e) {
                e.preventDefault();
                clearValidationErrors();

                const formData = new FormData(assignForm);
                const url = "/jobdesk/assign";

                const submitAction = async () => {
                    try {
                        const res = await fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Accept": "application/json",
                            },
                            body: formData
                        });

                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            if (res.status === 422) {
                                showValidationErrors(data.errors);
                                notify?.error("Validasi gagal. Periksa kembali assignment.");
                                return;
                            }

                            showErrorDialog("Gagal", data.message || "Gagal assign jobdesk.");
                            notify?.error(data.message || "Gagal assign jobdesk.");
                            return;
                        }

                        notify?.flash("success", data.message || "Jobdesk berhasil diassign.");
                        location.reload();
                    } catch (err) {
                        console.error(err);
                        showErrorDialog("Gagal", "Terjadi kesalahan saat assign jobdesk.");
                        notify?.error("Gagal assign jobdesk.");
                    }
                };

                showConfirmDialog("Assign Jobdesk", "Apakah Anda yakin ingin menugaskan karyawan ke jobdesk ini?", submitAction);
            });
        }

        return modal;
    }

    const btnAddJobdesk = document.getElementById("btnAddJobdesk");
    if (btnAddJobdesk) {
        btnAddJobdesk.addEventListener("click", async () => {
            const res = await fetch("/jobdesk/form");
            const html = await res.text();
            createModal(html, "modal-add");
        });
    }

    const btnAssignJobdesk = document.getElementById("btnAssignJobdesk");
    if (btnAssignJobdesk) {
        btnAssignJobdesk.addEventListener("click", async () => {
            const res = await fetch("/jobdesk/assign/form");
            const html = await res.text();
            createModal(html, "modal-assign");
        });
    }

    document.addEventListener("click", async function (e) {
        const editBtn = e.target.closest(".btnEdit");
        if (editBtn) {
            const row = editBtn.closest("tr");
            const id = row.dataset.id;

            const resForm = await fetch("/jobdesk/form");
            const html = await resForm.text();
            const modal = createModal(html, "modal-edit");

            const resData = await fetch(`/jobdesk/${id}`);
            if (!resData.ok) {
                showErrorDialog("Gagal", "Gagal mengambil data jobdesk.");
                notify?.error("Gagal mengambil data jobdesk.");
                return;
            }
            const data = await resData.json();

            modal.querySelector("#jobdesk_id").value = id;
            modal.querySelector("[name='nama_jobdesk']").value = data.nama_jobdesk;
            modal.querySelector("[name='tugas_utama']").value = data.tugas_utama;

            const assignedSectionEdit = modal.querySelector("#assignedSection");
            const assignedKaryawansEdit = modal.querySelector("#assignedKaryawansEdit");
            if (assignedKaryawansEdit) {
                const karyawans = data.karyawans || [];
                if (!karyawans.length) {
                    assignedSectionEdit.style.display = 'none';
                } else {
                    assignedSectionEdit.style.display = '';
                    assignedKaryawansEdit.innerHTML = '';
                    karyawans.forEach(karyawan => {
                        const badge = document.createElement('span');
                        badge.className = 'assigned-item';
                        badge.innerHTML = `${karyawan.nama} <button type="button" class="assigned-remove-edit" data-id="${karyawan.id}">&times;</button>`;
                        assignedKaryawansEdit.appendChild(badge);
                    });
                }
            }
        }
    });

    document.addEventListener("click", async function (e) {
        const deleteBtn = e.target.closest(".btnDelete");
        if (deleteBtn) {
            const row = deleteBtn.closest("tr");
            const id = row.dataset.id;

            showConfirmDialog("Hapus Jobdesk", "Yakin ingin menghapus jobdesk ini?", async () => {
                const res = await fetch(`/jobdesk/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                    }
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    showErrorDialog("Gagal", data.message || "Gagal menghapus jobdesk.");
                    notify?.error(data.message || "Gagal menghapus jobdesk.");
                    return;
                }
                notify?.flash("success", data.message || "Jobdesk berhasil dihapus.");
                location.reload();
            });
        }
    });

});
