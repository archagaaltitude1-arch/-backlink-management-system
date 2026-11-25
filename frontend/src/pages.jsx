import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { A, Flash, money } from './chrome';
import { HomePage } from './home';
import { NicheFilters, PublisherBoard, PublisherTable } from './listings';
import { Flag, Sparkline, compact } from './marks';
import { MarketplaceView } from './marketplace-view';
import { PublishView } from './publish-view';
import { PackageTabs, ServicePage, ServicesOverview } from './services-view';
import { ToolView } from './tools-view';
import { accountScreens } from './account';

export function screenFor(view) {
    return screens[view] || MissingScreen;
}

const screens = {
    'customer.pages.home': HomeScreen,
    'customer.pages.contact': ContactScreen,
    'customer.services.index': ServicesScreen,
    'customer.services.show': ServiceScreen,
    'customer.packages.index': PackagesScreen,
    'customer.packages.show': PackageScreen,
    'customer.packages.websites': WebsiteSelectionScreen,
    'customer.publishers.index': PublishersScreen,
    'customer.niches.index': NichesScreen,
    'customer.niches.show': NicheScreen,
    'customer.sites.index': SitesScreen,
    'customer.sites.show': SiteCollectionScreen,
    'customer.sites.marketplace': PublishersScreen,
    'customer.tools.index': ToolsScreen,
    'customer.tools.show': ToolScreen,
    'customer.wishlist.index': WishlistScreen,
    'customer.shopping.index': CartScreen,
    'customer.shopping.checkout': CheckoutScreen,
    'customer.publish.show': PublishScreen,
    'customer.marketplace.show': MarketplaceScreen,
    'auth.login': LoginScreen,
    'auth.register': RegisterScreen,
    'orders.payment': PaymentScreen,
    'orders.thanks': ThanksScreen,
    ...accountScreens,
};

function MissingScreen({ page }) {
    return <section className="mx-auto max-w-3xl px-4 py-16"><h1 className="text-3xl font-extrabold">This page is still opening from the Laravel site</h1><p className="mt-3 text-navy/70">{page.view}</p></section>;
}

function useSend(send) {
    const [pending, setPending] = useState(false);
    const [errors, setErrors] = useState({});

    async function submit(action, method, fields) {
        setPending(true);
        try {
            const result = await send(action, method, fields);
            setErrors(result?.errors || {});

            return result;
        } finally {
            setPending(false);
        }
    }

    return { pending, errors, submit };
}

function HomeScreen({ page }) {
    return <HomePage page={page} />;
}

function ContactScreen() {
    const [body, setBody] = useState({ name: '', email: '', message: '' });

    function sendMail(event) {
        event.preventDefault();
        const subject = encodeURIComponent(`BacklinkPro enquiry from ${body.name || 'a visitor'}`);
        const text = encodeURIComponent(`${body.message}\n\n${body.name}\n${body.email}`);
        window.location.href = `mailto:?subject=${subject}&body=${text}`;
    }

    return (
        <section className="mx-auto grid w-full gap-8 px-4 py-16 lg:grid-cols-2">
            <div>
                <p className="eyebrow text-brand">Contact</p>
                <h1 className="mt-3 text-4xl font-extrabold">Let’s plan your next backlink campaign</h1>
                <p className="mt-4 text-navy/70">Prime One Global (PVT) LTD. #146B, Goodshed Road, Thonikkal, Vavuniya, NP, Sri Lanka.</p>
            </div>
            <form className="panel space-y-4 p-6" onSubmit={sendMail}>
                <input className="field" required placeholder="Name" value={body.name} onChange={(event) => setBody({ ...body, name: event.target.value })} />
                <input className="field" type="email" required placeholder="Email" value={body.email} onChange={(event) => setBody({ ...body, email: event.target.value })} />
                <textarea className="field min-h-32" required placeholder="How can we help?" value={body.message} onChange={(event) => setBody({ ...body, message: event.target.value })} />
                <button className="btn-primary" type="submit">Send message</button>
            </form>
        </section>
    );
}

function ServicesScreen({ page }) {
    return <ServicesOverview page={page} />;
}

function ServiceScreen({ page, send }) {
    return <ServicePage page={page} send={send} />;
}

function PackagesScreen({ page, send }) {
    const selections = page.data.packageSelections || [];
    const [service, setService] = useState(selections[0]?.slug || '');
    const current = selections.find((item) => item.slug === service) || selections[0];

    return (
        <section className="mx-auto w-full space-y-8 px-4 py-12">
            <div id="select">
                <h1 className="text-4xl font-extrabold">Choose a package</h1>
                <p className="mt-3 max-w-2xl text-[15px] leading-7 text-[#4A5560]">Pick a placement, then a campaign size. The price updates as you change the number of backlinks.</p>
                <div className="mt-5 flex flex-wrap gap-2" role="tablist" aria-label="Placement type">
                    {selections.map((item) => {
                        const on = item.slug === current?.slug;

                        return (
                            <button key={item.slug} type="button" role="tab" aria-selected={on} className={`min-h-11 rounded-full px-4 text-sm font-extrabold ${on ? 'bg-[#FFF1EC] text-brand-dark' : 'border border-[#E3E5E8] bg-white text-navy hover:border-navy'}`} onClick={() => setService(item.slug)}>{item.name}</button>
                        );
                    })}
                </div>
                {current?.tabs?.length ? <div className="mt-6"><PackageTabs key={current.slug} tabs={current.tabs} send={send} /></div> : null}
            </div>
        </section>
    );
}

const packageSamples = {
    'display-starter': 'reports/starter-backlink-sample-report.xlsx',
    'display-growth': 'reports/growth-backlink-sample-report.xlsx',
    'display-professional': 'reports/professional-backlink-sample-report.xlsx',
    'display-enterprise': 'reports/enterprise-backlink-sample-report.xlsx',
};

function packageMoney(prefix, amount) {
    if (amount === null) {
        return 'Price on request';
    }

    return `${prefix === '' ? '' : `${prefix} `}${money(amount)}`;
}

function highlightTier(text) {
    const value = String(text || '');
    const match = value.match(/\b(Professional|Enterprise|Starter|Growth|Pro)\b/);

    if (!match || match.index === undefined) {
        return value;
    }

    const start = match.index;

    return <>{value.slice(0, start)}<span className="inline-block rounded bg-black px-[0.28em] py-[0.04em] leading-none text-white">{match[0]}</span>{value.slice(start + match[0].length)}</>;
}

function PackageScreen({ page, search }) {
    const pack = page.data.package || {};
    const story = pack.story || {};
    const navigate = useNavigate();
    const params = new URLSearchParams(search);
    const types = story.order_types?.length
        ? story.order_types
        : [{ value: 'package', label: pack.slug === 'display-enterprise' ? 'Custom campaign (We review your goals and confirm the quotation.)' : 'Backlink campaign (We place the links and send the report.)' }];
    const [orderType, setOrderType] = useState(types[0]?.value || 'package');
    const [quantity, setQuantity] = useState(1);
    const prefix = String(pack.price_prefix || '').trim();
    const unit = Number.isFinite(Number(pack.price)) ? Number(pack.price) : null;
    const showQuantity = prefix === '' && !page.data.selectedBacklinks;
    const qty = showQuantity ? Math.min(10, Math.max(1, Number(quantity) || 1)) : 1;
    const priceLabel = packageMoney(prefix, unit);
    const lineLabel = packageMoney(prefix, unit === null ? null : unit * qty);
    const sample = story.sample || packageSamples[pack.slug];
    const title = pack.label || pack.name;
    const hero = story.hero || `${pack.name || title} Backlink Package`;
    const badge = story.badge || story.count || pack.size;
    const chips = story.chips?.length ? story.chips : [`${story.count || pack.size} backlinks`, story.delivery || 'Delivery confirmed with the order', 'Report included', 'Order tracking'];
    const rules = story.rules?.length ? story.rules : [
        'Our team keeps each placement relevant to the niche in the order.',
        'Anchor text is placed so it reads naturally on the page.',
        'We do not publish adult content, hate speech, or misleading claims.',
        story.delivery_note,
    ].filter(Boolean);
    const note = page.data.selectedBacklinks
        ? `Next, choose ${page.data.selectedBacklinks} websites for this package.`
        : (story.note || 'Next, choose the websites for this package. Anchor text and target pages are added at checkout.');

    function choose(event) {
        event.preventDefault();
        const next = new URLSearchParams();
        next.set('quantity', String(qty));
        next.set('order_type', orderType);
        if (page.data.selectedBacklinks) {
            next.set('backlinks', String(page.data.selectedBacklinks));
        }
        if (params.get('backlinks')) {
            next.set('backlinks', params.get('backlinks'));
        }
        navigate(`/packages/${pack.slug}/websites?${next.toString()}`);
    }

    return (
        <section className="bg-[#F7F8FA] px-5 py-7 text-navy">
            <div className="w-full">
                <Flash flash={page.flash} errors={page.errors} />
                <nav className="mb-4 text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb">
                    <A href="/">Home</A> <span aria-hidden="true">/</span> <A href="/packages">Packages</A> <span aria-hidden="true">/</span> <span>{pack.name || title}</span>
                </nav>
                <div className="grid items-start gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <aside className="relative min-h-[420px] overflow-hidden rounded-2xl border border-[#C6A15B]/45 bg-[linear-gradient(160deg,#152029_0%,#0c0c0e_62%,#2a2114_100%)] px-7 py-9 text-white" aria-label={title}>
                        <div className="absolute top-0 right-0 h-[180px] w-[180px] bg-[#C6A15B]" style={{ clipPath: 'polygon(100% 0, 0 0, 100% 100%)' }} aria-hidden="true" />
                        <div className="absolute top-0 right-0 h-[164px] w-[164px] bg-brand" style={{ clipPath: 'polygon(100% 0, 0 0, 100% 100%)' }}>
                            <span className="absolute top-7 right-3 max-w-24 text-right font-sans text-[22px] leading-tight font-extrabold">{badge}</span>
                        </div>
                        <h2 className="relative z-10 mt-12 max-w-md font-display text-[34px] leading-[1.15] font-extrabold uppercase lg:text-5xl">{highlightTier(hero)}</h2>
                        <ul className="relative z-10 mt-7 max-w-md">
                            {(story.gets || []).map((point) => (
                                <li key={point} className="relative mt-2.5 pl-4 text-base leading-snug before:absolute before:top-[0.55em] before:left-0 before:h-1.5 before:w-1.5 before:rounded-full before:bg-[#C6A15B]">{point}</li>
                            ))}
                        </ul>
                        {story.marks?.length ? (
                            <div className="relative z-10 mt-7 flex max-w-md flex-wrap gap-2">
                                {story.marks.map((mark) => <span key={mark} className="inline-flex min-h-9 items-center rounded-lg border border-[#C6A15B]/55 bg-white/10 px-2.5 text-xs font-extrabold tracking-wide uppercase">{mark}</span>)}
                            </div>
                        ) : null}
                    </aside>
                    <article className="rounded-2xl border border-[#E3E5E8] bg-white p-6">
                        <h1 className="font-display text-3xl leading-tight font-extrabold lg:text-4xl">{highlightTier(title)}</h1>
                        <div className="mt-4 flex flex-wrap items-center gap-3">
                            <p className="font-sans text-[34px] font-extrabold">{priceLabel}</p>
                            {story.category ? <span className="inline-flex min-h-9 items-center rounded-lg border border-[#E3E5E8] px-2.5 text-sm font-bold text-[#4A5560]">{story.category}</span> : null}
                        </div>
                        {story.lead ? <p className="mt-4 text-[15px] leading-relaxed">{story.lead}</p> : <p className="mt-4 text-[15px] leading-relaxed">{pack.summary}</p>}
                        {story.support ? <p className="mt-3 text-[15px] leading-relaxed">{story.support}</p> : null}
                        <p className="mt-4 text-sm font-semibold leading-6 text-[#4A5560]">
                            {chips.map((chip, index) => (
                                <span key={chip}>
                                    {index > 0 ? <span className="px-2 text-[#C5CAD1]" aria-hidden="true">|</span> : null}
                                    {chip}
                                </span>
                            ))}
                        </p>
                        {sample ? <a className="mt-4 inline-flex min-h-11 items-center rounded-lg border border-brand px-4 text-sm font-extrabold text-brand hover:bg-[#FFF1EC] hover:text-brand-dark" href={`/${sample}`} target="_blank" rel="noopener">View Sample Report</a> : null}
                        {story.layout === 'offer' ? null : (
                            <div className="mt-6 space-y-2 text-[15px] leading-relaxed">
                                {story.headline ? <h2 className="font-display text-xl font-extrabold">{story.headline}</h2> : null}
                                <h2 className="pt-3 font-display text-xl font-extrabold">What you get</h2>
                                <ul className="list-disc space-y-1.5 pl-5">{(story.gets || []).map((point) => <li key={point}>{point}</li>)}</ul>
                                <h2 className="pt-3 font-display text-xl font-extrabold">Who is this package for?</h2>
                                <ul className="list-disc space-y-1.5 pl-5">{(story.audience || []).map((point) => <li key={point}>{point}</li>)}</ul>
                                <h2 className="pt-3 font-display text-xl font-extrabold">How it works</h2>
                                <ol className="list-decimal space-y-1.5 pl-5">{(story.steps || []).map((point) => <li key={point}>{point}</li>)}</ol>
                            </div>
                        )}
                        <div className="mt-5 rounded-xl bg-[#EEF5FC] p-4">
                            <h2 className="text-base font-extrabold">Publishing rules</h2>
                            <ul className="mt-2 list-disc space-y-1.5 pl-5 text-[15px]">{rules.map((rule) => <li key={rule}>{rule}</li>)}</ul>
                        </div>
                        <form className="mt-5" onSubmit={choose}>
                            <fieldset>
                                <legend className="font-extrabold">Order type *</legend>
                                <div className="mt-2 space-y-2">
                                    {types.map((type) => (
                                        <label key={type.value} className="flex min-h-11 items-start gap-2.5">
                                            <input className="mt-1" type="radio" name="order_type" value={type.value} checked={orderType === type.value} onChange={() => setOrderType(type.value)} />
                                            <span>{type.label}</span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                            {showQuantity ? (
                                <div className="mt-4 flex items-center gap-2">
                                    <button type="button" className="grid h-11 w-11 place-items-center rounded-xl border border-[#E3E5E8]" aria-label="Decrease quantity" onClick={() => setQuantity((value) => Math.max(1, Number(value) - 1))}>−</button>
                                    <input className="h-11 w-16 rounded-xl border border-[#E3E5E8] text-center" type="number" min="1" max="10" aria-label="Quantity" value={qty} onChange={(event) => setQuantity(event.target.value)} />
                                    <button type="button" className="grid h-11 w-11 place-items-center rounded-xl border border-[#E3E5E8]" aria-label="Increase quantity" onClick={() => setQuantity((value) => Math.min(10, Number(value) + 1))}>+</button>
                                </div>
                            ) : null}
                            <div className="mt-4 space-y-2 border-t border-[#E3E5E8] pt-3 text-sm">
                                <p className="flex justify-between gap-3"><span>{qty}× {title}</span><span>{lineLabel}</span></p>
                                <p className="flex items-center justify-between border-t border-[#E3E5E8] pt-3 font-sans font-extrabold"><span>Subtotal</span><strong className="text-[22px]">{lineLabel}</strong></p>
                            </div>
                            <p className="my-3.5 text-sm leading-relaxed text-[#4A5560]">{note}</p>
                            <button className="inline-flex min-h-[52px] w-full items-center justify-center rounded-lg bg-brand-dark text-base font-extrabold text-white hover:bg-brand" type="submit">Choose Backlinks</button>
                        </form>
                        <p className="mt-3 flex items-center gap-2.5 rounded-xl bg-[#E7F5EC] p-3 text-sm"><b className="rounded-full bg-[#17663A] px-2 py-1 text-[13px] text-white">Estimated delivery</b><span>{story.delivery || 'Confirmed with the order'}{story.delivery_note ? `. ${story.delivery_note}` : ''}</span></p>
                        <p className="mt-3"><strong>Questions?</strong> <A href="/contact" className="font-bold text-brand-dark">Contact us</A></p>
                        {pack.slug === 'display-enterprise' ? <p className="mt-3">Contact our team to discuss your custom backlink campaign.</p> : null}
                    </article>
                </div>
            </div>
        </section>
    );
}

function WebsiteSelectionScreen({ page, send }) {
    const rows = page.data.rows || [];
    const limit = page.data.limit == null ? 0 : Number(page.data.limit);
    const pack = page.data.package || {};
    const [picked, setPicked] = useState([]);
    const { pending, errors, submit } = useSend(send);

    function toggle(domain) {
        setPicked((current) => {
            if (current.includes(domain)) {
                return current.filter((item) => item !== domain);
            }

            if (limit > 0 && current.length >= limit) {
                return current;
            }

            return [...current, domain];
        });
    }

    function save() {
        const fields = {
            package_slug: pack.slug,
            quantity: page.data.quantity || 1,
            order_type: page.data.orderType || 'package',
            websites: picked,
        };

        if (pack.package_id) {
            fields.package_id = pack.package_id;
        }

        if (page.data.backlinks) {
            fields.backlinks = page.data.backlinks;
        }

        if (page.data.orderId) {
            fields.order = page.data.orderId;
            submit(`/packages/${pack.slug}/websites`, 'POST', fields);

            return;
        }

        if (page.data.backlinks) {
            fields.website_selection = true;
        }

        submit('/cart', 'POST', fields);
    }

    return (
        <>
            <Flash flash={page.flash} errors={{ ...page.errors, ...errors }} />
            <PublisherBoard
                rows={rows}
                shell={page.shell}
                send={send}
                title="Select Backlink Websites"
                lead={page.data.lead}
                breadcrumb={page.data.choice || 'Packages'}
                demo={page.data.demo}
                categories={page.data.categories || {}}
                sampleNote={rows.some((row) => row.sample_metrics)}
                selectable
                picked={picked}
                limit={limit}
                onToggle={toggle}
                onSubmit={save}
                pending={pending}
                submitLabel={page.data.orderId ? 'Confirm websites' : 'Continue to Checkout'}
            />
        </>
    );
}

function PublishersScreen({ page, send }) {
    const country = page.data.country || {};
    const heading = country.label
        ? `${country.label} publisher websites`
        : (page.data.defaultType ? `${page.data.defaultType} websites` : (page.data.site?.headline || 'List of posting websites (All publishers)'));
    const lead = country.label
        ? `This chart lists only the publisher websites whose strongest traffic comes from ${country.label}.`
        : (page.data.site?.detail || page.data.lead || 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.');

    return (
        <>
            <Flash flash={page.flash} errors={page.errors} />
            <PublisherBoard
                rows={page.data.rows || []}
                shell={page.shell}
                send={send}
                title={heading}
                lead={lead}
                breadcrumb={country.label || page.data.defaultType || page.data.site?.label || 'Publisher list'}
                demo={page.data.demo}
                categories={page.data.categories || {}}
                defaults={{ dr: page.data.defaultDr || '', type: page.data.defaultType || '', country: country.code || '' }}
                paidLabel={page.data.paidLabel || ''}
                sampleNote={(page.data.rows || []).some((row) => row.sample_metrics)}
            />
        </>
    );
}

function NichesScreen({ page }) {
    return (
        <section className="w-full px-4 py-14 lg:px-6 lg:py-20">
            <h1 className="text-4xl font-extrabold text-navy lg:text-5xl">Niches</h1>
            <div className="mt-8 grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {(page.data.niches || []).map((niche) => (
                    <A key={niche.slug} href={`/niches/${niche.slug}`} className="panel flex h-full min-h-14 items-center justify-between gap-4 px-5 py-4 font-extrabold">
                        <span>{niche.name}</span>
                        <span className="min-w-8 shrink-0 text-right text-sm font-bold tabular-nums text-navy/50">{niche.total}</span>
                    </A>
                ))}
            </div>
        </section>
    );
}

function NicheScreen({ page, search, send }) {
    const websites = page.data.websites?.data || [];
    const cards = new URLSearchParams(search).get('view') === 'cards';
    const name = page.data.name || 'Niche';
    const intro = page.data.custom_intro || [
        `Looking to reach readers in ${name}? Browse our list of ${name} websites that accept guest posts, contextual links, niche edits and text links. Every site is checked for real traffic before it's listed.`,
        'Compare publishers by DR, DA, monthly traffic, spam score and price, then add the ones that fit your campaign to your cart.',
    ];
    const related = page.data.intro_related || [];
    const newest = new URLSearchParams(search);
    newest.set('sort', 'newest');
    newest.delete('page');

    return (
        <div>
            <section className="bg-[#F2F2F2] px-4 py-12 sm:px-6">
                <div className="mx-auto w-full">
                    <nav className="text-sm font-bold" aria-label="Breadcrumb">
                        <A href="/">Home</A> <span aria-hidden="true">/</span> <A href="/niches">Niches</A> <span aria-hidden="true">/</span> <span>{name}</span>
                    </nav>
                    <div className="mt-6 flex flex-wrap items-center gap-4">
                        <h1 className="text-4xl font-extrabold">{name} Guest Posting Sites</h1>
                        <span className="inline-flex min-h-11 items-center rounded-full bg-[#FFF1EC] px-4 text-sm font-bold text-brand-dark">{page.data.total} Publishers</span>
                    </div>
                    <p className="mt-4"><A href={`/niches/${page.data.slug}?${newest.toString()}`} className="font-bold text-brand-dark">See recently added sites →</A></p>
                    <div className="mt-4 max-w-3xl text-[17px] leading-8">
                        <p>{intro[0]}</p>
                        <p className="mt-4">
                            {intro[1]}
                            {!page.data.custom_intro && related.length ? (
                                <> Related niches: {related.map((item, index) => (
                                    <span key={item.slug}>{index > 0 ? ', ' : ''}<A href={`/niches/${item.slug}`} className="font-bold text-brand-dark">{item.name}</A></span>
                                ))}.</>
                            ) : null}
                        </p>
                    </div>
                </div>
            </section>
            <section className="border-b border-[#E3E5E8] bg-white px-4 py-5 sm:px-6">
                <div className="mx-auto w-full">
                    <NicheFilters page={page} search={search} />
                </div>
            </section>
            <section className="bg-[#F7F8F9] px-4 py-6 sm:px-6">
                <div className="mx-auto w-full space-y-4">
                    <p className="font-extrabold">Showing {page.data.websites?.from || 0} – {page.data.websites?.to || 0} of {page.data.websites?.total || page.data.total || 0} publishers</p>
                    {websites.length === 0 ? <p className="panel p-8 text-center font-bold">No {name} websites match these filters.</p> : cards ? (
                        <div className="space-y-3">
                            {websites.map((row) => <NicheCard key={row.domain} row={row} />)}
                        </div>
                    ) : <PublisherTable rows={websites} shell={page.shell} send={send} />}
                    <div className="flex flex-wrap gap-2">
                        {(page.data.websites?.links || []).filter((link) => link.url).map((link) => (
                            <A key={`${link.url}-${link.label}`} href={link.url} className={`inline-flex min-h-11 items-center rounded-xl px-3 text-sm font-bold ${link.active ? 'bg-brand-dark text-white' : 'border border-[#E3E5E8] bg-white'}`}>{String(link.label).replace(/<[^>]+>/g, '')}</A>
                        ))}
                    </div>
                </div>
            </section>
            <section className="bg-white px-4 py-8 sm:px-6">
                <div className="grid items-center gap-6 rounded-3xl bg-[#F2F2F2] px-6 py-8 md:grid-cols-[minmax(0,1fr)_auto] md:px-10">
                    <div>
                        <h2 className="text-2xl font-extrabold">Can't find the website you need?</h2>
                        <p className="mt-2 max-w-xl text-base leading-7 text-[#4A5560]">Tell us the domain or niche you're after and our outreach team will try to add it.</p>
                    </div>
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <A href="/contact" className="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-dark px-5 font-bold text-white hover:bg-brand">Request a Website</A>
                        <A href="/contact" className="inline-flex min-h-11 items-center justify-center rounded-xl border border-navy bg-white px-5 font-bold">Add Your Website as a Publisher</A>
                    </div>
                </div>
            </section>
            {(page.data.related || []).length ? (
                <section className="bg-white px-4 pb-14 sm:px-6" aria-labelledby="related-heading">
                    <h2 id="related-heading" className="text-3xl font-extrabold">Related niches</h2>
                    <div className="mt-5 flex flex-wrap gap-2">
                        {page.data.related.map((item) => <A key={item.slug} href={`/niches/${item.slug}`} className="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] bg-white px-4 text-[15px] font-bold hover:border-navy">{item.name}</A>)}
                    </div>
                </section>
            ) : null}
        </div>
    );
}

function NicheCard({ row }) {
    const publish = `/publish/${encodeURIComponent(row.id && !row.demo ? row.id : row.domain)}`;

    return (
        <article className="rounded-[20px] border border-[#E3E5E8] bg-white p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <A href={row.demo || !row.id ? publish : `/marketplace/${row.id}`} className="text-lg font-extrabold">{row.domain}</A>
                    <ul className="mt-1 flex flex-wrap gap-x-3 text-sm text-[#4A5560]">{(row.niches || []).map((niche) => <li key={niche}>{niche}</li>)}</ul>
                    <p className="mt-1 text-sm text-[#4A5560]">{row.language}</p>
                </div>
                <div className="flex items-center gap-3">
                    <p className="text-lg font-extrabold">{row.price == null ? '' : money(row.price)}</p>
                    <A href={publish} className="inline-flex min-h-11 items-center rounded-xl bg-brand-dark px-4 text-xs font-extrabold tracking-wide text-white uppercase">Publish</A>
                </div>
            </div>
            <div className="mt-3 flex flex-wrap items-start gap-6 border-t border-[#F0F1F3] pt-3">
                <dl className="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-4">
                    {[['AS', row.as], ['DA', row.da], ['DR', row.dr], ['Ahrefs keywords', compact(row.keywords)]].map(([label, value]) => (
                        <div key={label}><dt className="text-xs font-bold text-[#4A5560]">{label}</dt><dd className="font-extrabold">{value ?? ''}</dd></div>
                    ))}
                </dl>
                <div>
                    <p className="text-xs font-bold text-[#4A5560]">Ahrefs traffic</p>
                    <Sparkline seed={`${row.domain}-ahrefs`} color="#E98989" value={row.traffic} label={`Ahrefs traffic trend for ${row.domain}`} />
                </div>
                <div>
                    <p className="mb-1 text-xs font-bold text-[#4A5560]">Countries</p>
                    <div className="flex flex-col gap-1">
                        {(row.top_countries || []).slice(0, 3).map((country) => {
                            const code = String(country[0] || '');

                            return <span key={code} className="inline-flex items-center gap-1.5 text-xs font-bold"><Flag code={code.toUpperCase() === 'UK' ? 'gb' : code.toLowerCase()} />{Number(country[1] || 0).toFixed(1)}%</span>;
                        })}
                    </div>
                </div>
                <div>
                    <p className="text-xs font-bold text-[#4A5560]">Semrush traffic</p>
                    <Sparkline seed={`${row.domain}-semrush`} color="#7DCEA0" value={row.semrush_traffic} label={`Semrush traffic trend for ${row.domain}`} />
                </div>
            </div>
        </article>
    );
}

function SitesScreen({ page }) {
    const sites = page.data.sites || [];

    return (
        <section className="bg-[#F7F8FA]">
            <div className="w-full px-4 py-14 lg:px-6 lg:py-20">
                <nav className="text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb">
                    <A href="/" className="hover:text-brand-dark">Home</A>
                    <span aria-hidden="true"> / </span>
                    <span className="text-navy">Sites</span>
                </nav>
                <p className="eyebrow mt-6 text-brand">Publisher lists</p>
                <h1 className="mt-3 text-4xl font-extrabold text-navy lg:text-5xl">Sites</h1>
                <p className="mt-4 max-w-[68ch] text-[17px] leading-7 text-[#4A5560]">Choose a publisher list. Each one opens the websites that match.</p>
                <div className="mt-10 grid items-stretch gap-4 sm:grid-cols-2">
                    {sites.map((site, index) => {
                        const wide = sites.length % 2 === 1 && index === sites.length - 1;

                        return (
                            <A key={site.slug} href={`/sites/${site.slug}`} className={`group flex h-full rounded-[20px] border border-[#E3E5E8] bg-white p-6 transition hover:border-brand hover:shadow-[0_18px_40px_rgba(21,32,41,0.08)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand ${wide ? 'flex-col justify-between gap-6 sm:col-span-2 sm:flex-row sm:items-center' : 'flex-col'}`}>
                                <div className={wide ? 'max-w-3xl' : 'flex h-full flex-col'}>
                                    <p className="font-sans text-[12px] font-extrabold tracking-[0.14em] text-brand">{String(index + 1).padStart(2, '0')}</p>
                                    <h2 className={`mt-3 text-[18px] leading-snug font-extrabold text-navy group-hover:text-brand-dark ${wide ? '' : 'min-h-[2.8em]'}`}>{site.label}</h2>
                                    <p className={`mt-2 text-[15px] leading-7 text-[#4A5560] ${wide ? '' : 'flex-1'}`}>{site.about}</p>
                                </div>
                                <span className={`inline-flex shrink-0 items-center gap-1.5 text-sm font-extrabold text-brand-dark ${wide ? '' : 'mt-6'}`}>
                                    View websites
                                    <span aria-hidden="true" className="transition-transform group-hover:translate-x-0.5">→</span>
                                </span>
                            </A>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

function SiteCollectionScreen({ page, send }) {
    return (
        <PublisherBoard
            rows={page.data.rows || []}
            shell={page.shell}
            send={send}
            title={page.data.site?.headline || page.data.site?.label || 'Sites'}
            lead={page.data.site?.detail || ''}
            breadcrumb={page.data.site?.label || 'Sites'}
            demo={page.data.demo}
            categories={page.data.categories || {}}
            defaults={{ dr: page.data.defaultDr || '', type: page.data.defaultType || '' }}
            sampleNote={(page.data.rows || []).some((row) => row.sample_metrics)}
        />
    );
}

function ToolsScreen({ page }) {
    return (
        <section className="mx-auto w-full px-4 py-12">
            <h1 className="text-4xl font-extrabold">Tools</h1>
            <div className="mt-6 grid gap-4 md:grid-cols-2">
                {(page.data.tools || []).map((tool) => (
                    <A key={tool.slug} href={`/tools/${tool.slug}`} className="panel p-5">
                        <h2 className="text-lg font-extrabold">{tool.label}</h2>
                        <p className="mt-2 text-sm text-navy/65">{tool.summary}</p>
                    </A>
                ))}
            </div>
        </section>
    );
}

function ToolScreen({ page }) {
    return <ToolView page={page} />;
}

function WishlistScreen({ page, send }) {
    return (
        <section className="mx-auto max-w-3xl space-y-4 px-4 py-12">
            <h1 className="text-4xl font-extrabold">Wishlist</h1>
            <Flash flash={page.flash} errors={page.errors} />
            {(page.data.saved || []).length === 0 ? <p className="panel p-6 text-sm text-navy/60">No saved publishers yet.</p> : (page.data.saved || []).map((item) => (
                <article key={item.domain} className="panel flex items-center justify-between gap-3 p-4">
                    <div><h2 className="font-extrabold">{item.domain}</h2><p className="text-sm text-navy/60">{[item.niche, item.dr ? `DR ${item.dr}` : ''].filter(Boolean).join(' · ')}</p></div>
                    <RemoveButton action="/wishlist" fields={{ domain: item.domain }} send={send} label="Remove" />
                </article>
            ))}
        </section>
    );
}

function CartScreen({ page, send }) {
    const items = page.data.items || [];

    return (
        <section className="mx-auto max-w-4xl space-y-4 px-4 py-12">
            <h1 className="text-4xl font-extrabold">Cart</h1>
            <Flash flash={page.flash} errors={page.errors} />
            {items.length === 0 ? <p className="panel p-8 text-sm text-navy/60">The cart is empty. Add a publisher or a package first.</p> : items.map((item) => (
                <article key={item.line || item.domain} className="panel flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <h2 className="font-extrabold">{item.display_name || item.domain}</h2>
                        <p className="mt-1 text-sm text-navy/60">{item.quantity} × {money(item.price)}{Array.isArray(item.websites) && item.websites.length ? ` · ${item.websites.join(', ')}` : ''}</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {item.kind !== 'package' ? <QuantityForm item={item} send={send} /> : null}
                        <RemoveButton action="/cart" fields={{ line: item.line, domain: item.domain }} send={send} label="Remove" />
                    </div>
                </article>
            ))}
            <aside className="panel space-y-3 p-5">
                <p className="flex justify-between"><span>Subtotal</span><strong>{money(page.data.subtotal)}</strong></p>
                {page.data.packageDiscount > 0 ? <p className="flex justify-between"><span>Package discount</span><strong>-{money(page.data.packageDiscount)}</strong></p> : null}
                {page.data.discount > 0 ? <p className="flex justify-between text-[#17663A]"><span>{page.data.offerLabel || 'Coupon'} {page.data.coupon}</span><strong>-{money(page.data.discount)}</strong></p> : null}
                <p className="flex justify-between text-xl font-extrabold"><span>Total</span><span>{money(page.data.total)}</span></p>
                {page.data.firstOrderOnly ? <p className="text-sm text-navy/70">Your first order includes the required 10% new-customer offer.</p> : <CouponBox coupon={page.data.coupon} send={send} />}
                <div className="flex flex-wrap gap-2">
                    <A href="/cart/checkout" className="btn-primary">Checkout</A>
                    <RemoveButton action="/cart/clear" method="POST" fields={{}} send={send} label="Clear cart" />
                </div>
            </aside>
        </section>
    );
}

function QuantityForm({ item, send }) {
    const { submit, pending } = useSend(send);

    return (
        <form className="flex gap-2" onSubmit={(event) => { event.preventDefault(); submit('/cart', 'PATCH', { line: item.line, quantity: Number(new FormData(event.currentTarget).get('quantity')) }); }}>
            <input className="field w-20" type="number" name="quantity" min="1" max="20" defaultValue={item.quantity} aria-label="Quantity" />
            <button className="btn-outline" type="submit" disabled={pending}>Update</button>
        </form>
    );
}

function CouponBox({ coupon, send }) {
    const { submit, pending, errors } = useSend(send);
    const [code, setCode] = useState('');

    if (coupon) {
        return (
            <div className="rounded-2xl border border-dashed border-[#E3E5E8] bg-[#FFF8F5] p-4">
                <p className="text-sm font-extrabold">Coupon {coupon} is applied.</p>
                <button className="btn-outline mt-3" type="button" disabled={pending} onClick={() => submit('/cart/coupon', 'DELETE', {})}>Remove</button>
            </div>
        );
    }

    return (
        <div className="flex flex-wrap gap-2">
            <input className="field max-w-xs uppercase" name="coupon_code" maxLength={40} placeholder="Coupon code" aria-label="Coupon code" value={code} onChange={(event) => setCode(event.target.value)} />
            <button className="btn-navy" type="button" disabled={pending} onClick={() => submit('/cart/coupon', 'POST', { coupon_code: code })}>Apply</button>
            {Object.values(errors).flat().slice(0, 1).map((message) => <p key={message} className="w-full text-sm font-bold text-brand-dark" role="alert">{message}</p>)}
        </div>
    );
}

function RemoveButton({ action, method = 'DELETE', fields, send, label }) {
    const { submit, pending } = useSend(send);

    return <button className="btn-outline" type="button" disabled={pending} onClick={() => submit(action, method, fields)}>{label}</button>;
}

function newestOpenCoupon(choices, applied) {
    return [...(choices || [])]
        .filter((reward) => reward?.code && reward.code !== applied)
        .sort((a, b) => String(b.created_at || '').localeCompare(String(a.created_at || '')) || Number(b.id || 0) - Number(a.id || 0))[0] || null;
}

function popupDismissed(code) {
    return Boolean(code) && window.sessionStorage.getItem(`checkout-coupon-popup:${code}`) === '1';
}

function CheckoutScreen({ page, send }) {
    const items = page.data.items || [];
    const applied = page.data.coupon;
    const offered = page.data.couponOffer?.code && page.data.couponOffer.code !== applied ? page.data.couponOffer : null;
    const reward = newestOpenCoupon(
        page.data.couponChoices?.length ? page.data.couponChoices : page.data.orderRewards,
        applied,
    ) || offered;
    const [dismissedCode, setDismissedCode] = useState('');
    const showCouponChoice = !page.data.firstOrderOnly && Boolean(reward) && dismissedCode !== reward.code && !popupDismissed(reward?.code) && !page.flash?.saved_coupon_popup;
    const { submit, pending } = useSend(send);
    const couponAction = useSend(send);
    const errors = couponAction.errors;

    function place(event) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const fields = { items: items.map(() => ({})), coupon_code: page.data.coupon || '' };
        data.forEach((value, key) => {
            const match = key.match(/^items\[(\d+)\]\[(.+)\]$/);
            if (match) {
                fields.items[Number(match[1])][match[2]] = value;
            }
        });
        submit('/cart/checkout', 'POST', fields);
    }

    return (
        <section className="bg-canvas">
            {showCouponChoice ? (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-navy/45 p-4" role="dialog" aria-modal="true" aria-labelledby="saved-coupon-select-title">
                    <div className="w-full max-w-md rounded-2xl border border-navy/10 bg-white p-6">
                        <h2 id="saved-coupon-select-title" className="font-display text-2xl font-semibold">Your new coupon</h2>
                        <p className="mt-2 text-sm leading-6 text-navy/70">{reward.discount_summary} on a package or an individual backlink. Apply this coupon before you place the order, or leave it for later.</p>
                        <p className="mt-4 rounded-xl bg-[#f2f2f2] px-4 py-3 text-center font-sans text-2xl font-semibold tracking-wide">{reward.code}</p>
                        {Object.values(errors).flat().slice(0, 1).map((message) => <p key={message} className="mt-3 text-sm text-red-800" role="alert">{message}</p>)}
                        <div className="mt-4 flex gap-2">
                            <button className="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-dark" type="button" disabled={couponAction.pending} onClick={() => couponAction.submit('/cart/coupon', 'POST', { coupon_code: reward.code })}>Apply</button>
                            <button className="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-navy px-4 py-2.5 text-sm font-medium text-white hover:bg-[#1d2b36]" type="button" onClick={() => { window.sessionStorage.setItem(`checkout-coupon-popup:${reward.code}`, '1'); setDismissedCode(reward.code); }}>Not now</button>
                        </div>
                    </div>
                </div>
            ) : null}
            <div className="mx-auto w-full px-4 py-10">
                <h1 className="text-4xl font-extrabold">Checkout</h1>
                <p className="mt-3 max-w-xl text-navy/70">{page.data.firstOrderOnly ? 'Your first order includes the required 10% new-customer offer.' : 'Add your website URL, anchor text and target details for each item. Have a coupon? Enter your code before you place the order.'}</p>
                <div className="mt-4"><Flash flash={page.flash} errors={{ ...page.errors, ...errors }} /></div>
                <form className="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]" onSubmit={place}>
                    <div className="space-y-4">
                        {items.map((item, index) => <CheckoutItem key={item.line || index} item={item} index={index} />)}
                    </div>
                    <aside className="panel h-fit space-y-3 p-5">
                        <h2 className="text-lg font-extrabold">Order summary</h2>
                        <p className="flex justify-between"><span>Items</span><strong>{page.data.quantity}</strong></p>
                        <p className="flex justify-between"><span>Subtotal</span><strong>{money(page.data.subtotal)}</strong></p>
                        {page.data.packageDiscount > 0 ? <p className="flex justify-between"><span>Package discount</span><strong>-{money(page.data.packageDiscount)}</strong></p> : null}
                        {page.data.discount > 0 ? <p className="flex justify-between"><span>{page.data.offerLabel || 'Discount'} {page.data.coupon}</span><strong>-{money(page.data.discount)}</strong></p> : null}
                        <p className="flex justify-between border-t border-[#E3E5E8] pt-3 text-lg font-extrabold"><span>Total</span><span>{money(page.data.total)}</span></p>
                        {page.data.firstOrderOnly ? <p className="text-sm text-navy/70">The 10% first-order offer is included. Other coupons can be used after this order is paid.</p> : <CouponBox coupon={page.data.coupon} send={send} />}
                        <button className="btn-primary w-full uppercase" type="submit" disabled={pending}>{pending ? 'Placing…' : 'Place order'}</button>
                        <A href="/cart" className="inline-flex text-sm font-extrabold text-brand-dark">Back to cart</A>
                    </aside>
                </form>
            </div>
        </section>
    );
}

function CheckoutItem({ item, index }) {
    const prefix = (item.price_prefix || '').trim();
    const total = Number(item.price) * Number(item.quantity || 1);

    return (
        <article className="panel space-y-4 p-5">
            <h2 className="text-lg font-extrabold">{item.kind === 'package' ? item.display_name : `Publish on ${item.display_name || item.domain}`}</h2>
            <p className="text-sm text-navy/65">{item.order_label} · Qty {item.quantity}{Array.isArray(item.websites) && item.websites.length ? ` · ${item.websites.join(', ')}` : ''}</p>
            <p className="font-extrabold text-brand-dark">{prefix ? `${prefix} ` : ''}{money(total)}</p>
            <input type="hidden" name={`items[${index}][line]`} value={item.line} />
            <div className="grid gap-3 md:grid-cols-2">
                <label className="text-sm font-extrabold">Target URL *<input className="field mt-1" type="url" name={`items[${index}][target_url]`} required maxLength={2048} placeholder="https://your-site.com/page" /></label>
                <label className="text-sm font-extrabold">Anchor text *<input className="field mt-1" name={`items[${index}][anchor_text]`} required minLength={15} maxLength={20} placeholder="15–20 characters" /><span className="mt-1 block text-xs font-semibold text-navy/55">Use 15–20 characters.</span></label>
                <label className="text-sm font-extrabold">Article topic *<input className="field mt-1" name={`items[${index}][article_topic]`} required maxLength={255} defaultValue={item.content_topic || ''} /></label>
                <KeywordField name={`items[${index}][keywords]`} />
                <label className="text-sm font-extrabold md:col-span-2">Additional requirements<textarea className="field mt-1 min-h-24" name={`items[${index}][requirements]`} maxLength={5000} /></label>
            </div>
        </article>
    );
}

function KeywordField({ name }) {
    const [value, setValue] = useState('');
    const count = value.split(',').map((item) => item.trim()).filter(Boolean).length;

    return (
        <label className="text-sm font-extrabold md:col-span-2">Keywords
            <input className="field mt-1" name={name} value={value} maxLength={500} placeholder="keyword one, keyword two" onChange={(event) => {
                const next = event.target.value;
                const words = next.split(',').map((item) => item.trim()).filter(Boolean);
                if (words.length > 5 && next.trim().endsWith(',')) {
                    return;
                }
                if (words.length > 5) {
                    return;
                }
                setValue(next);
            }} />
            <span className="mt-1 block text-xs font-semibold text-navy/55">{Math.min(count, 5)} / 5 keywords.</span>
        </label>
    );
}

function PublishScreen({ page, send }) {
    return <PublishView page={page} send={send} />;
}

function MarketplaceScreen({ page, send }) {
    return <MarketplaceView page={page} send={send} />;
}

function LoginScreen({ page, send }) {
    return <AuthForm page={page} send={send} title="Sign in" action="/login" submit="Sign in" extra={<><a href="/login/google" className="btn-outline w-full">Continue with Google</a><label className="flex items-center gap-2 text-sm"><input name="remember" type="checkbox" value="1" /> Remember me</label></>} footer={<A href="/register" className="font-extrabold text-brand-dark">Create an account</A>} />;
}

function RegisterScreen({ page, send }) {
    return <AuthForm page={page} send={send} title="Create your account" action="/register" submit="Create account" fields={['name', 'email', 'password', 'password_confirmation']} footer={<A href="/login" className="font-extrabold text-brand">Sign in</A>} />;
}

function AuthForm({ page, send, title, action, submit, fields = ['email', 'password'], extra, footer }) {
    const { submit: post, pending, errors } = useSend(send);

    return (
        <section className="mx-auto max-w-xl space-y-5 px-4 py-16">
            <h1 className="text-3xl font-extrabold">{title}</h1>
            <Flash flash={page.flash} errors={{ ...page.errors, ...errors }} />
            <form className="panel space-y-4 p-6" onSubmit={(event) => {
                event.preventDefault();
                const data = new FormData(event.currentTarget);
                const payload = {};
                data.forEach((value, key) => { payload[key] = value; });
                post(action, 'POST', payload);
            }}>
                {extra}
                {fields.map((field) => <input key={field} className="field" name={field} type={field.includes('password') ? 'password' : field === 'email' ? 'email' : 'text'} required placeholder={field.replaceAll('_', ' ')} />)}
                <button className="btn-primary w-full" type="submit" disabled={pending}>{submit}</button>
            </form>
            {footer ? <p className="text-center text-sm">{footer}</p> : null}
        </section>
    );
}

function PaymentScreen({ page, send }) {
    const order = page.data.order || {};
    const due = Number(order.total_amount || 0);
    const savings = Number(order.discount_amount || 0);
    const original = Math.round((due + savings) * 100) / 100;
    const applied = page.data.appliedCoupons || [];
    const available = page.data.availableCoupons || [];
    const { submit, pending, errors } = useSend(send);
    const alert = Object.values({ ...page.errors, ...errors }).flat().find(Boolean);

    return (
        <div className="mx-auto max-w-3xl space-y-6">
            <h1 className="text-3xl font-semibold">Complete payment</h1>
            {page.flash?.success ? <p className="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{page.flash.success}</p> : null}
            {alert ? <p className="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{alert}</p> : null}
            <section className="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-navy/10">
                <div className="flex justify-between gap-4">
                    <div>
                        <p className="text-sm text-navy/55">Order</p>
                        <p className="font-semibold">{order.order_number}</p>
                    </div>
                    <div className="text-right">
                        <p className="text-sm text-navy/55">Amount due</p>
                        {savings > 0 ? (
                            <>
                                <p className="text-sm text-navy/40 line-through">{money(original)}</p>
                                <p className="text-2xl font-semibold text-brand-dark">{money(due)}</p>
                                <p className="text-sm font-medium text-emerald-700">You save {money(savings)}</p>
                            </>
                        ) : <p className="text-2xl font-semibold">{money(due)}</p>}
                    </div>
                </div>
                {applied.length ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50/60">
                        <div className="border-b border-emerald-200 px-4 py-3">
                            <h2 className="font-semibold">On this order</h2>
                            <p className="mt-1 text-sm text-navy/60">Each coupon here is included in the amount due.</p>
                        </div>
                        <div className="divide-y divide-emerald-200">
                            {applied.map((coupon) => (
                                <div key={coupon.id || coupon.code} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                    <div className="min-w-0">
                                        <p className="font-semibold">Coupon {coupon.code} is applied</p>
                                        <p className="mt-1 text-sm text-emerald-800">Saves {money(couponSavings(coupon, original))} · {coupon.discount_summary}</p>
                                    </div>
                                    <button className="text-sm font-medium text-navy/70 hover:text-brand" type="button" disabled={pending} onClick={() => submit(`/orders/${order.id}/coupon`, 'DELETE', { coupon_code: coupon.code })}>Remove coupon</button>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : null}
                {available.length ? (
                    <div className="rounded-xl border border-navy/10">
                        <div className="border-b border-navy/10 px-4 py-3">
                            <h2 className="font-semibold">Available coupons</h2>
                            <p className="mt-1 text-sm text-navy/60">Use a coupon to move it onto this order. The amount due changes by that coupon.</p>
                        </div>
                        <div className="divide-y divide-navy/10">
                            {available.map((coupon) => {
                                const extra = couponSavings(coupon, original);
                                const nextDue = Math.max(0, Math.round((original - (savings + extra)) * 100) / 100);

                                return (
                                    <div key={coupon.id || coupon.code} className="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                        <div className="min-w-0">
                                            <p className="font-semibold">{coupon.code}</p>
                                            <p className="mt-1 text-sm text-navy/70">{coupon.discount_summary}{coupon.expires_at ? ` · Use before ${formatDay(coupon.expires_at)}` : ''}</p>
                                            <p className="mt-1 text-sm font-medium text-emerald-700">Amount due becomes {money(nextDue)} · save {money(extra)} more</p>
                                        </div>
                                        <button className="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-dark" type="button" disabled={pending} onClick={() => submit(`/orders/${order.id}/coupon`, 'POST', { coupon_code: coupon.code })}>Use coupon</button>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ) : null}
                {page.payments?.stripe ? <button className="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-dark" type="button" disabled={pending} onClick={() => submit(`/orders/${order.id}/payment`, 'POST', { payment_method: 'stripe' })}>Pay with Stripe</button> : null}
                {page.payments?.demo ? (
                    <form className="space-y-4" onSubmit={(event) => { event.preventDefault(); submit(`/orders/${order.id}/payment`, 'POST', { payment_method: 'demo_card', demo_result: new FormData(event.currentTarget).get('demo_result') }); }}>
                        <p className="text-sm text-navy/65">Dummy payment gateway. No money is charged. Use this until Stripe is connected.</p>
                        <label className="block text-sm font-medium" htmlFor="demo_result">Payment result</label>
                        <select id="demo_result" name="demo_result" className="w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm" defaultValue="success">
                            <option value="success">Successful payment</option>
                            <option value="declined">Declined payment</option>
                        </select>
                        <button className="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-dark" type="submit" disabled={pending}>Pay with dummy gateway</button>
                    </form>
                ) : null}
                {!page.payments?.stripe && !page.payments?.demo ? <p className="text-sm text-navy/65" role="status">Stripe checkout is currently unavailable.</p> : null}
            </section>
        </div>
    );
}

function couponSavings(coupon, subtotal) {
    const value = Number(coupon.discount_value || 0);
    let raw = coupon.discount_type === 'percentage' ? subtotal * (value / 100) : value;

    if (coupon.maximum_discount_amount !== null && coupon.maximum_discount_amount !== undefined && coupon.maximum_discount_amount !== '') {
        raw = Math.min(raw, Number(coupon.maximum_discount_amount));
    }

    return Math.max(0, Math.min(subtotal, Math.round(raw * 100) / 100));
}

function formatDay(value) {
    const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (!match) {
        return '';
    }

    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3])).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function ThanksScreen({ page }) {
    const order = page.data.order || {};
    const paid = order.payment_status === 'paid';
    const invoiceId = order.invoice?.id;

    if (!paid) {
        return (
            <div className="mx-auto max-w-3xl space-y-6 py-6">
                <section className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-navy/10 sm:p-8">
                    <p className="text-xs font-semibold tracking-wider text-brand uppercase">Payment</p>
                    <h1 className="mt-2 text-3xl font-semibold">Waiting for confirmation</h1>
                    <p className="mt-3 text-sm text-navy/70" role="status">Waiting for Stripe to confirm payment for {order.order_number}. Refresh this page in a moment.</p>
                </section>
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-3xl space-y-6 py-6">
            <section className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-navy/10 sm:p-8">
                <p className="text-xs font-semibold tracking-wider text-brand uppercase">Payment complete</p>
                <h1 className="mt-2 text-3xl font-semibold">Thank you</h1>
                <p className="mt-3 text-sm text-navy/70">Your payment for {order.order_number} is confirmed. The order total is {money(order.total_amount)} and it is awaiting approval.</p>
                <div className="mt-6 flex flex-wrap gap-3">
                    {invoiceId ? <a className="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-dark" href={`/customer/invoices/${invoiceId}/download`}>Download invoice</a> : null}
                    <A href="/packages" className="inline-flex min-h-11 items-center justify-center rounded-xl bg-navy px-4 py-2.5 text-sm font-medium text-white hover:bg-[#1d2b36]">Shop more</A>
                </div>
            </section>
            {page.flash?.success ? <p className="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{page.flash.success}</p> : null}
            <section className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-navy/10">
                <h2 className="font-semibold">Shop more to gain more discounts</h2>
                <p className="mt-2 text-sm text-navy/70">{page.data.shopMore}</p>
                <a className="mt-4 inline-flex font-semibold text-brand hover:underline" href="/customer/coupons">View coupons</a>
            </section>
            <a className="text-sm font-semibold text-navy hover:underline" href={`/orders/${order.id}`}>View order</a>
        </div>
    );
}
