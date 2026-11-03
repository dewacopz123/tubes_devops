(function(){
    async function loadNavbar(placeholder) {
        const src = placeholder.dataset.src;
        if (!src) return;
        try {
            const res = await fetch(src, {cache: 'no-store'});
            if (!res.ok) throw new Error('Failed to load: ' + res.status);
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            doc.querySelectorAll('script').forEach(s => s.remove());

            const sidebar = doc.querySelector('#sidebar');
            const topnav = doc.querySelector('.top-navbar');

            if (sidebar) {
                placeholder.insertAdjacentElement('beforebegin', sidebar);
            }
            if (topnav) {
                placeholder.insertAdjacentElement('beforebegin', topnav);
            }

            if (!sidebar && !topnav) {
                const bodyChildren = Array.from(doc.body.children);
                bodyChildren.forEach(child => placeholder.parentNode.insertBefore(child, placeholder));
            }

            placeholder.remove();
        } catch (err) {
            console.error('load_navbar error:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        const placeholders = document.querySelectorAll('#navbar-placeholder');
        placeholders.forEach(ph => loadNavbar(ph));
    });
})();
