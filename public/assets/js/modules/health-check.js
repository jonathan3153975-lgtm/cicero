import { request } from '../core/http.js';

export function initHealthCheck() {
    const root = document.querySelector('[data-health-check]');

    if (!root) {
        return;
    }

    const card = root.querySelector('.health-card');
    const status = root.querySelector('[data-health-status]');
    const meta = root.querySelector('[data-health-meta]');

    request('/api/v1/health')
        .then((payload) => {
            card?.classList.add('is-online');

            const database = payload?.data?.database;

            if (status) {
                status.textContent = database === 'ok'
                    ? 'API e banco operacionais'
                    : 'API online · banco indisponível';
            }

            if (meta) {
                meta.textContent = `Ambiente: ${payload?.data?.environment ?? '—'}`;
            }
        })
        .catch(() => {
            card?.classList.add('is-offline');

            if (status) {
                status.textContent = 'API indisponível';
            }

            if (meta) {
                meta.textContent = 'Verifique o servidor local';
            }
        });
}
