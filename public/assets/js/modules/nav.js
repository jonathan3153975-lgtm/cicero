const MOBILE_QUERY = '(max-width: 900px)';
const COLLAPSE_KEY = 'academia-sidebar-collapsed';
const GROUP_KEY_PREFIX = 'academia-nav-group-';

function readPreference(key) {
    try {
        return window.localStorage.getItem(key);
    } catch (error) {
        return null;
    }
}

function writePreference(key, value) {
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        /* preferência indisponível */
    }
}

export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const collapse = document.querySelector('[data-sidebar-collapse]');
    const overlay = document.querySelector('[data-sidebar-close]');

    if (!sidebar || !toggle) {
        return;
    }

    const media = window.matchMedia(MOBILE_QUERY);
    const isMobile = () => media.matches;

    const syncToggleState = () => {
        if (isMobile()) {
            const open = sidebar.classList.contains('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
            sidebar.inert = !open;
            return;
        }

        const collapsed = document.body.classList.contains('sidebar-collapsed');
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Abrir menu' : 'Recolher menu');
        sidebar.inert = collapsed;
    };

    const openMobile = () => {
        sidebar.classList.add('is-open');
        document.body.classList.add('sidebar-open');
        syncToggleState();
    };

    const closeMobile = () => {
        sidebar.classList.remove('is-open');
        document.body.classList.remove('sidebar-open');
        syncToggleState();
    };

    const setDesktopCollapsed = (collapsed, persist = true) => {
        document.body.classList.toggle('sidebar-collapsed', collapsed);

        if (persist) {
            writePreference(COLLAPSE_KEY, collapsed ? '1' : '0');
        }

        syncToggleState();
    };

    const applyDesktopPreference = () => {
        if (isMobile()) {
            document.body.classList.remove('sidebar-collapsed');
            closeMobile();
            return;
        }

        closeMobile();
        setDesktopCollapsed(readPreference(COLLAPSE_KEY) === '1', false);
    };

    toggle.addEventListener('click', () => {
        if (isMobile()) {
            if (sidebar.classList.contains('is-open')) {
                closeMobile();
            } else {
                openMobile();
            }

            return;
        }

        setDesktopCollapsed(!document.body.classList.contains('sidebar-collapsed'));
    });

    collapse?.addEventListener('click', () => {
        if (isMobile()) {
            closeMobile();
            return;
        }

        setDesktopCollapsed(true);
    });

    overlay?.addEventListener('click', closeMobile);

    sidebar.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobile()) {
                closeMobile();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isMobile()) {
            closeMobile();
        }
    });

    media.addEventListener('change', applyDesktopPreference);
    applyDesktopPreference();

    document.querySelectorAll('[data-nav-group]').forEach((group) => {
        const groupToggle = group.querySelector('[data-nav-group-toggle]');
        const panel = group.querySelector('.nav-group__items');

        if (!groupToggle || !panel) {
            return;
        }

        const key = GROUP_KEY_PREFIX + group.dataset.navGroup;
        const stored = readPreference(key);
        const isCurrent = group.classList.contains('is-current');
        const shouldOpen = isCurrent || stored === '1';

        const setOpen = (open, persist = true) => {
            group.classList.toggle('is-open', open);
            groupToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = !open;

            if (persist) {
                writePreference(key, open ? '1' : '0');
            }
        };

        setOpen(shouldOpen, false);

        groupToggle.addEventListener('click', () => {
            setOpen(!group.classList.contains('is-open'));
        });
    });
}
