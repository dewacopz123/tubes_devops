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

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
    }

    // ================= SUMMARY FINANSIAL MODAL =================

    const btnSummary = document.getElementById("btnSummaryFinansial");
    if (btnSummary) {
        btnSummary.onclick = async () => {
            btnSummary.disabled = true;
            btnSummary.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

            try {
                const res = await fetch('/penggajian/summary', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    notify?.error("Gagal memuat ringkasan finansial.");
                    return;
                }

                const s = data.summary;
                const isAdmin = data.role === 'admin';

                let contentHtml = '';
                if (isAdmin) {
                    contentHtml = `
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:12px; margin: 16px 0;">
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#166534; text-transform:uppercase;">Total Pengeluaran Kas</div>
                                <div style="font-size:18px; font-weight:bold; color:#15803d; margin-top:4px;">Rp ${formatRupiah(s.total_pengeluaran_gaji)}</div>
                                <div style="font-size:10px; color:#65a30d; margin-top:2px;">Akumulasi gaji pokok + bonus</div>
                            </div>
                            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#1e40af; text-transform:uppercase;">Total Gaji Pokok</div>
                                <div style="font-size:18px; font-weight:bold; color:#2563eb; margin-top:4px;">Rp ${formatRupiah(s.total_gaji_pokok)}</div>
                                <div style="font-size:10px; color:#60a5fa; margin-top:2px;">Beban gaji pokok karyawan</div>
                            </div>
                            <div style="background:#fefce8; border:1px solid #fef08a; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#854d0e; text-transform:uppercase;">Bonus Kehadiran</div>
                                <div style="font-size:18px; font-weight:bold; color:#ca8a04; margin-top:4px;">Rp ${formatRupiah(s.total_bonus_dibagikan)}</div>
                                <div style="font-size:10px; color:#a16207; margin-top:2px;">Insentif kehadiran selesai</div>
                            </div>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#475569; text-transform:uppercase;">Rata-rata Gaji</div>
                                <div style="font-size:18px; font-weight:bold; color:#334155; margin-top:4px;">Rp ${formatRupiah(s.rata_rata_gaji)}</div>
                                <div style="font-size:10px; color:#64748b; margin-top:2px;">Berdasarkan ${s.total_slip_diterbitkan} slip gaji</div>
                            </div>
                        </div>
                    `;
                } else {
                    contentHtml = `
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:12px; margin: 16px 0;">
                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#166534; text-transform:uppercase;">Total Pendapatan Bersih</div>
                                <div style="font-size:18px; font-weight:bold; color:#15803d; margin-top:4px;">Rp ${formatRupiah(s.total_pendapatan)}</div>
                                <div style="font-size:10px; color:#65a30d; margin-top:2px;">Total take home pay diterima</div>
                            </div>
                            <div style="background:#fefce8; border:1px solid #fef08a; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#854d0e; text-transform:uppercase;">Bonus Kehadiran Anda</div>
                                <div style="font-size:18px; font-weight:bold; color:#ca8a04; margin-top:4px;">Rp ${formatRupiah(s.total_bonus_diterima)}</div>
                                <div style="font-size:10px; color:#a16207; margin-top:2px;">Dari keaktifan presensi selesai</div>
                            </div>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                                <div style="font-size:11px; font-weight:bold; color:#475569; text-transform:uppercase;">Rata-rata per Bulan</div>
                                <div style="font-size:18px; font-weight:bold; color:#334155; margin-top:4px;">Rp ${formatRupiah(s.rata_rata_gaji)}</div>
                                <div style="font-size:10px; color:#64748b; margin-top:2px;">Dari ${s.total_slip} transaksi slip</div>
                            </div>
                        </div>
                    `;
                }

                openDialog(`
                    <div class="dialog-overlay">
                        <div class="dialog-box" style="width: min(540px, 95%);">
                            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:12px;">
                                <h3 style="margin:0; font-size:18px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                                    <i class="fas fa-chart-pie" style="color:#2563eb;"></i> Ringkasan Finansial Penggajian
                                </h3>
                                <span style="font-size:11px; font-weight:bold; padding:4px 8px; border-radius:6px; background:${isAdmin ? '#dbeafe; color:#1e40af;' : '#dcfce7; color:#166534;'}">
                                    ${isAdmin ? 'Peran: Admin' : 'Peran: Karyawan'}
                                </span>
                            </div>
                            <p style="font-size:12px; color:#64748b; margin:0 0 10px 0;">
                                ${isAdmin ? 'Rekapitulasi beban biaya penggajian seluruh karyawan perusahaan.' : 'Rekapitulasi akumulasi slip gaji dan insentif kehadiran pribadi Anda.'}
                            </p>
                            ${contentHtml}
                            <div class="dialog-actions" style="margin-top:16px;">
                                <button type="button" class="btn btn-jobdesk" data-dialog-close>Tutup</button>
                            </div>
                        </div>
                    </div>
                `);
            } catch (err) {
                notify?.error("Terjadi kesalahan saat memuat summary.");
            } finally {
                btnSummary.disabled = false;
                btnSummary.innerHTML = '<i class="fas fa-chart-line"></i> Summary Finansial';
            }
        };
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
            const bonusField = document.getElementById("bonus");
            if (bonusField) bonusField.value = data.bonus ?? "";
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
            const bonusInput = document.getElementById("bonus");
            if (bonusInput && bonusInput.value !== "") {
                data.append("bonus", bonusInput.value);
            }
            const hitungOtomatis = document.getElementById("hitung_otomatis_bonus");
            if (hitungOtomatis && hitungOtomatis.checked) {
                data.append("hitung_otomatis_bonus", "1");
            }

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
