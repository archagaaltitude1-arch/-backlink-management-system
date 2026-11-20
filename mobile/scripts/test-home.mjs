import { parseHome } from '../src/api/home.ts';

const base = 'http://10.66.193.47:8000';

function assert(condition, message) {
    if (!condition) {
        throw new Error(message);
    }
}

const website = await fetch(`${base}/home`, { signal: AbortSignal.timeout(20000) });
const websiteType = website.headers.get('content-type') ?? '';
assert(websiteType.includes('text/html'), `Plain /home was ${websiteType}, not the website HTML.`);
await website.text();

const response = await fetch(`${base}/api/home`, {
    headers: { Accept: 'application/json' },
    signal: AbortSignal.timeout(20000),
});
const type = response.headers.get('content-type') ?? '';
const text = await response.text();
assert(response.status === 200, `GET /api/home returned ${response.status}: ${text.slice(0, 180)}`);
assert(type.includes('application/json'), `GET /api/home content type was ${type}.`);

const body = JSON.parse(text);
const home = parseHome(body, response.status);
assert(home.serviceCards.length > 0, 'Homepage JSON did not include service cards.');
assert(home.nicheTiles.length > 0, 'Homepage JSON did not include niches.');
assert(home.publisherCount === null || typeof home.publisherCount === 'number', 'Publisher count was not a number.');

console.log(JSON.stringify({
    ok: true,
    endpoint: `${base}/api/home`,
    publisherCount: home.publisherCount,
    nicheCount: home.nicheCount,
    services: home.serviceCards.map((card) => ({ title: card.title, price: card.price ?? null })),
    niches: home.nicheTiles.length,
}, null, 2));
