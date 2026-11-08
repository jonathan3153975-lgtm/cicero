export async function request(url, options = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {}),
    };

    const response = await fetch(url, { ...options, headers });

    const isJson = (response.headers.get('content-type') || '').includes('application/json');
    const payload = isJson ? await response.json() : null;

    if (!response.ok) {
        const error = new Error(payload?.message || 'Não foi possível concluir a requisição.');
        error.status = response.status;
        error.payload = payload;
        throw error;
    }

    return payload;
}
