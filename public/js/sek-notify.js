(function () {
    function ensureContainer() {
        let container = document.getElementById("sek-toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "sek-toast-container";
            document.body.appendChild(container);
        }
        return container;
    }

    function show(type, message) {
        const container = ensureContainer();
        const toast = document.createElement("div");
        toast.className = `sek-toast sek-toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => toast.classList.add("show"), 20);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 250);
        }, 3200);
    }

    window.SEKNotify = {
        success(message) {
            show("success", message || "Berhasil.");
        },
        error(message) {
            show("error", message || "Terjadi kesalahan.");
        },
        flash(type, message) {
            sessionStorage.setItem("sekToast", JSON.stringify({ type, message }));
        },
    };

    document.addEventListener("DOMContentLoaded", function () {
        const raw = sessionStorage.getItem("sekToast");
        if (!raw) return;

        sessionStorage.removeItem("sekToast");
        try {
            const toast = JSON.parse(raw);
            show(toast.type || "success", toast.message || "Berhasil.");
        } catch (error) {
            show("success", raw);
        }
    });
})();
