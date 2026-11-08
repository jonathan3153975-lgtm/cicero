export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const overlay = document.querySelector('[data-sidebar-close]');

    if (!sidebar || !toggle) {
        return;
    }

    const open = () => {
        sidebar.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sidebar-open');
    };

    const close = () => {
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sidebar-open');
    };

    toggle.addEventListener('click', () => {
        if (sidebar.classList.contains('is-open')) {
            close();
        } else {
            open();
        }
    });

    overlay?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            close();
        }
    });
}
