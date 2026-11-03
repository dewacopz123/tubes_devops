document.addEventListener("DOMContentLoaded", function () {

    const btnMasuk = document.getElementById("btnMasukKerja");
    const btnKeluar = document.getElementById("btnKeluarKerja");

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const notify = window.SEKNotify;
    const dialogContainer = document.getElementById("dialogContainer");

    function openDialog(html) {
        if (!dialogContainer) return;

        dialogContainer.innerHTML = html;

        dialogContainer
            .querySelectorAll("[data-dialog-close]")
            .forEach(btn => {
                btn.addEventListener("click", () => {
                    dialogContainer.innerHTML = "";
                });
            });
    }

    function showConfirmDialog(title, message, onConfirm) {
        openDialog(`
            <div class="dialog-overlay">
                <div class="dialog-box">
                    <h3>${title}</h3>

                    <p class="dialog-text">
                        ${message}
                    </p>

                    <div class="dialog-actions">
                        <button type="button"
                                class="btn btn-jobdesk"
                                data-dialog-confirm>
                            Ya
                        </button>

                        <button type="button"
                                class="btn btn-danger"
                                data-dialog-close>
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        `);

        const confirmButton = dialogContainer.querySelector(
            "[data-dialog-confirm]"
        );

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

                    <div class="dialog-error-list">
                        ${message}
                    </div>

                    <div class="dialog-actions">
                        <button type="button"
                                class="btn btn-jobdesk"
                                data-dialog-close>
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        `);
    }

    function kirimAbsensi(
        url,
        judulDialog,
        pesanDialog,
        pesanSukses
    ) {

        const submitAction = async () => {

            try {

                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    showErrorDialog(
                        "Gagal",
                        data.message || "Terjadi kesalahan."
                    );

                    notify?.error(
                        data.message || "Terjadi kesalahan."
                    );

                    return;
                }

                notify?.flash(
                    "success",
                    data.message || pesanSukses
                );

                location.reload();

            } catch (err) {

                console.error(err);

                showErrorDialog(
                    "Gagal",
                    err.message || "Terjadi kesalahan."
                );

                notify?.error(
                    err.message || "Terjadi kesalahan."
                );
            }
        };

        showConfirmDialog(
            judulDialog,
            pesanDialog,
            submitAction
        );
    }

    btnMasuk?.addEventListener("click", () => {

        kirimAbsensi(
            "/absensi/masuk",
            "Absen Masuk Kerja",
            "Apakah Anda yakin ingin melakukan absen masuk kerja?",
            "Berhasil absen masuk kerja."
        );

    });

    btnKeluar?.addEventListener("click", () => {

        kirimAbsensi(
            "/absensi/keluar",
            "Absen Selesai Kerja",
            "Apakah Anda yakin ingin melakukan absen selesai kerja?",
            "Berhasil absen selesai kerja."
        );

    });

});