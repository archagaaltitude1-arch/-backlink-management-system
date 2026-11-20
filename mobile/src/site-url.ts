export function siteAddress(apiBase: string, target: string): string {
    const base = apiBase.replace(/\/$/, '');
    const value = target.trim();

    if (value === '' || value === '/') {
        return `${base}/`;
    }

    if (value.startsWith('/')) {
        return `${base}${value}`;
    }

    try {
        const url = new URL(value);
        const local = url.hostname === 'localhost' || url.hostname === '127.0.0.1';

        if (local) {
            return `${base}${url.pathname}${url.search}${url.hash}`;
        }
    } catch {
        return `${base}/${value.replace(/^\//, '')}`;
    }

    return value;
}
