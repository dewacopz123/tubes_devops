/* ==========================================================
   navbar.js — Sidebar, Dialog, Logout
   ========================================================== */

/* ================= NAVBAR TITLE ================= */

function initNavbarTitle() {
  const navbarTitle = document.getElementById("navbar-page-title");
  if (!navbarTitle) return;

  const activeItem = document.querySelector(".sidebar-nav ul li.active span");
  if (activeItem) {
    navbarTitle.textContent = activeItem.textContent.trim();
  }
}

/* ================= SIDEBAR ACTIVE MENU ================= */

function initSidebarActiveMenu() {
  const currentPath = window.location.pathname.replace(/\/$/, "") || "/";
  const menuLinks   = document.querySelectorAll(".sidebar-nav ul li a");

  document.querySelectorAll(".sidebar-nav ul li")
    .forEach((li) => li.classList.remove("active"));

  let activeItem = Array.from(menuLinks).find((link) => {
    const href = link.getAttribute("href");
    if (!href || href === "#" || href === "javascript:void(0)") return false;

    const url  = new URL(href, window.location.origin);
    const path = url.pathname.replace(/\/$/, "") || "/";

    if (path === currentPath) return true;
    if (currentPath === "/" && path === "/dashboard") return true;
    return false;
  });

  if (!activeItem && currentPath !== "/") {
    activeItem = Array.from(menuLinks).find((link) => {
      const href = link.getAttribute("href");
      if (!href) return false;
      const url  = new URL(href, window.location.origin);
      const path = url.pathname.replace(/\/$/, "") || "/";
      return currentPath.startsWith(path) && path !== "/";
    });
  }

  if (activeItem) {
    activeItem.closest("li")?.classList.add("active");
  }
}

/* ================= SIDEBAR TOGGLE ================= */

function updateMobileBodyState() {
  const sidebar  = document.querySelector(".sidebar");
  const isMobile = window.innerWidth <= 900;
  const isExpanded = sidebar && !sidebar.classList.contains("collapsed");
  document.body.classList.toggle("mobile-sidebar-open", isMobile && isExpanded);
}

function initSidebarToggle() {
  const sidebar   = document.querySelector(".sidebar");
  const toggleBtn = document.getElementById("sidebar-toggle");
  if (!sidebar || !toggleBtn) return;

  if (localStorage.getItem("sidebarState") === "collapsed") {
    sidebar.classList.add("collapsed");
  }

  // Hapus class anti-flash dari <html> setelah sidebar siap
  document.documentElement.classList.remove("sidebar-will-collapse");

  updateMobileBodyState();

  toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("collapsed");
    const state = sidebar.classList.contains("collapsed") ? "collapsed" : "expanded";
    localStorage.setItem("sidebarState", state);
    // Set cookie agar server bisa pre-render sidebar state yang benar (anti-flash)
    document.cookie = "sidebarState=" + state + ";path=/;max-age=31536000";
    updateMobileBodyState();
  });

  window.addEventListener("resize", updateMobileBodyState);
}

/* ================= DIALOG ================= */

function closeDialog() {
  const el = document.getElementById("__navbarDialog");
  if (el) el.remove();
}

function showConfirmDialog(title, message, onConfirm) {
  closeDialog();

  const overlay = document.createElement("div");
  overlay.id        = "__navbarDialog";
  overlay.className = "dialog-overlay";
  overlay.innerHTML = `
    <div class="dialog-box">
      <h3>${title}</h3>
      <p class="dialog-text">${message}</p>
      <div class="dialog-actions">
        <button type="button" class="btn btn-jobdesk" id="__dialogConfirm">Ya</button>
        <button type="button" class="btn btn-danger"  id="__dialogCancel">Batal</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);

  document.getElementById("__dialogConfirm").onclick = function () {
    closeDialog();
    onConfirm();
  };
  document.getElementById("__dialogCancel").onclick = closeDialog;
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeDialog();
  });
}

/* ================= LOGOUT ================= */

function initLogoutConfirmation() {
  const logoutForm = document.getElementById("logoutForm");
  if (!logoutForm) return;

  logoutForm.addEventListener("submit", function (e) {
    e.preventDefault();
    showConfirmDialog(
      "Logout",
      "Apakah Anda yakin ingin keluar dari sistem?",
      () => logoutForm.submit()
    );
  });
}

/* ================= INIT ================= */

document.addEventListener("DOMContentLoaded", function () {
  initSidebarActiveMenu();
  initNavbarTitle();
  initSidebarToggle();
  initLogoutConfirmation();
});
