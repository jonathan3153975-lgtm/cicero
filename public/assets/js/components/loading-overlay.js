const LINK_DELAY = 120;

export function initLoadingOverlay() {
    const overlay = document.querySelector('[data-app-loading]');
    const message = overlay?.querySelector('[data-app-loading-message]');

    if (!overlay || !message) {
        return;
    }

    let navigationTimer = null;

    const show = (text) => {
        message.textContent = text;
        overlay.hidden = false;
        document.body.classList.add('is-loading');
        document.documentElement.setAttribute('aria-busy', 'true');
    };

    const hide = () => {
        overlay.hidden = true;
        document.body.classList.remove('is-loading');
        document.documentElement.removeAttribute('aria-busy');
        window.clearTimeout(navigationTimer);
    };

    const resetForm = (form) => {
        delete form.dataset.submitting;

        form.querySelectorAll('[data-loading-submit]').forEach((button) => {
            button.removeAttribute('aria-disabled');
            button.classList.remove('is-loading');
        });
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.noLoading !== undefined) {
            return;
        }

        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = '1';

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.dataset.loadingSubmit = '1';
            button.setAttribute('aria-disabled', 'true');
            button.classList.add('is-loading');
        });

        window.setTimeout(() => {
            if (event.defaultPrevented) {
                resetForm(form);
                return;
            }

            show(form.dataset.loadingMessage || 'Processando...');
        }, 0);
    }, true);

    document.addEventListener('click', (event) => {
        if (
            event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (!link || link.dataset.noLoading !== undefined) {
            return;
        }

        if (link.target && link.target !== '_self') {
            return;
        }

        const href = link.getAttribute('href');

        if (
            !href
            || href.startsWith('#')
            || href.startsWith('javascript:')
            || href.startsWith('mailto:')
            || href.startsWith('tel:')
            || link.hasAttribute('download')
        ) {
            return;
        }

        const url = new URL(link.href, window.location.href);

        if (url.origin !== window.location.origin) {
            return;
        }

        if (url.pathname === window.location.pathname && url.search === window.location.search) {
            return;
        }

        window.clearTimeout(navigationTimer);
        navigationTimer = window.setTimeout(() => {
            if (!event.defaultPrevented) {
                show(link.dataset.loadingMessage || 'Carregando...');
            }
        }, LINK_DELAY);
    });

    window.addEventListener('pageshow', () => {
        hide();
        document.querySelectorAll('form[data-submitting]').forEach(resetForm);
    });
}
