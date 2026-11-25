import { useState } from 'react';
import { A, Flash, money } from './chrome';
import { compact } from './marks';

const services = [
    ['guest_post', 'write_and_publish', 'Guest Post'],
    ['contextual_link', 'contextual_link', 'Contextual Link'],
    ['niche_edit', 'niche_edit', 'Niche Edit'],
    ['text_link', 'text_link', 'Text Link'],
];

function slugify(value) {
    return String(value || '').toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function ago(value) {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const days = Math.max(0, Math.round((Date.now() - date.getTime()) / 86400000));

    if (days < 1) {
        return 'today';
    }

    if (days === 1) {
        return '1 day ago';
    }

    if (days < 30) {
        return `${days} days ago`;
    }

    const months = Math.round(days / 30);

    return months === 1 ? '1 month ago' : `${months} months ago`;
}

function TrafficChart({ points }) {
    const rows = (points || []).filter((point) => Number.isFinite(Number(point.value)));

    if (rows.length < 2) {
        return null;
    }

    const width = 640;
    const height = 240;
    const left = 56;
    const top = 20;
    const plotW = width - left - 16;
    const plotH = height - top - 36;
    const max = Math.max(1, ...rows.map((point) => Number(point.value)));
    const step = plotW / (rows.length - 1);
    const coords = rows.map((point, index) => {
        const x = left + step * index;
        const y = top + plotH - ((Number(point.value) / max) * plotH);

        return [Math.round(x * 10) / 10, Math.round(y * 10) / 10];
    });
    const line = coords.map((point) => point.join(',')).join(' ');
    const base = top + plotH;
    const area = `${coords[0][0]},${base} ${line} ${coords[coords.length - 1][0]},${base}`;
    const first = rows[0];
    const last = rows[rows.length - 1];

    return (
        <svg viewBox={`0 0 ${width} ${height}`} className="block h-auto w-full" role="img" aria-label={`Monthly traffic from ${first.label || ''} ${compact(first.value)} to ${last.label || ''} ${compact(last.value)}`}>
            <polygon points={area} fill="#FF4917" fillOpacity="0.1" />
            <polyline points={line} fill="none" stroke="#FF4917" strokeWidth="2.5" strokeLinejoin="round" strokeLinecap="round" />
        </svg>
    );
}

export function MarketplaceView({ page, send }) {
    const profile = page.data.profile || {};
    const signedIn = Boolean(page.shell?.auth);
    const demo = Boolean(profile.demo);
    const canBuy = !demo && Boolean(profile.website_id);
    const [service, setService] = useState('write_and_publish');
    const [quantity, setQuantity] = useState(1);
    const [pending, setPending] = useState(false);
    const [errors, setErrors] = useState({});
    const [notice, setNotice] = useState('');
    const price = profile.price == null || profile.price === '' ? '' : money(profile.price);
    const updated = ago(profile.updated_at);
    const change = Number(profile.traffic_change);

    async function add(event) {
        event.preventDefault();
        setPending(true);
        const chosen = services.find((item) => item[1] === service) || services[0];
        const result = await send('/cart', 'POST', {
            domain: profile.domain,
            site: profile.key || profile.domain,
            website_id: profile.website_id,
            product_key: chosen[0],
            order_type: chosen[1],
            order_label: chosen[2],
            content_topic: profile.primary || 'General',
            project: 'Default',
            quantity: Math.min(10, Math.max(1, Number(quantity) || 1)),
        });
        const messages = Object.values(result?.errors || {}).flat().filter(Boolean);
        setPending(false);
        setErrors(result?.errors || {});

        if (messages.length) {
            setNotice('');
        }
    }

    async function save(event) {
        event.preventDefault();
        setPending(true);
        const result = await send('/customer/saved-websites', 'POST', {
            website_name: profile.domain,
            domain: profile.domain,
            url: profile.url || `https://${profile.domain}`,
            domain_rating: profile.dr,
            monthly_traffic: profile.traffic,
            price: profile.price,
        });
        setPending(false);
        setErrors(result?.errors || {});
    }

    return (
        <section className="bg-[#F2F2F2] px-6 py-10 text-navy lg:px-8 lg:py-14">
            <nav className="text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb">
                <A href="/">Home</A>
                <span aria-hidden="true"> / </span>
                <A href="/marketplace">Marketplace</A>
                <span aria-hidden="true"> / </span>
                {profile.primary_slug ? <><A href={`/niches/${profile.primary_slug}`}>{profile.primary}</A><span aria-hidden="true"> / </span></> : null}
                <span className="text-navy">{profile.domain}</span>
            </nav>
            {demo ? <p className="mt-3 text-[13px] text-[#4A5560]">Sample data for preview</p> : null}
            <div className="mt-4"><Flash flash={{ ...page.flash, success: notice || page.flash?.success }} errors={{ ...page.errors, ...errors }} /></div>

            <div className="mt-5 grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                <article className="rounded-3xl border border-[#E3E5E8] bg-white p-6">
                    <div className="flex gap-3.5">
                        <span className="grid h-[52px] w-[52px] shrink-0 place-items-center rounded-xl bg-navy text-[22px] font-extrabold text-white" aria-hidden="true">{String(profile.domain || '?').slice(0, 1).toUpperCase()}</span>
                        <div>
                            <h1 className="text-3xl font-extrabold break-words">{profile.domain}</h1>
                            {profile.url ? <a className="font-bold text-brand-dark hover:underline" href={profile.url} rel="nofollow noopener" target="_blank">Visit site ↗</a> : null}
                        </div>
                    </div>
                    <div className="mt-3 flex flex-wrap gap-2">
                        <span className="inline-flex min-h-8 items-center rounded-full bg-[#E7F6EE] px-2.5 text-[13px] font-bold text-[#1F7A45]">{profile.link_type || 'Dofollow'}</span>
                        {profile.language ? <span className="inline-flex min-h-8 items-center rounded-full bg-[#F2F2F2] px-2.5 text-[13px] font-bold text-[#4A5560]">{profile.language}</span> : null}
                        {updated ? <span className="inline-flex min-h-8 items-center rounded-full bg-[#F2F2F2] px-2.5 text-[13px] font-bold text-[#4A5560]">Updated {updated}</span> : null}
                    </div>
                    <h2 className="mt-5 mb-2 text-[13px] font-bold tracking-wide text-[#4A5560] uppercase">Categories</h2>
                    <div className="flex flex-wrap gap-2">
                        {(profile.categories || []).map((category, index) => (
                            <A key={category} href={`/niches/${slugify(category)}`} className={`inline-flex min-h-9 items-center rounded-full px-3 text-sm font-bold ${index === 0 ? 'bg-[#FFF1EC] text-brand-dark' : 'border border-[#E3E5E8] bg-white'}`}>{category}</A>
                        ))}
                    </div>
                </article>

                <aside className="rounded-3xl border border-[#E3E5E8] bg-white p-5 lg:sticky lg:top-24" aria-label="Order">
                    <p className="text-4xl font-extrabold">{price}</p>
                    <A href={`/publish/${encodeURIComponent(profile.key || profile.domain)}`} className="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-dark font-bold text-white hover:bg-brand">Publish</A>
                    {profile.credits != null ? <p className="mt-2 text-[15px] text-[#4A5560]">or {profile.credits} credits</p> : null}
                    <fieldset className="mt-4 grid gap-1 border-0 p-0">
                        <legend className="sr-only">Service</legend>
                        {services.map(([product, orderType, label]) => (
                            <label key={orderType} className="flex min-h-11 items-center gap-2.5 font-bold">
                                <input type="radio" name="product_key" value={orderType} checked={service === orderType} disabled={!canBuy} onChange={() => setService(orderType)} />
                                {label}
                            </label>
                        ))}
                    </fieldset>
                    <p className="mt-2 text-[13px] text-[#4A5560]">Final price may vary by service</p>
                    <label className="mt-3.5 flex items-center gap-2.5 font-bold">Quantity
                        <input className="field w-[84px]" type="number" min="1" max="10" value={quantity} disabled={!canBuy} onChange={(event) => setQuantity(event.target.value)} />
                    </label>
                    {canBuy ? (
                        <button className="btn-primary mt-3 w-full" type="button" disabled={pending} onClick={add}>{pending ? 'Adding…' : 'Add to cart'}</button>
                    ) : demo ? (
                        <button className="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-dark font-bold text-white opacity-60" type="button" disabled title="Demo data">Add to cart</button>
                    ) : (
                        <A href="/login" className="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand-dark font-bold text-white hover:bg-brand">Login to buy</A>
                    )}
                    {signedIn && !demo ? (
                        <button className="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border-[1.5px] border-navy bg-white font-bold disabled:opacity-60" type="button" disabled={pending} onClick={save}>Save website</button>
                    ) : (
                        <A href="/login" className="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border-[1.5px] border-navy bg-white font-bold">Save website</A>
                    )}
                    <p className="mt-3 text-sm leading-6 text-[#4A5560]">You'll add your target URL, anchor text and topic at checkout. Our team publishes the placement.</p>
                </aside>
            </div>

            <div className="mt-4 space-y-4">
                <section className="rounded-3xl border border-[#E3E5E8] bg-white p-6" aria-labelledby="about-heading">
                    <h2 id="about-heading" className="text-[28px] font-extrabold">About {profile.domain}</h2>
                    <p className="mt-3 text-[17px] leading-7 text-[#4A5560]">{profile.about_p1}</p>
                    <p className="mt-3 text-[17px] leading-7 text-[#4A5560]">{profile.about_p2}</p>
                </section>

                <section className="rounded-3xl border border-[#E3E5E8] bg-white p-6" aria-labelledby="metrics-heading">
                    <h2 id="metrics-heading" className="text-[28px] font-extrabold">SEO metrics</h2>
                    <dl className="mt-3 grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-3">
                        {[
                            ['DR', profile.dr, 'Domain Rating from 0 to 100.'],
                            ['DA', profile.da, 'Domain Authority from 0 to 100.'],
                            ['PA', profile.pa, 'Page Authority for a typical page.'],
                            ['Trust Flow', profile.tf, 'How trusted the site’s links look.'],
                            ['Citation Flow', profile.cf, 'How widely other sites mention it.'],
                            ['Monthly traffic', compact(profile.traffic), 'Estimated visits in a recent month.'],
                            ['Organic keywords', compact(profile.keywords), 'Keywords the site ranks for.'],
                            ['Backlinks', compact(profile.backlinks), 'Links pointing at this website.'],
                            ['Referring domains', compact(profile.referring_domains), 'Unique websites that link here.'],
                            ['Spam score', profile.spam, 'A lower number means fewer spam signals.'],
                        ].map(([label, value, note]) => (
                            <div key={label} className="rounded-2xl border border-[#E3E5E8] bg-white p-3.5">
                                <dt className="text-[13px] font-bold text-[#4A5560]">{label}</dt>
                                <dd className="mt-1.5 text-[28px] leading-none font-extrabold">{value ?? ''}</dd>
                                <p className="mt-2 text-xs leading-5 text-[#4A5560]">{note}</p>
                            </div>
                        ))}
                    </dl>
                </section>

                <section className="rounded-3xl border border-[#E3E5E8] bg-white p-6" aria-labelledby="traffic-heading">
                    <h2 id="traffic-heading" className="text-[28px] font-extrabold">Traffic overview</h2>
                    <div className="mt-3 grid items-center gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                        <div>
                            <p className="text-4xl font-extrabold">{compact(profile.traffic)}</p>
                            <p className="mt-2 text-[13px] text-[#4A5560]">Current monthly traffic</p>
                            {profile.has_traffic_history && Number.isFinite(change) ? <p className={`mt-1 font-bold ${change >= 0 ? 'text-[#1F7A45]' : 'text-[#B42318]'}`}>{change >= 0 ? '↑ +' : '↓ '}{change}%</p> : null}
                        </div>
                        <div>
                            {profile.has_traffic_history ? <TrafficChart points={profile.traffic_series} /> : <p className="text-[13px] text-[#4A5560]">Traffic history will appear after the next metrics update.</p>}
                            {profile.has_traffic_history ? <p className="mt-2 text-[13px] text-[#4A5560]">Estimated organic visits per month.</p> : null}
                        </div>
                    </div>
                </section>

                <section className="rounded-3xl border border-[#E3E5E8] bg-white p-6" aria-labelledby="countries-heading">
                    <h2 id="countries-heading" className="text-[28px] font-extrabold">Where visitors come from</h2>
                    {profile.countries_ready && (profile.top_countries || []).length ? (
                        <ul className="mt-3 grid gap-3">
                            {profile.top_countries.map((item) => {
                                const percent = Math.max(0, Math.min(100, Number(item.percent) || 0));

                                return (
                                    <li key={`${item.code}-${item.name}`}>
                                        <div className="mb-1.5 flex items-center justify-between gap-3">
                                            <span className="flex min-w-0 items-center gap-2 text-[15px] font-bold">
                                                <span className="truncate">{item.name}</span>
                                                {item.code ? <span className="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs text-[#4A5560]">{item.code}</span> : null}
                                            </span>
                                            <span className="text-sm font-bold text-[#4A5560]">{percent}%</span>
                                        </div>
                                        <div className="h-2.5 overflow-hidden rounded-full bg-[#F2F2F2]" aria-hidden="true"><div className="h-full rounded-full bg-navy" style={{ width: `${percent}%` }} /></div>
                                    </li>
                                );
                            })}
                        </ul>
                    ) : <p className="mt-3 text-[13px] text-[#4A5560]">Country data coming soon.</p>}
                </section>

                <section className="rounded-3xl border border-[#E3E5E8] bg-white p-6" aria-labelledby="also-heading">
                    <h2 id="also-heading" className="text-[28px] font-extrabold">Also check these {profile.primary ? `${profile.primary} ` : ''}websites</h2>
                    <div className="mt-3 grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-3">
                        {(profile.similar || []).map((site) => (
                            <article key={site.key || site.domain} className="flex flex-col gap-2 rounded-2xl border border-[#E3E5E8] p-4">
                                <strong className="break-words">{site.domain}</strong>
                                {site.category ? <A href={`/niches/${slugify(site.category)}`} className="inline-flex min-h-9 w-fit items-center rounded-full border border-[#E3E5E8] px-3 text-sm font-bold">{site.category}</A> : null}
                                <div className="flex flex-wrap gap-1.5">
                                    <span className="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs font-bold">DR {site.dr ?? ''}</span>
                                    <span className="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs font-bold">DA {site.da ?? ''}</span>
                                    <span className="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs font-bold">{compact(site.traffic)}</span>
                                </div>
                                <p className="font-extrabold">{site.price == null ? '' : money(site.price)}</p>
                                <A href={`/marketplace/${site.key}`} className="font-bold text-brand-dark">View site →</A>
                            </article>
                        ))}
                    </div>
                    {(profile.popular_in_niche || []).length ? (
                        <p className="mt-4 text-sm leading-6 text-[#4A5560]"><span className="font-extrabold text-navy">Popular in {profile.primary}:</span> {profile.popular_in_niche.map((site, index) => <span key={site.key || site.domain}>{index > 0 ? ' · ' : ' '}<A href={`/marketplace/${site.key}`} className="font-bold text-brand-dark">{site.domain}</A></span>)}</p>
                    ) : null}
                    {profile.primary_slug ? <p className="mt-3 text-sm"><A href={`/niches/${profile.primary_slug}`} className="font-bold text-brand-dark">See all {profile.primary} websites →</A></p> : null}
                    <div className="mt-4 rounded-2xl bg-[#F2F2F2] px-4 py-4 pl-8">
                        <p className="mb-2 -ml-4 font-extrabold">Publisher guidelines</p>
                        <ul className="list-disc space-y-1.5">
                            <li>Original content, minimum [WORD COUNT] words</li>
                            <li>Topic must suit the site's categories</li>
                            <li>No adult, gambling or CBD content unless the publisher allows it</li>
                            <li>Up to [N] links per article</li>
                        </ul>
                    </div>
                </section>
            </div>
        </section>
    );
}
