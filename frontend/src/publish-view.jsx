import { useState } from 'react';
import { A, money } from './chrome';
import { Flag, Sparkline, compact } from './marks';

function figure(value, places = 0) {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '—';
    }

    return number.toLocaleString(undefined, { minimumFractionDigits: places, maximumFractionDigits: places });
}

export function PublishView({ page, send }) {
    const offer = page.data.offer || {};
    const groups = offer.order_types || {};
    const placement = groups.content_placement || [];
    const insertion = groups.link_insertion || [];
    const topics = offer.content_topics || ['General'];
    const metrics = offer.metrics || {};
    const [service, setService] = useState('content_placement');
    const types = service === 'link_insertion' ? insertion : placement;
    const [choice, setChoice] = useState(placement[0]?.key || insertion[0]?.key || '');
    const [topic, setTopic] = useState(topics.includes('General') ? 'General' : (topics[0] || 'General'));
    const [quantity, setQuantity] = useState(1);
    const [panel, setPanel] = useState('description');
    const [pending, setPending] = useState(false);
    const [error, setError] = useState('');
    const selected = [...placement, ...insertion].find((type) => type.key === choice) || types[0] || {};
    const unit = Number.isFinite(Number(offer.price)) ? Number(offer.price) : null;
    const addonPrice = Number.isFinite(Number(offer.writing_addon_price)) ? Number(offer.writing_addon_price) : null;
    const addon = Boolean(selected.addon);
    const extra = addon && addonPrice !== null ? addonPrice * quantity : 0;
    const line = unit === null ? null : unit * quantity;
    const subtotal = line === null ? null : line + extra;

    function chooseService(next) {
        setService(next);
        const list = next === 'link_insertion' ? insertion : placement;
        setChoice(list[0]?.key || '');
    }

    async function add(event) {
        event.preventDefault();
        setPending(true);
        setError('');
        const result = await send('/cart', 'POST', {
            domain: offer.domain,
            site: offer.key,
            website_id: offer.website_id,
            product_key: selected.product_key,
            order_type: selected.key,
            order_label: selected.label,
            content_topic: topic,
            project: 'Default',
            quantity,
        });
        setPending(false);
        const message = Object.values(result?.errors || {}).flat()[0];

        if (message) {
            setError(message);
        }
    }

    return (
        <section className="bg-[#F7F8F9] px-4 py-8 text-navy sm:px-6">
            <div className="mx-auto w-full">
                <nav className="flex flex-wrap gap-1.5 text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb">
                    <A href="/">Home</A>
                    <span aria-hidden="true">/</span>
                    {offer.primary_slug ? <A href={`/niches/${offer.primary_slug}`}>{offer.primary_category}</A> : <span>{offer.primary_category || '—'}</span>}
                    <span aria-hidden="true">/</span>
                    <span>{offer.display_name}</span>
                </nav>
                {offer.demo ? <p className="mt-3 text-sm text-[#4A5560]">Sample data for preview</p> : null}

                <div className="mt-4 grid gap-4 lg:grid-cols-2">
                    <div className="space-y-4">
                        <Facts offer={offer} />
                        <Countries rows={offer.top_countries || []} others={offer.others_percent} />
                    </div>
                    <div className="space-y-4">
                        <header className="rounded-2xl border border-[#E3E5E8] bg-white p-5">
                            <h1 className="text-3xl font-extrabold">Publish a Guest Post on {offer.display_name}</h1>
                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                {offer.premium ? <span className="inline-flex min-h-8 items-center rounded-full bg-brand px-3 text-sm font-bold text-white">Premium publisher</span> : null}
                                <span className="inline-flex min-h-8 items-center rounded-full border border-[#E3E5E8] px-3 text-sm font-bold">Site ID: {offer.site_id}</span>
                            </div>
                            <div className="mt-4 flex flex-wrap items-center gap-3 border-b border-[#E3E5E8] pb-4">
                                <p className="font-sans text-4xl font-extrabold">{offer.price_label || '—'}</p>
                                <button type="button" className={`min-h-11 rounded-xl border px-3 text-sm font-bold ${service === 'content_placement' ? 'border-brand bg-[#FFF1EC] text-brand-dark' : 'border-[#E3E5E8]'}`} aria-pressed={service === 'content_placement'} onClick={() => chooseService('content_placement')}>Content placement</button>
                                <button type="button" className={`min-h-11 rounded-xl border px-3 text-sm font-bold ${service === 'link_insertion' ? 'border-brand bg-[#FFF1EC] text-brand-dark' : 'border-[#E3E5E8]'}`} aria-pressed={service === 'link_insertion'} onClick={() => chooseService('link_insertion')}>Link insertion</button>
                            </div>
                        </header>
                        <Metrics metrics={metrics} trends={offer.trends || {}} domain={offer.domain} />
                        <dl className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            {[['Links allowed', offer.backlinks_allowed], ['Link validity', offer.link_validity], ['Link type', offer.link_type], ['Article length', offer.article_length]].map(([label, value]) => (
                                <div key={label} className="rounded-xl bg-[#EEF5FC] px-3 py-3 text-center"><dt className="text-sm text-[#4A5560]">{label}</dt><dd className="mt-1 font-extrabold">{value ?? '—'}</dd></div>
                            ))}
                        </dl>
                        <section className="rounded-2xl bg-[#EEF5FC] p-4" aria-labelledby="rules-title">
                            <h2 id="rules-title" className="text-lg font-extrabold">Publishing rules</h2>
                            <ul className="mt-3 list-disc space-y-1 pl-5 text-sm">{(offer.rules || []).map((rule) => <li key={rule}>{rule}</li>)}</ul>
                        </section>
                        <form className="rounded-2xl border border-[#E3E5E8] bg-white p-4" onSubmit={add}>
                            <fieldset>
                                <legend className="font-bold">Order type *</legend>
                                <div className="mt-2 space-y-2">
                                    {types.map((type) => (
                                        <label key={type.key} className={`flex gap-3 rounded-xl border p-3 ${choice === type.key ? 'border-[#3D7BC4] bg-[#EEF5FC]' : 'border-[#E3E5E8]'}`}>
                                            <input className="mt-1" type="radio" name="order_choice" checked={choice === type.key} onChange={() => setChoice(type.key)} />
                                            <span><strong>{type.label}</strong> <span className="text-[#4A5560]">({type.help})</span></span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                            <fieldset className="mt-4">
                                <legend className="font-bold">Your article topic *</legend>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {topics.slice(0, 6).map((item) => (
                                        <label key={item} className={`inline-flex min-h-11 items-center rounded-full border px-3 text-sm font-bold ${topic === item ? 'border-[#3D7BC4] bg-[#EEF5FC]' : 'border-[#E3E5E8]'}`}>
                                            <input className="sr-only" type="radio" name="topic_choice" checked={topic === item} onChange={() => setTopic(item)} />{item}
                                        </label>
                                    ))}
                                </div>
                                {topics.length > 6 ? (
                                    <details className="mt-2">
                                        <summary className="cursor-pointer font-bold text-brand-dark">More topics</summary>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {topics.slice(6).map((item) => (
                                                <label key={item} className={`inline-flex min-h-11 items-center rounded-full border px-3 text-sm font-bold ${topic === item ? 'border-[#3D7BC4] bg-[#EEF5FC]' : 'border-[#E3E5E8]'}`}>
                                                    <input className="sr-only" type="radio" name="topic_choice" checked={topic === item} onChange={() => setTopic(item)} />{item}
                                                </label>
                                            ))}
                                        </div>
                                    </details>
                                ) : null}
                            </fieldset>
                            <div className="mt-4 flex items-center justify-between gap-3">
                                <span id="qty-label" className="font-bold">Quantity</span>
                                <div className="flex items-center gap-1">
                                    <button type="button" className="grid h-11 w-11 place-items-center rounded-xl border border-[#E3E5E8]" aria-label="Decrease quantity" onClick={() => setQuantity((value) => Math.max(1, value - 1))}>−</button>
                                    <input className="h-11 w-16 rounded-xl border border-[#E3E5E8] text-center" type="number" min="1" max="10" aria-labelledby="qty-label" value={quantity} onChange={(event) => setQuantity(Math.min(10, Math.max(1, Number(event.target.value) || 1)))} />
                                    <button type="button" className="grid h-11 w-11 place-items-center rounded-xl border border-[#E3E5E8]" aria-label="Increase quantity" onClick={() => setQuantity((value) => Math.min(10, value + 1))}>+</button>
                                </div>
                            </div>
                            <div className="mt-4 space-y-2 border-t border-[#E3E5E8] pt-3 text-sm">
                                <p className="flex justify-between gap-3"><span>{quantity}× Publish on {offer.display_name} – {offer.link_type} link</span><span>{line === null ? '—' : money(line)}</span></p>
                                <p className="flex justify-between gap-3"><span>Order type – {selected.label}</span><span>{addon ? (addonPrice === null ? '[PRICE]' : `+ ${money(extra)}`) : '—'}</span></p>
                                <p className="flex justify-between gap-3"><span>Article topic – {topic}</span><span>—</span></p>
                                <p className="flex items-center justify-between border-t border-[#E3E5E8] pt-3 text-base"><span>Subtotal</span><strong className="text-2xl">{subtotal === null ? '—' : money(subtotal)}</strong></p>
                            </div>
                            <p className="mt-3 rounded-xl bg-[#F2F2F2] p-3 text-sm text-[#4A5560]">After checkout you'll add the topic, anchor text, and target URL on the requirements page. Our team writes and publishes the placement.</p>
                            {error ? <p className="mt-3 text-sm font-bold text-brand-dark" role="alert">{error}</p> : null}
                            <button className="btn-primary mt-3 w-full" type="submit" disabled={pending}>{pending ? 'Adding…' : 'Add to cart'}</button>
                            <ul className="mt-4 space-y-2 text-sm">
                                <li><strong className="block">Estimated delivery</strong>{offer.tat_days ?? '—'} days to publication</li>
                                <li><strong className="block">Pay only for verified links</strong>Reserved credits are released if a placement fails.</li>
                                <li><strong className="block">Questions?</strong><A href="/contact" className="font-bold text-brand-dark">Contact us</A></li>
                            </ul>
                        </form>
                    </div>
                </div>

                <div className="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white p-5">
                    <div className="flex gap-1 border-b border-[#E3E5E8]" role="tablist">
                        <button type="button" role="tab" className={`min-h-12 px-4 font-extrabold ${panel === 'description' ? 'border-b-[3px] border-brand text-navy' : 'text-[#4A5560]'}`} aria-selected={panel === 'description'} onClick={() => setPanel('description')}>Description</button>
                        <button type="button" role="tab" className={`min-h-12 px-4 font-extrabold ${panel === 'reviews' ? 'border-b-[3px] border-brand text-navy' : 'text-[#4A5560]'}`} aria-selected={panel === 'reviews'} onClick={() => setPanel('reviews')}>Reviews ({offer.reviews?.count || 0})</button>
                    </div>
                    {panel === 'description' ? <Description offer={offer} /> : <Reviews reviews={offer.reviews || {}} />}
                </div>

                {(offer.similar || []).length > 0 ? (
                    <section className="mt-8">
                        <h2 className="text-2xl font-extrabold">Similar publishers</h2>
                        <div className="mt-4 flex gap-3 overflow-x-auto pb-2">
                            {offer.similar.map((item) => (
                                <A key={item.domain || item.key} href={`/publish/${encodeURIComponent(item.key || item.domain)}`} className="w-64 shrink-0 rounded-2xl border border-[#E3E5E8] bg-white p-4">
                                    <p className="font-extrabold">{item.display_name || item.domain}</p>
                                    <p className="mt-2 text-xl font-extrabold">{item.price_label || (item.price == null ? '—' : money(item.price))}</p>
                                    <span className="mt-3 inline-flex min-h-11 items-center rounded-xl bg-brand-dark px-3 text-sm font-extrabold text-white">View</span>
                                </A>
                            ))}
                        </div>
                    </section>
                ) : null}
            </div>
        </section>
    );
}

function Facts({ offer }) {
    return (
        <section className="grid grid-cols-2 gap-2" aria-label="Publisher facts">
            <article className="rounded-2xl border border-[#E3E5E8] bg-white p-4 text-center"><p className="font-bold">{offer.primary_category || '—'}</p><p className="mt-1 text-sm text-[#4A5560]">Primary category</p></article>
            <article className="rounded-2xl border border-[#E3E5E8] bg-white p-4 text-center"><p className="font-bold">{offer.language || '—'}</p><p className="mt-1 text-sm text-[#4A5560]">Language</p></article>
            <article className="rounded-2xl border border-[#E3E5E8] bg-white p-4 text-center"><p className="font-bold">{offer.foreign_language_accepted === true ? 'Accepted' : offer.foreign_language_accepted === false ? 'Not accepted' : '—'}</p><p className="mt-1 text-sm text-[#4A5560]">Foreign-language anchors</p></article>
            <article className="rounded-2xl border border-[#E3E5E8] bg-white p-4 text-center"><p className="font-bold">{offer.google_news === true ? 'Included' : offer.google_news === false ? 'Not included' : '—'}</p><p className="mt-1 text-sm text-[#4A5560]">Google News</p></article>
        </section>
    );
}

function Metrics({ metrics, trends, domain }) {
    const boxes = [
        ['Authority', 'border-[#3D7BC4]', 'bg-[#3D7BC4] text-white', [
            ['DA', metrics.da, 'flat', '#3D7BC4'],
            ['PA', metrics.pa],
        ]],
        ['Trust', 'border-[#C0392B]', 'bg-[#FDE8E8] text-[#C0392B]', [
            ['TF', metrics.tf],
            ['CF', metrics.cf],
        ]],
        ['Strength', 'border-brand', 'bg-[#FFF1EC] text-brand', [
            ['DR', metrics.dr, trends.dr, '#FF4917'],
            ['Traffic', metrics.traffic, trends.traffic, '#FF4917'],
            ['Keywords', metrics.keywords],
        ]],
        ['Link profile', 'border-[#17663A]', 'bg-[#E7F5EC] text-[#17663A]', [
            ['Backlinks', metrics.backlinks],
            ['Ref. domains', metrics.referring_domains],
            ['Spam', metrics.spam, null, null, true],
        ]],
    ];

    return (
        <section className="grid gap-2" aria-label="Website metrics">
            {boxes.map(([name, border, badge, stats]) => (
                <article key={name} className={`flex overflow-hidden rounded-xl border bg-white ${border}`}>
                    <div className={`grid w-24 shrink-0 place-items-center px-2 text-center text-sm font-extrabold ${badge}`}>{name}</div>
                    <div className="flex flex-wrap gap-4 p-3">
                        {stats.map(([label, value, series, color, percent]) => (
                            <div key={label} className="min-w-16">
                                <p className="text-xs text-[#4A5560] underline">{label}</p>
                                <p className={`text-xl font-extrabold ${percent && Number(value) >= 30 ? 'text-[#A3221F]' : ''}`}>{percent && Number.isFinite(Number(value)) ? `${figure(value)}%` : figure(value)}</p>
                                {series === 'flat' ? <Sparkline flat value={value} color={color} showValue={false} label={`${label} trend, flat preview`} /> : null}
                                {Array.isArray(series) && series.length >= 2 ? <Sparkline points={series} seed={`${domain}-${label}`} color={color} showValue={false} label={`${label} over 12 months`} /> : null}
                            </div>
                        ))}
                    </div>
                </article>
            ))}
        </section>
    );
}

function Countries({ rows, others }) {
    if (!rows.length) {
        return null;
    }

    return (
        <section className="rounded-2xl border border-[#E3E5E8] bg-white p-4">
            <h2 className="text-lg font-extrabold">Top countries</h2>
            <ol className="mt-3 space-y-2">
                {rows.map((row, index) => (
                    <li key={row.code}>
                        <div className="flex items-center justify-between gap-3 text-sm font-bold">
                            <span className="inline-flex items-center gap-2"><Flag code={row.code} /><span>{row.name}</span></span>
                            <span>{row.label || `${Number(row.percent).toFixed(2)}%`}</span>
                        </div>
                        <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-[#E3E5E8]"><span className={`block h-full ${index === 0 ? 'bg-brand' : 'bg-[#3D7BC4]'}`} style={{ width: `${Math.min(100, Number(row.percent) || 0)}%` }} /></div>
                    </li>
                ))}
            </ol>
            {others ? <p className="mt-3 text-center text-xs text-[#4A5560]">Other countries {Number(others).toFixed(2)}%</p> : null}
        </section>
    );
}

function Description({ offer }) {
    return (
        <div className="mt-4 space-y-4">
            <p className="rounded-xl border-l-[3px] border-brand bg-[#FFF8F6] p-4 leading-7">{offer.lead}</p>
            <div className="flex flex-wrap gap-2">
                {(offer.categories || []).map((category) => (
                    <A key={category} href={`/niches/${category.toLowerCase().replace(/&/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}`} className="inline-flex min-h-9 items-center rounded-full bg-[#FFF1EC] px-3 text-sm font-extrabold text-brand-dark">{category}</A>
                ))}
            </div>
            <p className="text-sm text-[#4A5560]">Organic keywords: {compact(offer.metrics?.keywords) || '—'}</p>
        </div>
    );
}

function Reviews({ reviews }) {
    const items = reviews.items || reviews.list || [];

    if (!items.length) {
        return <p className="mt-4 rounded-2xl border border-dashed border-[#E3E5E8] p-6 text-center text-[#4A5560]">No reviews yet.</p>;
    }

    return (
        <ul className="mt-4 space-y-3">
            {items.map((review) => (
                <li key={`${review.name}-${review.created_at || review.body}`} className="rounded-2xl border border-[#E3E5E8] p-4">
                    <p className="font-bold">{review.name || 'Customer'} <span className="text-brand">{'★'.repeat(Number(review.rating) || 0)}</span></p>
                    <p className="mt-2 text-sm text-[#4A5560]">{review.body || review.comment}</p>
                </li>
            ))}
        </ul>
    );
}
