export type CookieJar = Record<string, string>;

const COOKIE_PAIR = /^([^=;\s]+)=([^;]*)/;

export function readSetCookieHeaders(headers: Headers): string[] {
    const withList = headers as Headers & { getSetCookie?: () => string[] };

    if (typeof withList.getSetCookie === 'function') {
        const values = withList.getSetCookie().filter((value) => value.trim() !== '');

        if (values.length > 0) {
            return values;
        }
    }

    const combined = headers.get('set-cookie');

    if (!combined) {
        return [];
    }

    return splitCombinedSetCookie(combined);
}

export function splitCombinedSetCookie(header: string): string[] {
    const parts: string[] = [];
    let start = 0;

    for (let index = 0; index < header.length; index += 1) {
        if (header[index] !== ',') {
            continue;
        }

        const rest = header.slice(index + 1);
        if (/^\s*[^;,\s]+=/.test(rest)) {
            parts.push(header.slice(start, index).trim());
            start = index + 1;
        }
    }

    const last = header.slice(start).trim();

    if (last !== '') {
        parts.push(last);
    }

    return parts;
}

export function storeSetCookies(jar: CookieJar, setCookies: string[]): CookieJar {
    const next = { ...jar };

    for (const line of setCookies) {
        const match = COOKIE_PAIR.exec(line.trim());

        if (!match) {
            continue;
        }

        const name = match[1];
        const value = match[2];
        const expired = /(?:^|;\s*)max-age=0(?:;|$)/i.test(line) || /(?:^|;\s*)expires=thu, 01 jan 1970/i.test(line);

        if (value === '' || expired) {
            delete next[name];
        } else {
            next[name] = value;
        }
    }

    return next;
}

export function cookieHeader(jar: CookieJar): string {
    return Object.entries(jar)
        .map(([name, value]) => `${name}=${value}`)
        .join('; ');
}

export function xsrfToken(jar: CookieJar): string {
    const raw = jar['XSRF-TOKEN'] ?? '';

    try {
        return decodeURIComponent(raw);
    } catch {
        return raw;
    }
}
