function token() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function request(path, options = {}) {
    const response = await fetch(path, {
        credentials: 'same-origin',
        headers: {
            Accept: 'text/html, application/json',
            'X-Backlink-Spa': '1',
            'X-XSRF-TOKEN': token(),
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
        ...options,
    });

    const payload = await response.json().catch(() => ({ message: 'The server returned an unexpected response.' }));

    if (!response.ok && !payload.redirect && !payload.errors) {
        const error = new Error(payload.message || 'The request could not be completed.');
        error.payload = payload;
        throw error;
    }

    return payload;
}

export function loadPage(path) {
    return request(path);
}

export function submitForm(action, method, fields) {
    return request(action, {
        method: method || 'POST',
        body: JSON.stringify(fields || {}),
    });
}
