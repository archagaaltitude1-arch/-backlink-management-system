import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { A, money } from './chrome';
import { compact } from './marks';

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
        <section className="bg-navy px-5 py-16 sm:px-8 lg:py-20" aria-labelledby="services-heading">
            <div className="mx-auto grid w-full max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-14">
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
                                    id={`home-service-${card.slug}`}
                                    aria-selected={selected}
                                    aria-controls="home-service-panel"
                                    tabIndex={selected ? 0 : -1}
                                    className={`min-h-11 shrink-0 whitespace-nowrap rounded-full px-5 text-[15px] font-bold ${selected ? 'bg-brand text-white shadow-[0_8px_24px_rgba(255,73,23,0.28)]' : 'border border-[#E3E5E8] bg-white text-[#152029] hover:border-brand'}`}
                                    onClick={() => setSlug(card.slug)}
                                    onKeyDown={(event) => {
                                        if (event.ctrlKey || event.metaKey || event.altKey) {
                                            return;
                                        }
                                        const index = ordered.findIndex((item) => item.slug === active.slug);
                                        const next = { ArrowRight: (index + 1) % ordered.length, ArrowLeft: (index - 1 + ordered.length) % ordered.length, Home: 0, End: ordered.length - 1 }[event.key];
                                        if (next === undefined) {
                                            return;
                                        }
                                        event.preventDefault();
                                        setSlug(ordered[next].slug);
                                        document.getElementById(`home-service-${ordered[next].slug}`)?.focus();
                                    }}
                                >
                                    {card.title}
                                </button>
                            );
                        })}
                    </div>
                </div>
                <div id="home-service-panel" aria-labelledby={`home-service-${active.slug}`} className="min-w-0 overflow-hidden rounded-[24px] border border-[#E6E8EB] bg-white" role="tabpanel" tabIndex={0}>
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
    const navigate = useNavigate();
    const data = page.data || {};
    const publishers = data.publisherCount == null ? 'Checked' : `${Number(data.publisherCount).toLocaleString()}+`;
    const startHref = page.shell?.auth ? '/marketplace' : '/register';
    const cards = data.serviceCards || [];
    const samples = data.samplePublishers || [];
    const niches = data.nicheTiles || [];

    return (
        <div className="home-page">
            <section className="home-hero px-5 py-14 sm:px-8 lg:py-24">
                <div className="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-16">
                    <div className="min-w-0">
                        <p className="home-hero-kicker">Publisher marketplace · Managed link building</p>
                        <h1 className="home-tech-heading mt-7">Build backlinks.<br /><span>Reach the right</span><br />audience.</h1>
                        <p className="mt-7 max-w-xl text-lg font-medium leading-8 text-white">Real publishers. Relevant placements. Verified live links.</p>
                        <p className="mt-4 max-w-lg text-base leading-7 text-[#acb6c0]">Compare websites by niche, authority, traffic and price. Choose where your brand appears, and follow each placement from order to publication.</p>
                        <ul className="mt-8 grid grid-cols-1 gap-3 rounded-xl border border-white/10 bg-white/[0.025] p-4 text-xs font-bold tracking-wide text-[#c3cbd3] sm:grid-cols-3">
                            <li>{publishers} checked publishers</li>
                            <li>3–14 day delivery</li>
                            <li>Verified placement report</li>
                        </ul>
                        <div className="mt-7 flex flex-col gap-3 sm:flex-row">
                            <A href={startHref} className="inline-flex min-h-14 items-center justify-center rounded-xl bg-brand px-7 text-sm font-bold tracking-wide text-navy shadow-[0_8px_32px_#ff49172b] hover:bg-[#ff7049]">Start Building Links</A>
                            <A href="/publishers" className="inline-flex min-h-14 items-center justify-center rounded-xl border border-white/20 bg-white/5 px-7 text-sm font-bold text-white hover:border-brand/60">Explore publishers</A>
                        </div>
                        <div className="mt-8 flex flex-wrap gap-x-7 gap-y-3 border-t border-white/10 pt-6 text-xs font-medium text-[#8e9aa7]">
                            <A href="/services/contextual-link" className="hover:text-white">Contextual links</A>
                            <A href="/services/guest-post" className="hover:text-white">Guest posts</A>
                            <A href="/sites/link-insertions-niche-edits" className="hover:text-white">Niche edits</A>
                        </div>
                    </div>

                    <div className="home-discovery-panel min-w-0 overflow-hidden rounded-3xl border border-white/10 bg-[#0d1319]">
                        <div className="home-hero-graphic relative overflow-hidden">
                            <img src="/images/services/hero-publisher-ribbon-v1.png" alt="A luminous orange ribbon connecting three sculptural publisher page frames" width="1536" height="1024" fetchPriority="high" className="h-full w-full object-cover" />
                            <div className="absolute inset-0 bg-gradient-to-t from-[#0d1319] via-transparent to-[#0d1319]/20" />
                            <div className="absolute inset-x-6 bottom-3">
                                <p className="text-xs font-bold tracking-[0.16em] text-[#ff9b7f] uppercase">Your next placement</p>
                                <h2 className="mt-2 text-2xl text-white">Connect with the right publishers.</h2>
                            </div>
                        </div>
                        <div className="px-6 pb-6 sm:px-7 sm:pb-7">
                            <form action="/publishers" method="GET" className="mt-4" onSubmit={(event) => {
                                event.preventDefault();
                                const niche = new FormData(event.currentTarget).get('niche');
                                navigate(niche ? `/publishers?niche=${encodeURIComponent(niche)}` : '/publishers');
                            }}>
                                <label htmlFor="home-niche" className="mb-2 block text-xs font-bold tracking-wide text-[#aeb8c3] uppercase">Find publishers in your niche</label>
                                <div className="flex flex-col gap-2 sm:flex-row">
                                    <select id="home-niche" name="niche" className="min-h-12 min-w-0 flex-1 rounded-lg border border-white/15 bg-[#152029] px-3 text-sm text-white">
                                        <option value="">All niches</option>
                                        {niches.map((niche) => <option key={niche.slug} value={niche.name}>{niche.name}</option>)}
                                    </select>
                                    <button type="submit" className="min-h-12 rounded-lg bg-brand px-5 text-sm font-bold text-navy hover:bg-[#ff7049]">Browse publishers</button>
                                </div>
                            </form>
                            <div className="mt-6 flex items-center justify-between gap-3 text-xs text-[#82909d]"><span>Marketplace preview</span><span>Sample listings</span></div>
                            <div className="mt-2 divide-y divide-white/10">
                                {samples.slice(0, 2).map((sample) => (
                                    <A key={sample.domain} href={'/publish/' + sample.domain} className="flex flex-wrap items-center justify-between gap-3 py-4 text-sm hover:text-[#ff9b7f]">
                                        <span className="min-w-0 break-all text-[#dbe1e7]">{sample.domain}<span className="mt-1 block text-xs text-[#82909d]">DR {sample.dr} · {sample.niches?.[0] || 'Technology'}</span></span>
                                        <span className="font-bold text-white">{money(sample.price)}</span>
                                    </A>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section className="why-pro px-5 py-16 lg:py-20" aria-labelledby="why-heading">
                <div className="mx-auto w-full max-w-7xl">
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(16rem,0.7fr)] lg:gap-16">
                        <div>
                            <p className="eyebrow text-brand-dark">Why BacklinkPro</p>
                            <h2 id="why-heading" className="mt-4 max-w-xl text-4xl text-[#152029] lg:text-[44px]">One Partner for Text Links, Contextual Links, Guest Posts & Niche Edits</h2>
                        </div>
                        <p className="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:pt-12">Why brands and agencies rely on BacklinkPro for safe, lasting link building.</p>
                    </div>
                    <dl className="mt-8 grid grid-cols-1 divide-y divide-navy/10 border-y border-navy/10 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                        {[[publishers, 'Checked publishers'], ['3–14 days', 'Typical delivery'], ['Live URL', 'Verified placement report']].map(([figure, label]) => (
                            <div key={label} className="px-5 py-5 first:pl-0">
                                <dt className="text-2xl font-extrabold text-[#152029]">{figure}</dt>
                                <dd className="mt-1 text-sm font-semibold text-[#4A5560]">{label}</dd>
                            </div>
                        ))}
                    </dl>
                    <div className="mt-8 grid gap-0 divide-y divide-navy/10 sm:grid-cols-2 sm:divide-y-0 xl:grid-cols-4">
                        {reasons.slice(0, 4).map(([icon, title, text]) => (
                            <article key={title} className="flex h-full flex-col p-5 first:pl-0">
                                <div className="flex items-start justify-between gap-3">
                                    <span className="grid h-11 w-11 place-items-center rounded-full bg-[#152029] text-white">
                                        <ReasonMark name={icon} />
                                    </span>
                                </div>
                                <h3 className="mt-6 text-[17px] text-[#152029]">{title}</h3>
                                <p className="mt-2 text-sm leading-6 text-[#4A5560]">{text}</p>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <ServicesShowcase cards={cards} servicesPublished={Boolean(data.servicesPublished)} />

            <section className="bg-white px-5 py-16 text-center">
                <div className="mx-auto w-full max-w-7xl">
                    <p className="text-sm font-bold tracking-widest text-brand-dark uppercase">Do it yourself</p>
                    <h2 className="mt-3 text-3xl font-extrabold lg:text-4xl">Prefer to Choose <span className="text-brand">Every Website</span> Yourself?</h2>
                    <p className="mx-auto mt-4 max-w-2xl text-base leading-7 text-[#4A5560]">Compare publishers by niche, traffic, authority and price. Review each website before choosing your placement.</p>
                    <div className="mt-10 grid gap-4 text-left sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            [`${publishers} Checked Publishers`, 'Each website is reviewed by our team for real traffic and genuine content before it\'s listed.'],
                            ['Filter by Niche & Metrics', 'Narrow the list by DR, DA, PA, traffic, spam score, niche and price to match your campaign.', data.nicheCount],
                            ['Clear SEO Metrics', 'Authority, traffic and spam data are shown on every listing and on each website\'s details page.'],
                            ['No Hidden Networks', 'Every publisher is a real, named website. Check the full details before you buy.'],
                            ['You Stay in Control', 'Set your target URL and anchor text. Our team writes and publishes the placement.'],
                            ['Delivery in 3–14 Days', 'Most placements are live within two weeks, with a verified report when done.'],
                        ].map(([title, text, count]) => (
                            <article key={title} className="rounded-2xl border border-[#E3E5E8] bg-[#F2F2F2]/60 px-5 py-6">
                                <h3 className="text-lg font-extrabold">{title}</h3>
                                <p className="mt-2 text-[15px] text-[#4A5560]">{text}</p>
                                {count != null ? <p className="mt-1 font-bold text-brand-dark">{Number(count).toLocaleString()} active {Number(count) === 1 ? 'niche' : 'niches'}</p> : null}
                            </article>
                        ))}
                    </div>
                    <div className="mt-10 overflow-hidden rounded-2xl border border-navy/15 text-left">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-navy/10 bg-[#faf8f5] px-5 py-4">
                            <p className="text-sm font-medium text-navy">A preview of the marketplace</p>
                            <span className="text-xs text-navy/55">Sample publisher data</span>
                        </div>
                        <div className="overflow-x-auto" role="region" aria-label="Sample publisher comparison" tabIndex={0}>
                            <table className="w-full min-w-[600px] text-left text-sm">
                                <thead className="border-b border-navy/10 bg-white text-xs text-navy/60">
                                    <tr>{['Publisher', 'Niche', 'DR', 'Monthly traffic', 'Price', 'Details'].map((label, index) => <th key={index} scope="col" className="px-5 py-4 font-medium">{label}</th>)}</tr>
                                </thead>
                                <tbody className="divide-y divide-navy/10">
                                    {samples.map((sample) => (
                                        <tr key={sample.domain} className="transition hover:bg-[#faf8f5]">
                                            <th scope="row" className="px-5 py-5 font-medium text-navy">{sample.domain}</th>
                                            <td className="px-5 py-5 text-navy/65">{sample.niches?.[0] || 'Technology'}</td>
                                            <td className="px-5 py-5 tabular-nums">{sample.dr}</td>
                                            <td className="px-5 py-5 tabular-nums">{compact(sample.traffic)}</td>
                                            <td className="px-5 py-5 font-medium tabular-nums">{money(sample.price)}</td>
                                            <td className="px-5 py-5"><A href={'/publish/' + sample.domain} aria-label={'View details for ' + sample.domain} className="inline-flex min-h-11 items-center font-medium text-brand-dark underline-offset-4 hover:underline">View details</A></td>
                                        </tr>
                                    ))}
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
                <div className="mx-auto max-w-7xl overflow-hidden rounded-3xl border border-[#E3E5E8] bg-white">
                    <div className="grid items-end gap-4 border-b border-[#E6E8EB] px-6 py-8 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.55fr)] lg:px-10">
                        <div>
                            <p className="eyebrow text-brand-dark">Niches</p>
                            <h2 id="home-niches-heading" className="mt-3 max-w-xl text-4xl text-[#152029] lg:text-[44px]">Browse Publisher Websites by Niche</h2>
                        </div>
                        <p className="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:justify-self-end">Open a niche to see the publisher websites that accept guest posts, contextual links, niche edits and text links.</p>
                    </div>
                    <div className="-mr-px -mb-px grid bg-white sm:grid-cols-2 lg:grid-cols-4">
                        {niches.map((niche) => (
                            <A key={niche.slug} href={`/niches/${niche.slug}`} className="group flex min-h-16 items-center gap-3 border-r border-b border-[#E6E8EB] bg-white px-6 py-4 transition hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand">
                                <span className="min-w-0 truncate font-sans text-[15px] font-bold text-[#152029]">{niche.name}</span>
                                <span aria-hidden="true" className="ml-auto text-[#C5CBD1] transition group-hover:translate-x-0.5 group-hover:text-brand">→</span>
                            </A>
                        ))}
                    </div>
                </div>
            </section>

            <section className="bg-[#F2F2F2] px-5 pb-16">
                <div className="order-card mx-auto grid w-full max-w-7xl overflow-hidden rounded-[28px] border-t-4 border-brand lg:grid-cols-[1.25fr_0.75fr]">
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
