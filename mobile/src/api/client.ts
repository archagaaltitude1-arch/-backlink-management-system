import { apiUrl } from '../config';
import { cookieHeader, readSetCookieHeaders, splitCombinedSetCookie, storeSetCookies, xsrfToken, type CookieJar } from './cookies';
import { clearCookies, loadCookies, saveCookies } from './session';

export type ApiResult = {
    status: number;
    body: Record<string, unknown>;
    raw: string;
};

let jar: CookieJar = {};
let ready: Promise<void> | null = null;

export async function restoreSession(): Promise<void> {
    if (!ready) {
        ready = loadCookies().then((stored) => {
            jar = stored;
        });
    }

    await ready;
}

export function sessionCookies(): CookieJar {
    return jar;
}

export async function forgetSession(): Promise<void> {
    jar = {};
    ready = Promise.resolve();
    await clearCookies();
}

export async function apiRequest(path: string, options: { method?: string; body?: Record<string, unknown>; retried?: boolean; json?: boolean; timeoutMs?: number } = {}): Promise<ApiResult> {
    await restoreSession();
    const method = (options.method ?? 'GET').toUpperCase();

    if (method !== 'GET' && method !== 'HEAD' && !xsrfToken(jar)) {
        await apiRequest('/login');
    }

    const headers: Record<string, string> = {
        Accept: options.json ? 'application/json' : 'text/html, application/json',
        'X-Backlink-Spa': '1',
        'X-Requested-With': 'XMLHttpRequest',
    };
    const cookie = cookieHeader(jar);

    if (cookie) {
        headers.Cookie = cookie;
    }

    const token = xsrfToken(jar);

    if (token && method !== 'GET' && method !== 'HEAD') {
        headers['X-XSRF-TOKEN'] = token;
    }

    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    const url = apiUrl + path;
    const response = await request(url, {
        method,
        headers,
        body: options.body ? JSON.stringify(options.body) : undefined,
    }, options.timeoutMs ?? 20000);
    const text = await response.text();
    let body: Record<string, unknown> = {};
    let parsedJson = false;

    if (text) {
        try {
            const parsed = JSON.parse(text) as unknown;
            if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                body = parsed as Record<string, unknown>;
                parsedJson = true;
            }
        } catch {
            body = { message: text.slice(0, 180) };
        }
    }

    if (!parsedJson || response.status >= 400) {
        console.warn('[api]', method, url, response.status, text.slice(0, 180));
    }

    if (response.status === 419 && !options.retried && method !== 'GET' && method !== 'HEAD') {
        await refreshCsrf();

        return apiRequest(path, { ...options, retried: true });
    }

    return { status: response.status, body, raw: text };
}

async function refreshCsrf(): Promise<void> {
    delete jar['XSRF-TOKEN'];
    await saveCookies(jar);
    await request(apiUrl + '/login', {
        method: 'GET',
        headers: {
            Accept: 'text/html, application/json',
            'X-Backlink-Spa': '1',
        },
    }, 20000);
}

async function request(url: string, init: RequestInit, timeoutMs: number): Promise<Response> {
    if (typeof XMLHttpRequest !== 'undefined') {
        const viaXhr = await requestWithXhr(url, init, timeoutMs);

        if (viaXhr) {
            return viaXhr;
        }
    }

    return fetchWithDeadline(url, init, timeoutMs);
}

async function fetchWithDeadline(url: string, init: RequestInit, timeoutMs: number): Promise<Response> {
    try {
        const response = await deadline(fetch(url, init), timeoutMs);
        await capture(response);

        return response;
    } catch (error) {
        if (!isAbortError(error)) {
            throw error;
        }

        const response = await deadline(fetch(url, init), timeoutMs);
        await capture(response);

        return response;
    }
}

function deadline<T>(work: Promise<T>, timeoutMs: number): Promise<T> {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
            reject(new Error('The request timed out before the server responded.'));
        }, timeoutMs);

        work.then(
            (value) => {
                clearTimeout(timer);
                resolve(value);
            },
            (error: unknown) => {
                clearTimeout(timer);
                reject(error);
            },
        );
    });
}

function isAbortError(error: unknown): boolean {
    return error instanceof Error && (error.name === 'AbortError' || /operation was aborted/i.test(error.message));
}

async function capture(response: Response): Promise<void> {
    const setCookies = readSetCookieHeaders(response.headers);

    if (setCookies.length === 0) {
        return;
    }

    jar = storeSetCookies(jar, setCookies);
    await saveCookies(jar);
}

function requestWithXhr(url: string, init: RequestInit, timeoutMs: number): Promise<Response | null> {
    return new Promise((resolve) => {
        const xhr = new XMLHttpRequest();
        xhr.open((init.method ?? 'GET').toUpperCase(), url);
        xhr.timeout = timeoutMs;
        xhr.responseType = 'text';
        const headers = (init.headers ?? {}) as Record<string, string>;

        for (const [name, value] of Object.entries(headers)) {
            if (name.toLowerCase() === 'cookie') {
                continue;
            }
            xhr.setRequestHeader(name, value);
        }

        const cookie = cookieHeader(jar);
        if (cookie) {
            try {
                xhr.setRequestHeader('Cookie', cookie);
            } catch {
                // Some Android runtimes hide the Cookie header. The stored jar is still sent when the runtime allows it.
            }
        }

        xhr.onerror = () => resolve(null);
        xhr.ontimeout = () => resolve(null);
        xhr.onload = () => {
            const fromHeader = xhr.getResponseHeader('set-cookie');
            const fromAll = xhr.getAllResponseHeaders()
                .split(/\r?\n/)
                .filter((line) => line.toLowerCase().startsWith('set-cookie:'))
                .map((line) => line.slice('set-cookie:'.length).trim());
            const combined = [fromHeader, ...fromAll].filter((value): value is string => Boolean(value));

            if (combined.length > 0) {
                jar = storeSetCookies(jar, combined.flatMap((value) => splitCombinedSetCookie(value)));
                void saveCookies(jar);
            }

            const raw = typeof xhr.responseText === 'string' && xhr.responseText !== ''
                ? xhr.responseText
                : (typeof xhr.response === 'string' ? xhr.response : '');

            if (xhr.status === 0) {
                resolve(null);
                return;
            }

            resolve(new Response(raw, {
                status: xhr.status,
                headers: { 'content-type': xhr.getResponseHeader('content-type') ?? 'application/json' },
            }));
        };
        xhr.send(typeof init.body === 'string' ? init.body : null);
    });
}

export function errorText(body: Record<string, unknown>): string {
    const errors = body.errors;

    if (errors && typeof errors === 'object' && !Array.isArray(errors)) {
        for (const value of Object.values(errors)) {
            if (Array.isArray(value) && typeof value[0] === 'string') {
                return value[0];
            }
            if (typeof value === 'string') {
                return value;
            }
        }
    }

    if (typeof body.message === 'string' && body.message !== '') {
        return body.message;
    }

    return 'The request could not be completed.';
}

export function hasErrors(body: Record<string, unknown>): boolean {
    const errors = body.errors;

    return Boolean(errors && typeof errors === 'object' && !Array.isArray(errors) && Object.keys(errors).length > 0);
}
