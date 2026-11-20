import { readFileSync } from 'node:fs';
import { routeForLaunch } from '../src/navigation/launch.ts';

const base = (process.env.API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const email = process.env.AUTH_EMAIL ?? 'noah.williams@example.com';
const password = process.env.AUTH_PASSWORD ?? 'Password123';

function assert(condition, message) {
    if (!condition) {
        throw new Error(message);
    }
}

assert(routeForLaunch({ signedIn: false }) === 'Home', 'A signed-out launch opened a screen other than Home.');
assert(routeForLaunch({ signedIn: true }) === 'Home', 'A signed-in launch opened a screen other than Home.');

const navigator = readFileSync(new URL('../src/navigation/RootNavigator.tsx', import.meta.url), 'utf8');
const auth = readFileSync(new URL('../src/context/AuthContext.tsx', import.meta.url), 'utf8');
const client = readFileSync(new URL('../src/api/client.ts', import.meta.url), 'utf8');
assert(navigator.includes('initialRouteName={launchRouteName}'), 'The navigator does not start on the launch route.');
assert(!navigator.includes('customer ?'), 'Authentication still chooses the first screen.');
assert(!auth.includes('navigate('), 'The session restore navigates.');
assert(!client.includes('navigate('), 'The HTTP client navigates.');

const jar = new Map();

function store(response) {
    for (const line of response.headers.getSetCookie?.() ?? []) {
        const match = /^([^=;\s]+)=([^;]*)/.exec(line.trim());
        if (match) {
            jar.set(match[1], match[2]);
        }
    }
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
    const token = jar.get('XSRF-TOKEN');
    if (options.method && options.method !== 'GET' && token) {
        headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
    }
    const response = await fetch(base + path, {
        method: options.method ?? 'GET',
        headers,
        body: options.body ? JSON.stringify(options.body) : undefined,
        redirect: 'manual',
    });
    store(response);
    return { status: response.status, body: JSON.parse(await response.text()) };
}

const guest = await call('/home');
assert(guest.body.view === 'customer.pages.home', `Guest /home was ${guest.body.view ?? guest.body.redirect}.`);
assert(!guest.body.redirect, 'Guest /home returned a redirect.');

await call('/login');
const signedOutDashboard = await call('/customer/dashboard');
assert(String(signedOutDashboard.body.redirect ?? '').includes('/login') || signedOutDashboard.status === 401 || signedOutDashboard.status === 302, 'A guest dashboard request was treated as an app launch route.');

const signedIn = await call('/login', { method: 'POST', json: true, body: { email, password } });
assert(signedIn.status < 400 && signedIn.body.redirect !== '/login', `Could not sign in for the launch check: ${JSON.stringify(signedIn.body)}`);

const memberHome = await call('/home');
assert(memberHome.body.view === 'customer.pages.home', `Signed-in /home was ${memberHome.body.view ?? memberHome.body.redirect}.`);
assert(!memberHome.body.redirect, 'Signed-in /home redirected away from the public home page.');

await call('/logout', { method: 'POST', json: true, body: {} });

console.log(JSON.stringify({
    ok: true,
    guest: guest.body.view,
    signedIn: memberHome.body.view,
    launch: routeForLaunch({ signedIn: false }),
}, null, 2));
