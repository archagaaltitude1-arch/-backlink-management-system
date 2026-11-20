const base = (process.env.API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const email = process.env.AUTH_EMAIL ?? 'noah.williams@example.com';
const password = process.env.AUTH_PASSWORD ?? 'Password123';
const jar = new Map();

function store(response) {
    const list = typeof response.headers.getSetCookie === 'function' ? response.headers.getSetCookie() : [];

    for (const line of list) {
        const match = /^([^=;\s]+)=([^;]*)/.exec(line.trim());
        if (match) {
            jar.set(match[1], match[2]);
        }
    }
}

function xsrf() {
    const raw = jar.get('XSRF-TOKEN') ?? '';
    return decodeURIComponent(raw);
}

async function call(path, options = {}) {
    const headers = {
        Accept: options.json ? 'application/json' : 'text/html, application/json',
        'X-Backlink-Spa': '1',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
    };
    if (jar.size > 0) {
        headers.Cookie = [...jar.entries()].map(([name, value]) => `${name}=${value}`).join('; ');
    }
    if (options.method && options.method !== 'GET' && xsrf()) {
        headers['X-XSRF-TOKEN'] = xsrf();
    }
    const response = await fetch(base + path, {
        method: options.method ?? 'GET',
        headers,
        body: options.body ? JSON.stringify(options.body) : undefined,
        redirect: 'manual',
    });
    store(response);
    const text = await response.text();
    let body = {};
    try {
        body = text ? JSON.parse(text) : {};
    } catch {
        body = { raw: text.slice(0, 160) };
    }
    return { status: response.status, body, cookies: [...jar.keys()] };
}

function assert(condition, message) {
    if (!condition) {
        throw new Error(message);
    }
}

function failed(body) {
    const errors = body.errors;

    return Boolean(errors && !Array.isArray(errors) && Object.keys(errors).length > 0);
}

const bootstrap = await call('/login');
assert(bootstrap.cookies.includes('XSRF-TOKEN'), `CSRF cookie was not set. Status ${bootstrap.status}.`);

const rejected = await call('/login', { method: 'POST', json: true, body: { email, password: 'not-the-password' } });
assert(failed(rejected.body) || rejected.body.redirect === '/login' || rejected.status >= 400, `Wrong password was accepted: ${JSON.stringify(rejected.body)}`);

const invalidRegister = await call('/register', {
    method: 'POST',
    json: true,
    body: { name: 'Phase One', email: 'phase-one-invalid@example.com', password: 'short', password_confirmation: 'different' },
});
assert(failed(invalidRegister.body) || invalidRegister.status >= 400, `Registration validation did not return errors: ${JSON.stringify(invalidRegister.body)}`);

const signedIn = await call('/login', { method: 'POST', json: true, body: { email, password } });
assert(!failed(signedIn.body) && signedIn.status < 400 && signedIn.body.redirect !== '/login', `Login failed: ${JSON.stringify(signedIn.body)}`);

const dashboard = await call('/customer/dashboard');
const customer = dashboard.body.data?.customer;
assert(customer?.email?.toLowerCase() === email.toLowerCase(), `Dashboard did not return the signed-in customer: ${JSON.stringify(dashboard.body).slice(0, 300)}`);

await call('/logout', { method: 'POST', json: true, body: {} });
const afterLogout = await call('/customer/dashboard');
const stillSignedIn = afterLogout.body.data?.customer?.email;
assert(!stillSignedIn, 'Logout did not end the session.');

console.log(JSON.stringify({
    ok: true,
    api: base,
    customer: customer.email,
    dashboardView: dashboard.body.view,
    registrationValidation: failed(invalidRegister.body) ? Object.keys(invalidRegister.body.errors) : [`status ${invalidRegister.status}`],
}, null, 2));
