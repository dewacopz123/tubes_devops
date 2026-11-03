// ===== JAM REALTIME =====
function updateClock() {
    const now = new Date();
    document.getElementById("clock").innerText =
        now.toLocaleTimeString("id-ID");
    document.getElementById("date").innerText =
        now.toLocaleDateString("id-ID", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric"
        });
}
setInterval(updateClock, 1000);
updateClock();

// ===== GRAFIK ABSENSI =====
const absensiCtx = document.getElementById("absensiChart");

if (absensiCtx) {
    new Chart(absensiCtx, {
        type: "doughnut",
        data: {
            labels: ["Hadir", "Izin", "Alpha"],
            datasets: [{
                data: [
                    window.hadirHariIni,
                    window.izinHariIni,
                    window.alphaHariIni
                ],
                backgroundColor: ["#28a745", "#ffc107", "#dc3545"]
            }]
        }
    });
}

// ===== GRAFIK JOBDESK =====
const jobdeskCtx = document.getElementById("jobdeskChart");

if (jobdeskCtx) {
    new Chart(jobdeskCtx, {
        type: "bar",
        data: {
            labels: ["Aktif Bertugas", "Belum Ada Jobdesk"],
            datasets: [{
                label: "Jumlah Karyawan",
                data: [
                    window.karyawanPunyaJobdesk,
                    window.karyawanTanpaJobdesk
                ],
                backgroundColor: ["#17a2b8", "#fd7e14"]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}
