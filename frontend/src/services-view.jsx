import { useState } from 'react';
import { A, Flash, money } from './chrome';
import { PublisherBoard } from './listings';

const marks = {
    check: ['M20 6 9 17l-5-5'],
    shield: ['M12 3 5 6v6c0 4.2 2.8 7.2 7 8.5 4.2-1.3 7-4.3 7-8.5V6Z', 'm9 12 2 2 4-4'],
    chart: ['M4 19V5', 'M4 19h16', 'm8 15 3.5-4 2.5 2.5L19 8'],
    layers: ['m12 3 9 5-9 5L3 8Z', 'm3 13 9 5 9-5'],
    users: ['M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20', 'M20 20v-1.5a3.2 3.2 0 0 0-2.4-3.1', 'M16.5 5.2a3 3 0 0 1 0 5.6'],
    globe: ['M2 12h20', 'M12 4c2.2 2.4 3.3 5.1 3.3 8S14.2 17.6 12 20c-2.2-2.4-3.3-5.1-3.3-8S9.8 6.4 12 4Z'],
    link: ['M10 13a5 5 0 0 0 7.1.1l1.4-1.4a5 5 0 0 0-7.1-7.1L10.2 5.8', 'M14 11a5 5 0 0 0-7.1-.1L5.5 12.3a5 5 0 0 0 7.1 7.1l1.2-1.2'],
    type: ['M5 6h14', 'M12 6v12', 'M8 18h8'],
    article: ['M6 4h9l3 3v13H6Z', 'M15 4v3h3', 'M9 11h6M9 15h4'],
    pen: ['M4 20h4l10-10-4-4L4 16Z', 'm13 7 4 4'],
    edit: ['M4 6h10', 'M4 12h7', 'M4 18h6', 'm14 15 6-6-2-2-6 6v2h2Z'],
};

const heroBackgrounds = {
    'guest-post': ['/images/services/hero-guest-post.jpg', 'Two editors reviewing an article under a desk lamp at night'],
    'contextual-link': ['/images/services/hero-contextual.jpg', 'Three colleagues reading a laptop screen in a dark office'],
    'text-link': ['/images/services/hero-text-link.jpg', 'Two people with laptops facing each other at night'],
    'niche-edit': ['/images/services/hero-niche-edit.jpg', 'Two editors marking a sentence into an existing article at night'],
};

const areaTones = {
    Technology: ['border-[#C9DCF2] bg-[#E7F0FA]', 'bg-[#1D4E89] text-white'],
    Finance: ['border-[#C3E6CC] bg-[#E7F6EA]', 'bg-[#1B7A38] text-white'],
    Health: ['border-[#F6D0D8] bg-[#FDECEF]', 'bg-[#BE123C] text-white'],
    Business: ['border-[#D9D2F2] bg-[#F1ECFA]', 'bg-[#6D28D9] text-white'],
    'Travel & Lifestyle': ['border-[#C5E6F5] bg-[#E6F4FB]', 'bg-[#0369A1] text-white'],
    Education: ['border-[#F3DFB4] bg-[#FFF4DE]', 'bg-[#B45309] text-white'],
};

const pointTones = [
    'bg-[#FFF1EC] text-[#D63A10]',
    'bg-[#E7F0FA] text-[#1D4E89]',
    'bg-[#E7F6EA] text-[#1B7A38]',
    'bg-[#FFF4DE] text-[#B45309]',
];

const nicheChipTones = {
    Technology: 'border-[#C9DCF2] bg-[#E7F0FA] text-[#1D4E89]',
    'Software & IT': 'border-[#D9D2F2] bg-[#F1ECFA] text-[#6D28D9]',
    'Software and IT': 'border-[#D9D2F2] bg-[#F1ECFA] text-[#6D28D9]',
    Business: 'border-[#D5DCE3] bg-[#EEF1F4] text-[#152029]',
    Finance: 'border-[#C3E6CC] bg-[#E7F6EA] text-[#1B7A38]',
    Health: 'border-[#F6D0D8] bg-[#FDECEF] text-[#BE123C]',
    Education: 'border-[#F3DFB4] bg-[#FFF4DE] text-[#B45309]',
    Travel: 'border-[#C5E6F5] bg-[#E6F4FB] text-[#0369A1]',
    'Fashion & Beauty': 'border-[#F6D0D8] bg-[#FDECEF] text-[#BE123C]',
    'Fashion and Beauty': 'border-[#F6D0D8] bg-[#FDECEF] text-[#BE123C]',
    Lifestyle: 'border-[#F3DFB4] bg-[#FFF4DE] text-[#B45309]',
    'Real Estate': 'border-[#C9E8E3] bg-[#E5F6F2] text-[#0F766E]',
    'E-commerce': 'border-[#D9D2F2] bg-[#F1ECFA] text-[#6D28D9]',
    Automotive: 'border-[#D5DCE3] bg-[#F1F3F5] text-[#3E4C59]',
    Sports: 'border-[#C3E6CC] bg-[#E7F6EA] text-[#1B7A38]',
    Food: 'border-[#FFD9CC] bg-[#FFF1EC] text-[#D63A10]',
    'Agriculture & Farming': 'border-[#C3E6CC] bg-[#E7F6EA] text-[#1B7A38]',
    'Agriculture and Farming': 'border-[#C3E6CC] bg-[#E7F6EA] text-[#1B7A38]',
    Legal: 'border-[#D5DCE3] bg-[#EEF1F4] text-[#152029]',
};

const tierLevels = {
    'text-link': { Starter: 'starter', Growth: 'pro', Professional: 'professional', Premium: 'enterprise' },
    'contextual-link': { Starter: 'starter', Growth: 'pro', Professional: 'professional', Premium: 'enterprise' },
    'guest-post': { 'DR 50+': 'starter', 'DR 60+': 'pro', 'DR 70+': 'professional' },
};

function Mark({ name, className = 'h-6 w-6' }) {
    const lines = marks[name] || marks.link;

    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            {name === 'users' ? <circle cx="10" cy="8" r="3" /> : null}
            {name === 'globe' ? <circle cx="12" cy="12" r="8" /> : null}
            {lines.map((line) => <path key={line} d={line} />)}
        </svg>
    );
}

function benefitSpan(count, index) {
    const remainder = count % 3;
    const onLastRow = remainder !== 0 && index >= count - remainder;
    const wide = onLastRow ? (remainder === 1 ? 'xl:col-span-6' : 'xl:col-span-3') : 'xl:col-span-2';
    const fillsRow = count % 2 === 1 && index === count - 1 ? 'md:col-span-2' : '';

    return `${wide} ${fillsRow}`.trim();
}

function benefitIsWide(count, index) {
    const remainder = count % 3;

    return remainder !== 0 && index >= count - remainder;
}

function slugify(value) {
    return String(value || '').toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function highlighted(title, highlight) {
    const text = String(title || '');
    const mark = String(highlight || '');
    const start = mark === '' ? -1 : text.indexOf(mark);

    if (start < 0) {
        return text;
    }

    return <>{text.slice(0, start)}<span className="text-brand">{mark}</span>{text.slice(start + mark.length)}</>;
}

function packageHref(service, tier) {
    const level = tierLevels[service]?.[tier];

    return level ? `/packages/${level}-${service}` : `/publishers?service=${encodeURIComponent(service)}&tier=${encodeURIComponent(tier)}`;
}

export function ServicesOverview({ page }) {
    const overview = page.data.overview || {};
    const cards = page.data.cards || [];
    const rows = page.data.compareRows || [];

    return (
        <>
            <section className="relative overflow-hidden border-b border-white/10 bg-[#152029] px-6 py-16 text-white lg:px-14 lg:py-24">
                <img src="/images/services/services-overview-bg.jpg" alt="" className="pointer-events-none absolute inset-0 h-full w-full object-cover" />
                <div className="pointer-events-none absolute inset-0 bg-[#152029]/55" />
                <div className="relative mx-auto max-w-3xl text-center">
                    <p className="text-xs font-extrabold tracking-[0.1em] text-brand uppercase">Services</p>
                    <h1 className="mt-3 text-4xl leading-tight font-extrabold tracking-tight text-white lg:text-5xl">{overview.h1 || 'Link Building Services for Every Goal and Budget'}</h1>
                    <p className="mx-auto mt-4 max-w-[46ch] text-[17px] leading-7 text-white/80">{overview.intro}</p>
                </div>
            </section>

            <section className="bg-[#F6F7F9] px-6 py-10 lg:px-14 lg:py-16" aria-label="Service list">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => (
                        <article key={card.slug} className="flex flex-col rounded-[20px] border border-[#E3E5E8] bg-white p-6 shadow-[0_10px_28px_rgba(21,32,41,0.05)]">
                            <div className="flex items-start justify-between gap-3">
                                <span className="grid h-12 w-12 place-items-center rounded-[14px] bg-[#FFF1EC] text-brand"><Mark name={card.icon} /></span>
                                <p className="text-[15px] font-extrabold text-brand-dark">{card.price}</p>
                            </div>
                            <h2 className="mt-5 text-[22px] font-extrabold">{card.name}</h2>
                            <p className="mt-2 text-[15px] leading-6 text-[#4A5560]">{card.menu}</p>
                            <p className="mt-4 flex items-center gap-2 text-[13px] font-bold before:h-1.5 before:w-1.5 before:rounded-full before:bg-brand before:content-['']">{card.turnaround}</p>
                            <A href={`/services/${card.slug}`} className="mt-6 inline-flex min-h-11 items-center self-start rounded-full border border-navy px-4 text-sm font-extrabold hover:bg-navy hover:text-white">View {card.name}</A>
                        </article>
                    ))}
                </div>
            </section>

            <section className="bg-white px-6 py-14 lg:px-14 lg:py-16" aria-labelledby="compare-heading">
                <h2 id="compare-heading" className="text-3xl font-extrabold tracking-tight lg:text-4xl">Compare all services</h2>
                <p className="mt-2.5 max-w-[42ch] text-base leading-7 text-[#4A5560]">Content, placement, turnaround and starting price for each link type.</p>
                <p className="mt-4 text-[13px] font-bold text-[#4A5560] lg:hidden">Scroll sideways to compare every column.</p>
                <div className="mt-5 overflow-x-auto rounded-[20px] border border-[#E3E5E8] bg-white shadow-[0_10px_28px_rgba(21,32,41,0.05)]">
                    <table className="w-full min-w-[760px] border-collapse text-left">
                        <thead>
                            <tr>
                                <th className="bg-[#F6F7F9] px-4 py-4" />
                                {cards.map((card) => <th key={card.slug} className="bg-[#F6F7F9] px-4 py-4 text-[15px] font-extrabold">{card.name}</th>)}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.label} className={row.label === 'Price from' ? 'bg-[#FFF1EC] font-extrabold' : ''}>
                                    <th className={`sticky left-0 px-4 py-4 text-sm font-extrabold ${row.label === 'Price from' ? 'bg-[#FFF1EC]' : 'bg-white'}`}>{row.label}</th>
                                    {(row.cells || []).map((cell, index) => <td key={`${row.label}-${index}`} className="border-t border-[#E3E5E8] px-4 py-4 text-[15px] leading-6 text-[#4A5560] first:border-t-0">{cell}</td>)}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>
        </>
    );
}

export function ServicePage({ page, send }) {
    const details = page.data.page || {};
    const buttons = details.buttons || {};
    const tabs = details.package_tabs || [];
    const tiers = details.tiers || [];
    const publishers = page.data.publishers;
    const nicheEdit = details.slug === 'niche-edit';
    const offerHref = nicheEdit ? '#publishers' : '#packages';
    const hero = heroBackgrounds[details.slug];

    return (
        <>
            <Flash flash={page.flash} errors={page.errors} />
            <section className={hero ? 'relative flex min-h-[calc(100dvh-4.5rem)] items-center overflow-hidden border-b border-white/10 bg-[#0c1218] px-6 py-14 text-white lg:px-14' : 'border-b border-[#E3E5E8] bg-white px-6 py-10 lg:px-14 lg:py-16'} aria-labelledby="service-heading">
                {hero ? (
                    <>
                        <img src={hero[0]} alt="" className="pointer-events-none absolute inset-0 h-full w-full object-cover object-[72%_center]" />
                        <div className="pointer-events-none absolute inset-0 bg-gradient-to-r from-[#0c1218] via-[#0c1218]/78 to-[#0c1218]/20" />
                    </>
                ) : null}
                <div className="relative max-w-3xl">
                        <nav className={`text-sm font-bold ${hero ? 'text-white/75' : 'text-[#4A5560]'}`} aria-label="Breadcrumb">
                            <A href="/">Home</A> <span aria-hidden="true"> / </span> <A href="/services">Services</A> <span aria-hidden="true"> / </span> <span className={hero ? 'text-white' : 'text-navy'}>{details.name}</span>
                        </nav>
                        <p className="mt-4 flex flex-wrap gap-2">
                            <span className="inline-flex min-h-9 items-center rounded-full bg-[#FFF1EC] px-3.5 text-[13px] font-extrabold text-brand-dark">{details.pill}</span>
                            <span className={`inline-flex min-h-9 items-center rounded-full border px-3.5 text-[13px] font-extrabold ${hero ? 'border-white/25 bg-white/10 text-white' : 'border-[#E3E5E8] bg-[#F6F7F9]'}`}>{details.turnaround}</span>
                        </p>
                        <h1 id="service-heading" className={`mt-4 text-4xl leading-tight font-extrabold tracking-tight lg:text-5xl ${hero ? 'text-white' : ''}`}>{highlighted(details.h1, details.h1_highlight)}</h1>
                        <p className={`mt-4 max-w-[46ch] text-[17px] leading-7 ${hero ? 'text-white/80' : 'text-[#4A5560]'}`}>{details.intro}</p>
                        <p className="mt-3.5 text-base font-extrabold">{details.tagline}</p>
                        <div className="mt-5 flex flex-wrap gap-2.5">
                            <A href={`/marketplace?service=${details.slug}`} className="inline-flex min-h-[46px] items-center rounded-full bg-brand-dark px-4 text-[15px] font-extrabold text-white hover:bg-brand">{buttons.primary || 'Publisher Websites & Pricing'}</A>
                            <a href={offerHref} className={`inline-flex min-h-[46px] items-center rounded-full border px-4 text-[15px] font-extrabold ${hero ? 'border-white text-white hover:bg-white hover:text-navy' : 'border-navy hover:bg-navy hover:text-white'}`}>{buttons.secondary || 'View Packages'}</a>
                        </div>
                        <ul className="mt-5 flex flex-wrap gap-x-4 gap-y-2">
                            {(details.trust || []).map((item) => (
                                <li key={item} className="flex items-center gap-2 text-sm font-bold">
                                    <span className="grid h-[22px] w-[22px] place-items-center rounded-full bg-[#FFF1EC] text-brand"><Mark name="check" className="h-3.5 w-3.5" /></span>
                                    {item}
                                </li>
                            ))}
                        </ul>
                </div>
            </section>

            <section className="bg-[#F6F7F9] px-6 py-14 lg:px-14 lg:py-20" aria-labelledby="why-service-heading">
                <div className="grid items-end gap-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:gap-16">
                    <div>
                        <p className="text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">Benefits</p>
                        <h2 id="why-service-heading" className="mt-3 text-3xl font-extrabold tracking-tight lg:text-4xl">{details.why_title}</h2>
                    </div>
                    {details.why_intro ? <p className="border-l-2 border-brand pl-5 text-base leading-7 text-[#4A5560] [&_strong]:font-semibold [&_strong]:text-navy" dangerouslySetInnerHTML={{ __html: details.why_intro }} /> : null}
                </div>
                <div className="mt-10 grid items-stretch gap-4 md:grid-cols-2 xl:grid-cols-6">
                    {(details.why || []).map((reason, index, reasons) => {
                        const wide = benefitIsWide(reasons.length, index);
                        const alone = reasons.length % 2 === 1 && index === reasons.length - 1;

                        return (
                            <article key={reason[0]} className={`group relative flex h-full flex-col overflow-hidden rounded-[20px] border border-[#E3E5E8] bg-white p-6 shadow-[0_10px_28px_rgba(21,32,41,0.05)] transition duration-150 motion-safe:hover:-translate-y-0.5 hover:border-[#FFD5C8] hover:shadow-[0_16px_36px_rgba(21,32,41,0.08)] ${benefitSpan(reasons.length, index)} ${wide ? 'xl:flex-row xl:items-start xl:gap-5' : ''} ${alone ? 'md:flex-row md:items-start md:gap-5' : ''}`}>
                                <span className="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-brand transition duration-150 motion-safe:group-hover:scale-x-100" aria-hidden="true" />
                                <span className="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#FFF1EC] text-[15px] font-semibold text-brand-dark">{index + 1}</span>
                                <div>
                                    <h3 className={`text-lg font-semibold ${wide ? 'mt-4 xl:mt-1' : 'mt-4'} ${alone ? 'md:mt-1' : ''}`}>{reason[0]}</h3>
                                    <p className="mt-2 text-[15px] leading-6 text-[#4A5560]">{reason[1]}</p>
                                </div>
                            </article>
                        );
                    })}
                </div>
            </section>

            <section className="bg-white px-6 py-14 lg:px-14 lg:py-20" aria-labelledby="areas-heading">
                <p className="text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">Our key areas</p>
                <h2 id="areas-heading" className="mt-3 text-3xl font-extrabold tracking-tight lg:text-4xl">{details.areas_title}</h2>
                <p className="mt-4 max-w-[46ch] text-[17px] leading-7 text-[#4A5560]">{details.areas_intro}</p>
                {(details.areas_points || []).length > 0 ? (
                    <ul className="mt-4 grid gap-2 sm:grid-cols-2">
                        {details.areas_points.map((point, index) => (
                            <li key={point} className="flex items-center gap-2 text-sm font-bold">
                                <span className={`grid h-[22px] w-[22px] place-items-center rounded-full ${pointTones[index % pointTones.length]}`}><Mark name="check" className="h-3.5 w-3.5" /></span>
                                {point}
                            </li>
                        ))}
                    </ul>
                ) : null}
                <div className="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {(details.areas || []).map((area) => {
                        const tone = areaTones[area.title] || areaTones.Technology;

                        return (
                            <article key={area.title} className={`rounded-[20px] border p-5 shadow-[0_10px_28px_rgba(21,32,41,0.05)] ${tone[0]}`}>
                                <span className={`grid h-11 w-11 place-items-center rounded-[14px] ${tone[1]}`}><Mark name={area.icon} /></span>
                                <h3 className="mt-3.5 text-lg font-extrabold">{area.title}</h3>
                                <p className="mt-2 text-[15px] leading-6 text-[#4A5560]">{area.text}</p>
                            </article>
                        );
                    })}
                </div>
                <div className="mt-5 flex flex-wrap gap-2">
                    {(details.niches || []).map((niche) => (
                        <A key={niche.name} href={`/niches/${slugify(niche.name)}`} className={`inline-flex min-h-10 items-center gap-2 rounded-full border px-3.5 text-sm font-bold hover:border-navy ${nicheChipTones[niche.name] || 'border-[#E3E5E8] bg-white text-navy'}`}>
                            {niche.name}
                            {niche.count != null ? <span className="rounded-full bg-white/80 px-2 py-0.5 text-xs">{niche.count}</span> : null}
                        </A>
                    ))}
                </div>
            </section>

            <section className="bg-navy px-6 py-14 text-white lg:px-14 lg:py-20" aria-labelledby="steps-heading">
                <p className="text-xs font-extrabold tracking-[0.1em] text-[#FF8A55] uppercase">How it works</p>
                <h2 id="steps-heading" className="mt-3 text-3xl font-extrabold tracking-tight text-white lg:text-4xl">{details.steps_title}</h2>
                <ol className="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    {(details.steps || []).map((step, index) => (
                        <li key={step[0]} className="rounded-[20px] border border-white/10 bg-white p-5 text-navy shadow-[0_10px_28px_rgba(0,0,0,0.18)]">
                            <span className="grid h-9 w-9 place-items-center rounded-full bg-[#FFF1EC] text-sm font-extrabold text-brand-dark">{index + 1}</span>
                            <h3 className="mt-3.5 text-lg font-extrabold text-navy">{step[0]}</h3>
                            <p className="mt-2 text-[15px] leading-6 text-[#4A5560]">{step[1]}</p>
                        </li>
                    ))}
                </ol>
                <div className="mt-5">
                    <A href={details.start_href || '/register'} className="inline-flex min-h-[46px] items-center rounded-full bg-brand-dark px-4 text-[15px] font-extrabold text-white hover:bg-brand">{buttons.start || 'Start Your Order'}</A>
                </div>
            </section>

            {nicheEdit ? (
                <PublisherBoard
                    rows={publishers?.rows || []}
                    shell={page.shell}
                    send={send}
                    title="Buy Niche Edits Backlinks"
                    lead="Websites that allow link insertions. Choose a relevant publisher and we place your link in an existing article on that site."
                    demo={publishers?.demo}
                    categories={publishers?.categories || {}}
                    sampleNote={(publishers?.rows || []).some((row) => row.sample_metrics)}
                    embedded
                    sectionId="publishers"
                    priceHeading="Link insertion price"
                />
            ) : (
                <section id="packages" className="scroll-mt-24 bg-white px-6 py-14 lg:px-14 lg:py-20" aria-labelledby="packages-heading">
                    <p className="text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">Packages</p>
                    <h2 id="packages-heading" className="mt-3 text-3xl font-extrabold tracking-tight lg:text-4xl">{details.packages_title}</h2>
                    <p className="mt-4 max-w-[46ch] text-[17px] leading-7 text-[#4A5560]">{details.packages_intro || 'Choose a package that fits your requirements.'}</p>
                    {tabs.length > 0 ? <div className="mt-7"><PackageTabs tabs={tabs} send={send} /></div> : (
                        <div className="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            {tiers.map((tier) => (
                                <article key={tier.name} id={`tier-${slugify(tier.name)}`} className={`flex flex-col rounded-[20px] border p-5 shadow-[0_10px_28px_rgba(21,32,41,0.05)] ${tier.popular ? 'border-brand bg-[#FFF8F6]' : 'border-[#E3E5E8] bg-white'}`}>
                                    {tier.popular ? <p className="mb-2 text-xs font-extrabold tracking-[0.08em] text-brand-dark uppercase">Popular</p> : null}
                                    <span className="grid h-11 w-11 place-items-center rounded-[14px] bg-[#FFF1EC] text-brand"><Mark name={tier.icon || 'layers'} /></span>
                                    <h3 className="mt-3.5 text-lg font-extrabold">{tier.name}</h3>
                                    <p className="mt-2 text-4xl font-extrabold">{tier.price}</p>
                                    <p className="mt-2 text-sm text-[#4A5560]">{tier.summary || (tier.links === 'Custom' ? 'Custom links' : `${tier.links} links`)}</p>
                                    <ul className="mt-4 space-y-2.5">
                                        {(tier.features || []).map((feature) => (
                                            <li key={feature} className="flex gap-2 text-sm">
                                                <span className="mt-0.5 grid h-[22px] w-[22px] shrink-0 place-items-center rounded-full bg-[#FFF1EC] text-brand"><Mark name="check" className="h-3.5 w-3.5" /></span>
                                                <span>{feature}</span>
                                            </li>
                                        ))}
                                    </ul>
                                    <A href={packageHref(details.slug, tier.name)} className="mt-5 inline-flex min-h-[46px] items-center justify-center rounded-full bg-brand-dark px-4 text-[15px] font-extrabold text-white hover:bg-brand">Buy now</A>
                                </article>
                            ))}
                        </div>
                    )}
                    <p className="mt-5 text-[15px] leading-6 text-[#4A5560]"><A href="/publishers" className="font-extrabold text-navy hover:text-brand-dark">Check out the publisher sites and individual pricing</A></p>
                    <p className="mt-2 text-[15px] leading-6 text-[#4A5560]">Need a custom or bulk order? <A href="/contact" className="font-extrabold text-navy hover:text-brand-dark">Contact us</A></p>
                </section>
            )}

            <section className="relative overflow-hidden bg-[#F6F7F9] px-6 py-14 text-center lg:px-14 lg:py-20" aria-labelledby="faq-heading">
                <span aria-hidden="true" className="faq-mark pointer-events-none absolute -left-3 top-2 select-none font-sans text-[190px] leading-none font-extrabold lg:text-[320px]">?</span>
                <span aria-hidden="true" className="faq-mark pointer-events-none absolute right-0 bottom-0 select-none font-sans text-[150px] leading-none font-extrabold lg:text-[250px]">?</span>
                <div className="relative">
                <p className="text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">FAQs</p>
                <h2 id="faq-heading" className="mt-3 text-3xl font-extrabold tracking-tight lg:text-4xl">Questions about {details.name}</h2>
                <p className="mx-auto mt-4 max-w-[46ch] text-[17px] leading-7 text-[#4A5560]">What is included, how long it takes, and how we confirm the link is live.</p>
                <div className="mx-auto mt-7 max-w-[820px] text-left">
                    {(details.faqs || []).map((faq) => (
                        <details key={faq[0]} className="mt-2.5 rounded-2xl border border-[#E3E5E8] bg-white px-4 first:mt-0">
                            <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 text-base font-extrabold [&::-webkit-details-marker]:hidden">
                                {faq[0]}
                                <span className="text-[22px] font-normal text-brand-dark" aria-hidden="true">+</span>
                            </summary>
                            <p className="pb-4 pr-7 text-[15px] leading-7 text-[#4A5560]">{faq[1]}</p>
                        </details>
                    ))}
                </div>
                <div className="mx-auto mt-7 max-w-[820px]">
                    <h3 className="text-2xl font-extrabold">Still have a question?</h3>
                    <p className="mx-auto mt-2 max-w-[46ch] text-[#4A5560]">Tell us the niche, website, or package you want and we will help you choose.</p>
                    <A href="/contact" className="mt-4 inline-flex min-h-[46px] items-center rounded-full border border-navy px-4 text-[15px] font-extrabold hover:bg-navy hover:text-white">Contact</A>
                </div>
                </div>
            </section>

            <section className="border-t-[3px] border-brand bg-navy px-6 py-14 text-white lg:px-14 lg:py-16" aria-labelledby="cta-heading">
                <p className="text-xs font-extrabold tracking-[0.1em] text-[#FF8A55] uppercase">{details.name}</p>
                <h2 id="cta-heading" className="mt-3 max-w-[22ch] text-3xl leading-tight font-extrabold lg:text-4xl">{details.cta_title}</h2>
                <p className="mt-3 max-w-[46ch] text-base leading-7 text-[#D5DCE3]">{details.cta_text}</p>
                <div className="mt-5 flex flex-wrap gap-2.5">
                    <A href={details.start_href || '/register'} className="inline-flex min-h-[46px] items-center rounded-full bg-brand-dark px-4 text-[15px] font-extrabold text-white hover:bg-brand">{buttons.start || 'Start Your Order'}</A>
                    <a href={offerHref} className="inline-flex min-h-[46px] items-center rounded-full border border-white px-4 text-[15px] font-extrabold hover:bg-white hover:text-navy">{buttons.secondary || 'View Packages'}</a>
                    <A href="/contact" className="inline-flex min-h-[46px] items-center rounded-full border border-white px-4 text-[15px] font-extrabold hover:bg-white hover:text-navy">{buttons.contact || 'Talk to Our Team'}</A>
                </div>
            </section>
        </>
    );
}

const packageLooks = {
    starter: 'bg-[#F1F3F5] text-[#3E4C59]',
    professional: 'bg-[#E7F0FA] text-[#1D4E89]',
    pro: 'bg-[#FFF1EC] text-[#D63A10]',
    enterprise: 'bg-[#E5F6F2] text-[#0F766E]',
};

export function PackageTabs({ tabs, send }) {
    const [active, setActive] = useState(tabs[0]?.key);
    const tab = tabs.find((item) => item.key === active) || tabs[0];
    const [quantity, setQuantity] = useState(tab?.min || 1);
    const selected = Math.min(tab.max, Math.max(tab.min, Number(quantity) || tab.min));
    const priced = tierDisplay(tab, selected);
    const [pending, setPending] = useState(false);
    const [errors, setErrors] = useState({});
    const span = tab.max - tab.min;
    const choices = span <= 14 ? Array.from({ length: span + 1 }, (_, index) => tab.min + index) : [];

    function choose(item) {
        setActive(item.key);
        setQuantity(item.min);
        setErrors({});
    }

    function move(next) {
        setQuantity(Math.min(tab.max, Math.max(tab.min, next)));
    }

    async function save(event) {
        event.preventDefault();
        setPending(true);
        setErrors({});
        const result = await send('/cart', 'POST', { package_slug: tab.slug, order_type: 'package', backlinks: selected });
        setPending(false);

        if (result?.errors) {
            setErrors(result.errors);
        }
    }

    return (
        <div>
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" role="tablist" aria-label="Package options">
                {tabs.map((item) => {
                    const on = item.key === tab.key;
                    const shown = on ? priced.final : tierDisplay(item, item.min).final;

                    return (
                        <button key={item.key} type="button" role="tab" aria-selected={on} className={`rounded-[20px] border p-4 text-left transition ${on ? 'border-brand bg-[#FFF8F6] shadow-[0_14px_32px_rgba(255,73,23,0.14)]' : 'border-[#E3E5E8] bg-white hover:border-navy'}`} onClick={() => choose(item)}>
                            <span className="flex items-center justify-between gap-2">
                                <span className="font-sans text-[15px] font-extrabold">{item.label}</span>
                                {item.key === 'pro' ? <span className="rounded-full bg-brand px-2 py-0.5 font-sans text-[11px] font-extrabold text-white">Best value</span> : null}
                            </span>
                            <span className="mt-1 block font-sans text-[13px] font-bold text-[#4A5560]">{item.custom ? `${item.min}+ links` : `${item.min}–${item.max} links`}</span>
                            <span className={`mt-3 inline-flex rounded-full px-2.5 py-1 font-sans text-[12px] font-extrabold ${packageLooks[item.key] || packageLooks.starter}`}>{item.custom ? 'Custom quote' : `${item.discount}% off`}</span>
                            <span className="mt-3 block font-sans text-[22px] leading-none font-extrabold tracking-tight">{on ? money(shown) : `From ${money(shown)}`}</span>
                        </button>
                    );
                })}
            </div>
            <div className="mt-4 grid items-stretch gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)]">
                <div className="rounded-[24px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                    <p className="font-sans text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">Your selection</p>
                    <p className="mt-2 font-sans text-2xl font-extrabold tracking-tight">{tab.label} package</p>
                    <p className="mt-2 max-w-[48ch] font-sans text-[15px] leading-6 text-[#4A5560]">{tab.summary}</p>
                    <div className="mt-5 flex items-center justify-between gap-4">
                        <p className="font-sans text-sm font-extrabold">Backlinks</p>
                        <div className="flex items-center gap-2">
                            <button type="button" className="grid h-11 w-11 place-items-center rounded-full border border-[#E3E5E8] font-sans text-xl font-extrabold hover:border-navy disabled:opacity-40" aria-label="Fewer backlinks" disabled={selected <= tab.min} onClick={() => move(selected - 1)}>−</button>
                            <span className="min-w-12 text-center font-sans text-3xl font-extrabold tabular-nums">{selected}</span>
                            <button type="button" className="grid h-11 w-11 place-items-center rounded-full border border-[#E3E5E8] font-sans text-xl font-extrabold hover:border-navy disabled:opacity-40" aria-label="More backlinks" disabled={selected >= tab.max} onClick={() => move(selected + 1)}>+</button>
                        </div>
                    </div>
                    {choices.length > 0 ? (
                        <div className="mt-4 flex flex-wrap gap-2">
                            {choices.map((count) => (
                                <button key={count} type="button" className={`grid h-11 min-w-11 place-items-center rounded-xl px-2 font-sans text-sm font-extrabold ${count === selected ? 'bg-navy text-white' : 'bg-[#F4F7FA] text-navy hover:bg-[#E7EEF4]'}`} aria-pressed={count === selected} onClick={() => move(count)}>{count}</button>
                            ))}
                        </div>
                    ) : (
                        <label className="mt-4 block font-sans text-sm font-bold text-[#4A5560]">
                            <span className="sr-only">Backlink quantity</span>
                            <input className="w-full accent-brand" type="range" min={tab.min} max={tab.max} value={selected} onChange={(event) => move(Number(event.target.value))} />
                            <span className="mt-2 flex justify-between font-sans text-xs font-bold"><span>{tab.min}</span><span>{tab.max}</span></span>
                        </label>
                    )}
                </div>
                <form className="flex flex-col rounded-[24px] bg-navy p-5 text-white sm:p-6" onSubmit={save}>
                    <p className="font-sans text-xs font-extrabold tracking-[0.1em] text-[#FF8A55] uppercase">Live price</p>
                    <div className="mt-4 space-y-2 font-sans text-sm text-[#D5DCE3]" aria-live="polite">
                        <p className="flex justify-between gap-3"><span>Quantity</span><strong className="text-white">{priced.quantity}</strong></p>
                        {priced.custom ? <p className="flex justify-between gap-3"><span>Pricing</span><strong className="text-white">Custom quotation</strong></p> : (
                            <>
                                <p className="flex justify-between gap-3"><span>Price per backlink</span><strong className="text-white">{money(priced.unit)}</strong></p>
                                <p className="flex justify-between gap-3"><span>Subtotal</span><strong className="text-white">{money(priced.subtotal)}</strong></p>
                                <p className="flex justify-between gap-3 text-[#8DDBA8]"><span>Discount ({tab.discount}%)</span><strong>-{money(priced.discount)}</strong></p>
                            </>
                        )}
                    </div>
                    <p className="mt-5 font-sans text-[13px] font-bold text-[#D5DCE3]">{priced.custom ? 'Starting price' : 'Final price'}</p>
                    <p key={`${tab.key}-${priced.final}`} className="price-live mt-1 font-sans text-[40px] leading-none font-extrabold">{money(priced.final)}</p>
                    {Object.values(errors).flat().slice(0, 1).map((message) => <p key={message} className="mt-3 font-sans text-sm font-bold text-[#FFB199]" role="alert">{message}</p>)}
                    <button className="btn-primary mt-auto w-full" type="submit" disabled={pending}>{pending ? 'Saving…' : 'Continue to Checkout'}</button>
                </form>
            </div>
        </div>
    );
}

function tierDisplay(tab, quantity) {
    const quote = tab.quote || {};

    if (tab.custom) {
        return { custom: true, quantity, final: quote.final };
    }

    const unit = Number(quote.unit || 0);
    const rate = Number(tab.discount || quote.discount_percent || 0) / 100;
    const subtotal = Math.round(unit * quantity * 100) / 100;
    const discount = Math.round(subtotal * rate * 100) / 100;

    return { custom: false, quantity, unit, subtotal, discount, final: Math.round((subtotal - discount) * 100) / 100 };
}
