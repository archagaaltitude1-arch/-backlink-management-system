import { useState } from 'react';
import { A, money } from './chrome';
import { Flag, Sparkline, compact } from './marks';

function ReasonMark({ name }) {
    const paths = {
        shield: <><path d="M12 3 5 6v6c0 4.2 2.8 7.2 7 8.5 4.2-1.3 7-4.3 7-8.5V6Z" /><path d="m9 12 2 2 4-4" /></>,
        search: <><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></>,
        chart: <><path d="M4 19V5" /><path d="M4 19h16" /><path d="m8 15 3.5-4 2.5 2.5L19 8" /></>,
        layers: <><path d="m12 3 9 5-9 5L3 8Z" /><path d="m3 13 9 5 9-5" /></>,
        file: <><path d="M7 3h7l5 5v13H7Z" /><path d="M14 3v5h5" /><path d="M9 13h6M9 17h6" /></>,
        users: <><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20" /><circle cx="10" cy="8" r="3" /><path d="M20 20v-1.5a3.2 3.2 0 0 0-2.4-3.1" /><path d="M16.5 5.2a3 3 0 0 1 0 5.6" /></>,
    };

    return (
        <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            {paths[name] || <circle cx="12" cy="12" r="8" />}
        </svg>
    );
}

const servicePhotos = {
    'contextual-link': ['/images/services/service-contextual.jpg', 'Open book with one glowing line under a warm lamp'],
    'text-link': ['/images/services/service-text-link.jpg', 'Two laptops joined by a thread of orange light'],
    'guest-post': ['/images/services/service-guest-post.jpg', 'Fountain pen on blank paper under a desk lamp'],
    'niche-edit': ['/images/services/service-niche-edit.jpg', 'Laptop page with an orange block inserted into the article'],
};

function ServicesShowcase({ cards, servicesPublished }) {
    const ordered = [
        ...cards.filter((card) => card.featured),
        ...cards.filter((card) => !card.featured),
    ];
    const [slug, setSlug] = useState(ordered[0]?.slug ?? '');
    const active = ordered.find((card) => card.slug === slug) || ordered[0];

    if (!active) {
        return null;
    }

    const photo = servicePhotos[active.slug];

    return (
        <section className="bg-[#0c0c0e] px-6 py-16 lg:px-12 lg:py-24" aria-labelledby="services-heading">
            <div className="mx-auto grid w-full items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p className="eyebrow text-brand">Our services</p>
                    <h2 id="services-heading" className="mt-3 max-w-xl text-4xl text-white lg:text-5xl">Link building services built around real results</h2>
                    <p className="mt-5 max-w-md text-[17px] leading-7 text-[#A7ADB4]">Order text links, contextual links, guest posts and niche edits on checked publisher websites—with clear metrics, fast delivery, and reports made for agencies and in-house teams.</p>
                    {servicesPublished ? null : <p className="mt-4 max-w-md text-sm leading-6 text-[#A7ADB4]" role="status">Service prices are not published yet. A starting price appears once that service is active.</p>}
                    <div className="mt-8 grid grid-cols-2 justify-items-start gap-3 xl:flex xl:flex-wrap" role="tablist" aria-label="Services">
                        {ordered.map((card) => {
                            const selected = card.slug === active.slug;

                            return (
                                <button
                                    key={card.slug}
                                    type="button"
                                    role="tab"
                                    aria-selected={selected}
                                    className={`min-h-11 shrink-0 whitespace-nowrap rounded-full px-5 text-[15px] font-bold ${selected ? 'bg-brand text-white shadow-[0_8px_24px_rgba(255,73,23,0.28)]' : 'border border-[#E3E5E8] bg-white text-[#152029] hover:border-brand'}`}
                                    onClick={() => setSlug(card.slug)}
                                >
                                    {card.title}
                                </button>
                            );
                        })}
                    </div>
                </div>
                <div className="overflow-hidden rounded-[28px] border border-[#E6E8EB] bg-white shadow-[0_18px_40px_rgba(21,32,41,0.08)]" role="tabpanel">
                    <div className="service-dots flex items-center gap-2 bg-[#f3f4f6] px-4 py-3">
                        <span className="h-2.5 w-2.5 rounded-full" />
                        <span className="h-2.5 w-2.5 rounded-full" />
                        <span className="h-2.5 w-2.5 rounded-full" />
                        <p className="min-w-0 flex-1 truncate text-center">
                            <span className="inline-block max-w-full truncate rounded-full border border-[#e5e7eb] bg-white px-4 py-1 text-xs font-semibold text-[#6b7280]">services / {active.slug}</span>
                        </p>
                    </div>
                    <div className="grid items-center gap-6 p-6 sm:p-8 md:grid-cols-[minmax(0,1.1fr)_minmax(160px,0.9fr)]">
                        <div>
                            <p className="text-xs font-extrabold tracking-[0.16em] text-brand uppercase">{active.featured ? 'Most popular' : 'Service'}</p>
                            <h3 className="mt-3 text-3xl text-[#152029] lg:text-4xl">{active.title}</h3>
                            {active.price ? <p className="mt-3 text-lg font-extrabold text-brand-dark">{active.price}</p> : null}
                            <p className="mt-3 text-[15px] leading-7 text-[#4A5560]">{active.description}</p>
                            <p className="mt-2 text-sm text-[#152029]"><span className="font-bold">Best for:</span> {active.best_for}</p>
                            <ul className="mt-5 flex flex-wrap gap-2">
                                {(active.options || []).slice(0, 3).map((option) => (
                                    <li key={option.label}><A href={option.href} className="inline-flex min-h-9 items-center rounded-full border border-[#E5E7EB] bg-[#F7F8FA] px-3 text-sm font-semibold text-[#152029] hover:border-brand hover:text-brand">{option.label}</A></li>
                                ))}
                            </ul>
                            <A href={active.cta_href || `/services/${active.slug}`} className="btn-primary mt-6">{active.button}</A>
                        </div>
                        <img src={photo?.[0]} alt={photo?.[1] || active.title} className="h-56 w-full rounded-2xl object-cover md:h-full md:min-h-64" />
                    </div>
                </div>
            </div>
        </section>
    );
}

const reasons = [
    ['shield', 'Safe, Relevant Placements', 'Your links appear on websites that match your niche and already attract real readers. We never use link farms or private blog networks.', 'Explore Our Services', '/services', 'Checked websites'],
    ['search', 'A Marketplace You Can Inspect', 'See DR, DA, PA, Trust Flow, monthly traffic, spam score and price for every website, then filter by niche to build your shortlist.', 'See the publisher list', '/publishers', 'Marketplace'],
    ['chart', 'Track Every Order', 'Watch each order move from submitted to published to verified, and receive the live URL and anchor details the moment it goes up.', null, null, 'Order updates'],
    ['layers', 'From One Link to Hundreds', 'Buy a single placement or prepaid credit packages. Credits are only reserved on submission and deducted once your link is verified.', 'See Packages', '/packages', 'Credits and packages'],
    ['file', 'Client-Ready Reports', 'Download white-label reports showing live URLs, anchors and metrics, ready to share with your own clients.', null, null, 'White-label reports'],
    ['users', 'Real SEO Team Support', 'Our specialists check content relevance, review every published page and help you choose the right link type for each target URL.', 'Contact Our Team', '/contact', 'SEO team'],
];

export function HomePage({ page }) {
    const data = page.data || {};
    const publishers = data.publisherCount == null ? 'Checked' : `${Number(data.publisherCount).toLocaleString()}+`;
    const startHref = page.shell?.auth ? '/marketplace' : '/register';
    const dashboardHref = '/customer/dashboard';
    const cards = data.serviceCards || [];
    const samples = data.samplePublishers || [];
    const niches = data.nicheTiles || [];

    return (
        <div>
            <section className="relative flex min-h-[calc(100dvh-4.5rem)] items-center overflow-hidden bg-[#152029]">
                <video
                    className="pointer-events-none absolute inset-0 h-full w-full object-cover"
                    src="/videos/hero-dark.mp4"
                    autoPlay
                    muted
                    loop
                    playsInline
                    aria-hidden="true"
                    onLoadedMetadata={(event) => {
                        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                            event.currentTarget.pause();
                        }
                    }}
                />
                <div className="pointer-events-none absolute inset-0 bg-[#152029]/55" />
                <div className="relative z-10 mx-auto flex w-full max-w-4xl flex-col items-center px-5 py-14 text-center">
                    <p className="eyebrow inline-flex rounded-full border border-[#E3E5E8] bg-white/90 px-4 py-2 text-brand-dark">Link Building Agency · White-Hat Only</p>
                    <h1 className="mt-5 w-[min(1120px,calc(100vw-2.5rem))] font-display text-[42px] leading-tight text-white lg:text-[68px]">Earn Higher Rankings<br className="hidden lg:block" /> with Backlinks from <span className="text-brand">Real, Traffic-Driven</span> <br className="hidden lg:block" /><span className="text-brand">Websites</span></h1>
                    <p className="mt-5 max-w-3xl text-[17px] leading-8 text-white/80">Pick from {publishers} checked publisher websites and grow your search visibility, visitors and sales with text links, contextual links, guest posts and niche edits. Every one is verified before you get your report.</p>
                    <div className="mt-6 flex flex-wrap justify-center gap-3">
                        <A href={dashboardHref} className="btn-primary">Start Building Links</A>
                        <A href="/publishers" className="btn-outline">See the publisher list</A>
                    </div>
                    <ul className="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2">
                        {[`${publishers} Checked Publishers`, 'White-Hat Placements Only', 'Delivered in 3–14 Days', 'Every Link Verified'].map((item) => (
                            <li key={item} className="flex items-center gap-2 text-[15px] font-bold text-white"><span className="grid h-6 w-6 place-items-center rounded-full bg-[#FFF1EC] text-brand-dark">✓</span>{item}</li>
                        ))}
                    </ul>
                </div>
            </section>

            <section className="why-pro px-5 py-16 lg:py-20" aria-labelledby="why-heading">
                <div className="mx-auto w-full max-w-7xl">
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(16rem,0.7fr)] lg:gap-16">
                        <div>
                            <p className="eyebrow text-[#8B939C]">[[ Why BacklinkPro ]]</p>
                            <h2 id="why-heading" className="mt-4 max-w-xl text-4xl text-[#152029] lg:text-[44px]">One Partner for Text Links, Contextual Links, Guest Posts & Niche Edits</h2>
                        </div>
                        <p className="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:pt-12">Why brands and agencies rely on BacklinkPro for safe, lasting link building.</p>
                    </div>
                    <dl className="mt-10 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-4">
                        {[[publishers, 'Checked Publishers'], ['[N]+', 'Campaigns Completed'], ['3–14 Days', 'Delivery Window'], ['[YEAR]', 'Serving Clients Since']].map(([figure, label]) => (
                            <div key={label}>
                                <dt className="text-2xl font-extrabold text-[#152029]">{figure}</dt>
                                <dd className="mt-1 text-sm font-semibold text-[#4A5560]">{label}</dd>
                            </div>
                        ))}
                    </dl>
                    <div className="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {reasons.slice(0, 4).map(([icon, title, text], index) => (
                            <article key={title} className="flex h-full flex-col rounded-xl border border-[#E6E8EB] bg-white p-5 shadow-[0_10px_28px_rgba(21,32,41,0.04)]">
                                <div className="flex items-start justify-between gap-3">
                                    <span className="grid h-11 w-11 place-items-center rounded-full bg-[#152029] text-white">
                                        <ReasonMark name={icon} />
                                    </span>
                                    <span className="text-sm font-semibold tracking-wide text-[#C5CBD1]">{String(index + 1).padStart(2, '0')}</span>
                                </div>
                                <h3 className="mt-6 text-[17px] text-[#152029]">{title}</h3>
                                <p className="mt-2 text-sm leading-6 text-[#4A5560]">{text}</p>
                            </article>
                        ))}
                    </div>
                    <div className="why-dots mt-6 flex justify-center gap-1.5" aria-hidden="true">
                        <span className="h-1.5 w-1.5 rounded-full" />
                        <span className="h-1.5 w-1.5 rounded-full" />
                    </div>
                </div>
            </section>

            <ServicesShowcase cards={cards} servicesPublished={Boolean(data.servicesPublished)} />

            <section className="bg-white px-5 py-16 text-center">
                <div className="mx-auto w-full">
                    <p className="text-sm font-bold tracking-widest text-brand-dark uppercase">Do it yourself</p>
                    <h2 className="mt-3 text-3xl font-extrabold lg:text-4xl">Prefer to Choose <span className="text-brand">Every Website</span> Yourself?</h2>
                    <p className="mx-auto mt-4 max-w-3xl text-lg font-bold">Shop contextual placements straight from our publisher marketplace. Filter by metrics, niche and price, see everything up front, and order in minutes.</p>
                    <div className="mt-10 grid gap-6 text-center md:grid-cols-2">
                        {[
                            [`${publishers} Checked Publishers`, 'Each website is reviewed by our team for real traffic and genuine content before it\'s listed.'],
                            ['Filter by Niche & Metrics', 'Narrow the list by DR, DA, PA, traffic, spam score, niche and price to match your campaign.', data.nicheCount],
                            ['Clear SEO Metrics', 'Authority, traffic and spam data are shown on every listing and on each website\'s details page.'],
                            ['No Hidden Networks', 'Every publisher is a real, named website. Check the full details before you buy.'],
                            ['You Stay in Control', 'Set your target URL and anchor text. Our team writes and publishes the placement.'],
                            ['Delivery in 3–14 Days', 'Most placements are live within two weeks, with a verified report when done.'],
                        ].map(([title, text, count]) => (
                            <article key={title} className="rounded-2xl border border-[#FFD5C8] bg-[#FFF7F4] px-5 py-6">
                                <h3 className="text-lg font-extrabold">{title}</h3>
                                <p className="mt-2 text-[15px] text-[#4A5560]">{text}</p>
                                {count != null ? <p className="mt-1 font-bold text-brand-dark">{Number(count).toLocaleString()} active {Number(count) === 1 ? 'niche' : 'niches'}</p> : null}
                            </article>
                        ))}
                    </div>
                    <div className="mt-10 overflow-hidden rounded-[20px] border border-[#E3E5E8] text-left shadow-lg" aria-hidden="true">
                        <div className="flex flex-wrap items-center gap-2 border-b border-[#E3E5E8] bg-[#F7F8F9] px-4 py-3 text-xs font-bold text-[#4A5560]">
                            <span className="h-2 w-2 rounded-full bg-[#FF5F57]" /><span className="h-2 w-2 rounded-full bg-[#FEBC2E]" /><span className="h-2 w-2 rounded-full bg-[#28C840]" />
                            {['All', 'DR', 'Traffic', 'Niche', 'Price'].map((chip) => <span key={chip} className="rounded-md bg-white px-2 py-1">{chip}</span>)}
                            <span className="ml-auto rounded-full bg-[#FFF1EC] px-2 py-0.5 text-[10px] font-extrabold tracking-wide text-brand-dark uppercase">Sample</span>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[1100px] text-center text-[13px]">
                                <thead className="bg-navy text-xs text-white"><tr>{['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic', 'Price', 'Buy now'].map((heading) => <th key={heading} className="px-2 py-3">{heading}</th>)}</tr></thead>
                                <tbody>
                                    {samples.map((sample) => {
                                        const country = sample.top_countries?.[0];
                                        const code = String(country?.[0] || '');

                                        return (
                                            <tr key={sample.domain} className="border-t border-[#B7C0C8]">
                                                <td className="px-2 py-3 font-extrabold">{sample.domain}</td>
                                                <td className="font-extrabold">{sample.as}</td>
                                                <td className="font-extrabold">{sample.da}</td>
                                                <td className="font-extrabold">{sample.dr}</td>
                                                <td>{sample.niches?.[0] || 'Technology'}</td>
                                                <td>{sample.language}</td>
                                                <td><div className="flex justify-center"><Sparkline seed={`${sample.domain}-ahrefs`} color="#E98989" value={sample.traffic} /></div></td>
                                                <td className="font-extrabold">{compact(sample.keywords)}</td>
                                                <td>{country ? <span className="inline-flex items-center justify-center gap-1.5 font-bold"><Flag code={code.toUpperCase() === 'UK' ? 'gb' : code} />{Number(country[1] || 0).toFixed(1)}%</span> : null}</td>
                                                <td><div className="flex justify-center"><Sparkline seed={`${sample.domain}-semrush`} color="#7DCEA0" value={sample.semrush_traffic} /></div></td>
                                                <td className="font-extrabold">{money(sample.price)}</td>
                                                <td><A href={`/publish/${sample.domain}`} className="inline-flex min-h-9 items-center rounded-[10px] bg-brand-dark px-3 text-[11px] font-extrabold text-white uppercase">Publish</A></td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <ul className="mt-6 flex flex-wrap justify-center gap-2">
                        {['Verified Websites', 'Metrics Updated Regularly', 'White-Hat Only'].map((chip) => <li key={chip} className="inline-flex min-h-11 items-center rounded-full border border-navy px-4 text-sm font-bold">{chip}</li>)}
                    </ul>
                    <div className="mt-6"><A href="/publishers#all-publishers" className="btn-primary">See the publisher list</A></div>
                </div>
            </section>

            <section className="bg-[#152029] px-6 py-16 lg:px-12 lg:py-24" aria-labelledby="home-niches-heading">
                <div className="overflow-hidden rounded-3xl border border-[#E3E5E8] bg-white">
                    <div className="grid items-end gap-4 border-b border-[#E6E8EB] px-6 py-8 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.55fr)] lg:px-10">
                        <div>
                            <p className="eyebrow text-brand-dark">Niches</p>
                            <h2 id="home-niches-heading" className="mt-3 max-w-xl text-4xl text-[#152029] lg:text-[44px]">Browse Publisher Websites by Niche</h2>
                        </div>
                        <p className="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:justify-self-end">Open a niche to see the publisher websites that accept guest posts, contextual links, niche edits and text links.</p>
                    </div>
                    <div className="-mr-px -mb-px grid bg-white sm:grid-cols-2 lg:grid-cols-4">
                        {niches.map((niche, index) => (
                            <A key={niche.slug} href={`/niches/${niche.slug}`} className="group flex min-h-16 items-center gap-3 border-r border-b border-[#E6E8EB] bg-white px-6 py-4 transition hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand">
                                <span className="w-6 shrink-0 font-sans text-[13px] font-bold text-[#8B939C] tabular-nums group-hover:text-brand">{String(index + 1).padStart(2, '0')}</span>
                                <span className="min-w-0 truncate font-sans text-[15px] font-bold text-[#152029]">{niche.name}</span>
                                <span aria-hidden="true" className="ml-auto text-[#C5CBD1] transition group-hover:translate-x-0.5 group-hover:text-brand">→</span>
                            </A>
                        ))}
                    </div>
                </div>
            </section>

            <section className="bg-[#F2F2F2] px-5 pb-16">
                <div className="order-card mx-auto grid w-full overflow-hidden rounded-[28px] border-t-[6px] border-brand shadow-lg lg:grid-cols-[1.25fr_0.75fr]">
                    <div className="order-card-copy flex flex-col justify-center px-6 py-12 text-center lg:px-10">
                        <p className="text-xs font-extrabold tracking-widest text-brand-dark uppercase">Get started</p>
                        <h2 className="mt-3 text-3xl font-extrabold lg:text-4xl">Ready to Order Your First Backlink?</h2>
                        <p className="mx-auto mt-4 max-w-xl text-[17px] leading-7 text-[#4A5560]">Choose a checked publisher website, set your target URL and anchor, and we send the live URL once the link is verified.</p>
                        <div className="mt-7 flex flex-wrap justify-center gap-3">
                            <A href={startHref} className="btn-primary">Start Your Order</A>
                            <A href="/publishers" className="btn-outline">See the publisher list</A>
                        </div>
                    </div>
                    <div className="order-card-side flex flex-col justify-center px-6 py-12 text-center text-white">
                        <h3 className="text-lg font-extrabold">How an order starts</h3>
                        <ol className="mt-6 space-y-5">
                            {[['Choose a website', 'Open the publisher list and compare the metrics stored on each listing.'], ['Set the placement', 'Add your target URL, anchor, and topic. Our team publishes the placement.'], ['Get the live URL', 'We send the verified page address once the link is published.']].map(([title, text], index) => (
                                <li key={title}><span className="mx-auto grid h-11 w-11 place-items-center rounded-full bg-brand-dark text-lg font-extrabold">{index + 1}</span><p className="mt-2 font-extrabold">{title}</p><p className="mt-1 text-sm text-[#D5DCE3]">{text}</p></li>
                            ))}
                        </ol>
                    </div>
                </div>
            </section>
        </div>
    );
}
