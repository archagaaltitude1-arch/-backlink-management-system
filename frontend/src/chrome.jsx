import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Flag } from './marks';

export function pathOf(href) {
    if (typeof href !== 'string' || href === '') {
        return '/';
    }

    try {
        const url = new URL(href, window.location.origin);

        if (url.origin !== window.location.origin) {
            return href;
        }

        return `${url.pathname}${url.search}${url.hash}`;
    } catch {
        return href.startsWith('/') ? href : `/${href}`;
    }
}

export function A({ href, className, children, ...props }) {
    const path = pathOf(href);

    if (!path.startsWith('/') || path.includes('/download')) {
        return <a href={href || '#'} className={className} {...props}>{children}</a>;
    }

    return <Link to={path} className={className} {...props}>{children}</Link>;
}

export function money(amount) {
    const number = Number(amount);

    if (!Number.isFinite(number)) {
        return '—';
    }

    return `$${number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function Flash({ flash, errors }) {
    const messages = [
        ...(flash?.shopping_error ? [flash.shopping_error] : []),
        ...(flash?.wishlist_error ? [flash.wishlist_error] : []),
        ...Object.values(errors || {}).flat(),
    ];
    const ok = flash?.shopping_status || flash?.wishlist_status || flash?.status || flash?.success;

    return (
        <>
            {ok ? <p className="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">{ok}</p> : null}
            {messages.map((message) => <p key={message} className="rounded-xl bg-[#FFF1EC] px-4 py-3 text-sm font-bold text-brand-dark" role="alert">{message}</p>)}
        </>
    );
}

const reviews = [
    ['Amira Shah', 'Northline', 'Contextual links went onto checked publisher sites, and the live URL was in our report the same week.', '/images/reviews/review-amira.jpg'],
    ['Leo Grant', 'Harbor Digital', 'Filtering by niche and DR made the shortlist simple. Every site we ordered was a real publisher.', '/images/reviews/review-leo.jpg'],
    ['Priya Nair', 'Field & Co', 'Guest posts went live inside the delivery window, and the verified report was ready for our client.', '/images/reviews/review-priya.jpg'],
    ['Jonah Ellis', 'Brightpath', 'Niche edits on older articles gave us a faster start than waiting for brand-new posts to index.', '/images/reviews/review-jonah.jpg'],
    ['Sofia Marquez', 'Lumen SEO', 'Traffic, spam score and price are on every listing, so we stopped guessing which sites were worth buying.', '/images/reviews/review-sofia.jpg'],
    ['Kenji Sato', 'Atlas Links', 'The white-label report was clean enough to send to our clients without rebuilding it.', '/images/reviews/review-kenji.jpg'],
];

const markEmoji = {
    pen: '📝',
    edit: '✏️',
    article: '📰',
    type: '🔗',
    anchor: '🔤',
    cpu: '💻',
    code: '⌨️',
    briefcase: '💼',
    coins: '💰',
    health: '🩺',
    heart: '❤️',
    book: '🎓',
    plane: '✈️',
    spark: '👗',
    sun: '☀️',
    home: '🏠',
    bag: '🛒',
    car: '🚗',
    trophy: '🏆',
    utensils: '🍽️',
    sprout: '🌾',
    scale: '⚖️',
    crown: '👑',
    chart: '📈',
    globe: '🌍',
    layout: '🗂️',
    hash: '#️⃣',
    shield: '🛡️',
    link: '🔗',
    gauge: '📊',
    award: '🏅',
    alert: '⚠️',
    users: '👥',
    star: '⭐',
    badge: '✅',
    building: '🏢',
    seed: '🚀',
    layers: '📌',
};

const markTones = {
    brand: 'bg-[#FFF1EC] text-[#D63A10]',
    navy: 'bg-[#EEF1F4] text-[#152029]',
    blue: 'bg-[#E7F0FA] text-[#1D4E89]',
    teal: 'bg-[#E5F6F2] text-[#0F766E]',
    green: 'bg-[#E7F6EA] text-[#1B7A38]',
    amber: 'bg-[#FFF4DE] text-[#B45309]',
    rose: 'bg-[#FDECEF] text-[#BE123C]',
    violet: 'bg-[#F1ECFA] text-[#6D28D9]',
    sky: 'bg-[#E6F4FB] text-[#0369A1]',
    slate: 'bg-[#F1F3F5] text-[#3E4C59]',
};

const serviceMarks = {
    'guest-post': ['pen', 'brand'],
    'niche-edit': ['edit', 'teal'],
    'contextual-link': ['article', 'blue'],
    'text-link': ['type', 'slate'],
};

const nicheMarks = {
    Technology: ['cpu', 'blue'],
    'Software & IT': ['code', 'violet'],
    Business: ['briefcase', 'navy'],
    Finance: ['coins', 'green'],
    Health: ['health', 'rose'],
    Education: ['book', 'amber'],
    Travel: ['plane', 'sky'],
    'Fashion & Beauty': ['spark', 'rose'],
    Lifestyle: ['sun', 'amber'],
    'Real Estate': ['home', 'teal'],
    'E-commerce': ['bag', 'violet'],
    Automotive: ['car', 'slate'],
    Sports: ['trophy', 'green'],
    Food: ['utensils', 'brand'],
    'Agriculture & Farming': ['sprout', 'green'],
    Legal: ['scale', 'navy'],
};

const packageMarks = {
    'Text backlink package': ['type', 'slate'],
    'Contextual backlink package': ['article', 'blue'],
    'Guest post package': ['pen', 'brand'],
};

const tierMarks = {
    Starter: ['seed', 'slate'],
    Pro: ['star', 'brand'],
    Professional: ['badge', 'teal'],
    Enterprise: ['building', 'navy'],
};

const siteMarks = {
    'premium-guest-posting-sites': ['crown', 'amber'],
    'link-insertions-niche-edits': ['edit', 'teal'],
    'guest-posting-organic-traffic': ['chart', 'green'],
    'top-traffic-by-country': ['globe', 'sky'],
    'sidebar-footer-blogroll': ['layout', 'violet'],
    'favorite-websites': ['heart', 'rose'],
    'organic-keywords-guest-posts': ['hash', 'blue'],
};

const toolMarks = {
    'website-authority-checker': ['shield', 'navy'],
    'backlink-checker': ['link', 'brand'],
    'domain-rating-checker': ['gauge', 'amber'],
    'domain-authority-checker': ['award', 'blue'],
    'traffic-checker': ['chart', 'green'],
    'spam-score-checker': ['alert', 'rose'],
    'anchor-text-analyzer': ['anchor', 'violet'],
    'competitor-backlink-analyzer': ['users', 'teal'],
};

function MenuMark({ name, tone = 'navy' }) {
    return (
        <span className={`grid h-8 w-8 shrink-0 place-items-center rounded-lg text-[16px] leading-none ${markTones[tone] || markTones.navy}`} aria-hidden="true">
            {markEmoji[name] || markEmoji.layers}
        </span>
    );
}

function samePath(href, path) {
    return pathOf(href).split('?')[0] === path;
}

function finePointer() {
    return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
}

function MegaButton({ label, current, open, onOpen, onToggle }) {
    return (
        <button type="button" className={`inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium ${open || current ? 'bg-[#FFF1EC] text-brand-dark' : 'text-navy hover:bg-[#F2F2F2]'}`} aria-expanded={open} aria-controls="mega-menu" onMouseEnter={onOpen} onFocus={onOpen} onClick={onToggle}>
            {label}
            <svg className={`h-4 w-4 transition-transform duration-150 ${open ? 'rotate-180' : ''}`} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
        </button>
    );
}

function MegaIntro({ kicker, text, href, cta }) {
    return (
        <A href={href} className="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5 hover:bg-[#FFE8E1]">
            <p className="eyebrow text-brand-dark">{kicker}</p>
            <p className="mt-3 text-[15px] leading-6 text-[#4A5560]">{text}</p>
            <span className="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-brand-dark">{cta}</span>
        </A>
    );
}

function MegaLink({ href, active, icon, tone, title, line }) {
    return (
        <A href={href} aria-current={active ? 'page' : undefined} className={`group flex items-start gap-3 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] ${active ? 'bg-[#FFF7F4]' : ''}`}>
            <MenuMark name={icon} tone={tone} />
            <span className="min-w-0">
                <span className={`block font-sans text-[14px] font-semibold leading-5 ${active ? 'text-brand-dark' : 'text-[#152029] group-hover:text-brand-dark'}`}>{title}</span>
                {line ? <span className="mt-0.5 block font-sans text-[12px] leading-4 text-[#5C6770]">{line}</span> : null}
            </span>
        </A>
    );
}

function MegaPanel({ shell, path, mega }) {
    if (!mega) {
        return null;
    }

    const services = shell?.services || [];
    const niches = shell?.niches || [];
    const sections = (shell?.packages || []).flatMap((group) => group.sections || []);
    const sites = shell?.sites || [];
    const tools = shell?.tools || [];

    return (
        <div id="mega-menu" className="absolute inset-x-0 top-full z-50 border-t-2 border-brand bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)] motion-safe:animate-[mega-in_180ms_ease-out]" role="region" aria-label={`${mega} menu`}>
            <div className="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                {mega === 'services' ? (
                    <>
                        <MegaIntro kicker="Services" text="Four ways to place a backlink." href="/services" cta="Compare all services →" />
                        <div className="grid grid-cols-2 gap-1">
                            {services.map((item) => {
                                const [icon, tone] = serviceMarks[item.slug] || ['link', 'brand'];

                                return <MegaLink key={item.slug} href={item.href} active={samePath(item.href, path)} icon={icon} tone={tone} title={item.name} line={item.line} />;
                            })}
                        </div>
                    </>
                ) : null}
                {mega === 'niches' ? (
                    <>
                        <MegaIntro kicker="Niches" text="Browse publisher websites by topic." href="/niches" cta="View all niches →" />
                        <div className="grid grid-cols-2 gap-1 xl:grid-cols-4">
                            {niches.map((item) => {
                                const [icon, tone] = nicheMarks[item.label] || ['layers', 'navy'];

                                return <MegaLink key={item.href} href={item.href} active={samePath(item.href, path)} icon={icon} tone={tone} title={item.label} />;
                            })}
                        </div>
                    </>
                ) : null}
                {mega === 'packages' ? (
                    <>
                        <MegaIntro kicker="Packages" text="Campaign sizes for each placement type." href="/packages#select" cta="View all packages →" />
                        <div className="grid grid-cols-3 gap-6">
                            {sections.map((section) => {
                                const [icon, tone] = packageMarks[section.title] || ['layers', 'navy'];

                                return (
                                    <div key={section.title}>
                                        <div className="flex items-center gap-2.5 px-3 pb-2">
                                            <MenuMark name={icon} tone={tone} />
                                            <p className="font-sans text-[13px] font-bold leading-4 text-[#152029]">{section.title}</p>
                                        </div>
                                        {(section.items || []).map((item) => {
                                            const href = item.href || `/packages/${item.slug}`;
                                            const [tierIcon, tierTone] = tierMarks[item.label] || ['seed', 'slate'];

                                            return <MegaLink key={item.slug} href={href} active={samePath(href, path)} icon={tierIcon} tone={tierTone} title={item.label} />;
                                        })}
                                    </div>
                                );
                            })}
                        </div>
                    </>
                ) : null}
                {mega === 'sites' ? (
                    <>
                        <MegaIntro kicker="Sites" text="Publisher lists, filtered and ready to order." href="/sites" cta="Browse all sites →" />
                        <div className="grid grid-cols-2 gap-1">
                            {sites.map((item) => {
                                const href = `/sites/${item.slug}`;
                                const [icon, tone] = siteMarks[item.slug] || ['globe', 'navy'];

                                return <MegaLink key={item.slug} href={href} active={samePath(href, path)} icon={icon} tone={tone} title={item.label} />;
                            })}
                        </div>
                    </>
                ) : null}
                {mega === 'tools' ? (
                    <>
                        <MegaIntro kicker="SEO tools" text="Check a publisher before you buy." href="/tools" cta="Open all tools →" />
                        <div className="grid grid-cols-2 gap-1">
                            {tools.map((item) => {
                                const href = `/tools/${item.slug}`;
                                const [icon, tone] = toolMarks[item.slug] || ['search', 'navy'];

                                return <MegaLink key={item.slug} href={href} active={samePath(href, path)} icon={icon} tone={tone} title={item.label} line={item.summary} />;
                            })}
                        </div>
                    </>
                ) : null}
            </div>
        </div>
    );
}

export function PublicChrome({ shell, path, view, flash, send, children }) {
    const headerRef = useRef(null);
    const [mobile, setMobile] = useState(false);
    const [account, setAccount] = useState(false);
    const [mega, setMega] = useState(null);
    const [cartOpen, setCartOpen] = useState(Boolean(flash?.open_cart));
    const auth = shell?.auth;
    const home = path === '/' || path === '/home';

    useEffect(() => {
        if (flash?.open_cart) {
            setCartOpen(true);
        }
    }, [flash?.open_cart]);

    useEffect(() => {
        setMega(null);
    }, [path]);

    useEffect(() => {
        function onKey(event) {
            if (event.key === 'Escape') {
                setMega(null);
            }
        }

        document.addEventListener('keydown', onKey);

        return () => document.removeEventListener('keydown', onKey);
    }, []);

    useEffect(() => {
        if (!mega) {
            return undefined;
        }

        function onPointerDown(event) {
            if (!headerRef.current?.contains(event.target)) {
                setMega(null);
            }
        }

        document.addEventListener('pointerdown', onPointerDown);

        return () => document.removeEventListener('pointerdown', onPointerDown);
    }, [mega]);

    function openMega(id) {
        setAccount(false);
        setMega(id);
    }

    function toggleMega(id) {
        setAccount(false);

        if (finePointer()) {
            setMega(id);

            return;
        }

        setMega((current) => (current === id ? null : id));
    }

    const accountPage = accountViews.has(view);
    const loginPage = view === 'auth.login';
    const settings = shell?.settings || {};
    const websiteName = accountPage ? 'BacklinkPro' : (settings.websiteName || 'BacklinkPro');

    useEffect(() => {
        const root = document.documentElement;

        if (accountPage) {
            root.style.setProperty('--color-brand', '#ff4917');
            root.style.setProperty('--color-brand-dark', '#d63a10');
            root.style.setProperty('--color-navy', '#152029');
            root.style.removeProperty('--site-accent');
            root.style.removeProperty('--site-bg');
            root.style.removeProperty('--site-text');
            document.body.style.backgroundColor = '';
            document.body.style.color = '';
            document.body.style.backgroundImage = '';
            document.body.style.backgroundRepeat = '';
            document.body.style.backgroundSize = '';
            document.body.style.backgroundPosition = '';
            document.body.style.backgroundAttachment = '';

            return undefined;
        }

        const theme = settings.theme;

        if (theme) {
            root.style.setProperty('--color-brand', theme.button);
            root.style.setProperty('--color-brand-dark', theme.secondary);
            root.style.setProperty('--color-navy', theme.primary);
            root.style.setProperty('--site-accent', theme.accent);
            root.style.setProperty('--site-bg', theme.background);
            root.style.setProperty('--site-text', theme.text);
            document.body.style.backgroundColor = theme.background || '';
            document.body.style.color = theme.text || '';
        }

        if (settings.wallpaperUrl) {
            document.body.style.backgroundImage = `url("${settings.wallpaperUrl}")`;
            document.body.style.backgroundRepeat = 'no-repeat';
            document.body.style.backgroundSize = 'cover';
            document.body.style.backgroundPosition = settings.wallpaperCssPosition || 'center center';
            document.body.style.backgroundAttachment = 'fixed';
        } else {
            document.body.style.backgroundImage = '';
        }

        if (settings.faviconUrl) {
            let icon = document.querySelector('link[rel="icon"]');

            if (!icon) {
                icon = document.createElement('link');
                icon.rel = 'icon';
                document.head.appendChild(icon);
            }

            icon.type = 'image/x-icon';
            icon.href = settings.faviconUrl;
        }

        return undefined;
    }, [accountPage, settings]);

    return (
        <div className={`min-h-screen ${accountPage ? 'bg-[#f2f2f2]' : 'bg-white'}`}>
            <header
                ref={headerRef}
                className="sticky top-0 z-50 border-b border-[#E3E5E8] bg-white"
                onMouseLeave={() => setMega(null)}
                onBlur={(event) => {
                    if (!event.currentTarget.contains(event.relatedTarget)) {
                        setMega(null);
                    }
                }}
            >
                <div className="mx-auto flex items-center justify-between gap-3 px-4 py-3.5 lg:px-6">
                    <A href="/home" className="flex items-center gap-2.5 font-sans text-xl font-extrabold text-navy" onMouseEnter={() => setMega(null)}>
                        {accountPage || !settings.logoUrl ? <span className="grid h-9 w-9 place-items-center rounded-lg bg-brand text-sm text-white">↗</span> : <img src={settings.logoUrl} alt="" className="h-10 w-auto max-w-[250px] object-contain" />}
                        {websiteName}
                    </A>
                    <nav className="hidden items-center gap-1 lg:flex" aria-label="Primary">
                        <A href="/" className={linkClass(home)} aria-current={home ? 'page' : undefined} onMouseEnter={() => setMega(null)}>Home</A>
                        <MegaButton label="Services" current={path.startsWith('/services')} open={mega === 'services'} onOpen={() => openMega('services')} onToggle={() => toggleMega('services')} />
                        <MegaButton label="Niches" current={path.startsWith('/niches')} open={mega === 'niches'} onOpen={() => openMega('niches')} onToggle={() => toggleMega('niches')} />
                        <MegaButton label="Packages" current={path.startsWith('/packages')} open={mega === 'packages'} onOpen={() => openMega('packages')} onToggle={() => toggleMega('packages')} />
                        <MegaButton label="Sites" current={path.startsWith('/sites')} open={mega === 'sites'} onOpen={() => openMega('sites')} onToggle={() => toggleMega('sites')} />
                        <MegaButton label="Tools" current={path.startsWith('/tools')} open={mega === 'tools'} onOpen={() => openMega('tools')} onToggle={() => toggleMega('tools')} />
                        <MegaPanel shell={shell} path={path} mega={mega} />
                    </nav>
                    <div className="flex items-center gap-1" onMouseEnter={() => setMega(null)}>
                        <A href="/wishlist" className="relative grid h-11 w-11 place-items-center rounded-xl hover:bg-[#F2F2F2]" aria-label={`Wishlist${shell?.wishlistCount ? `, ${shell.wishlistCount} saved` : ''}`}>♡{shell?.wishlistCount > 0 ? <span className="absolute top-0.5 right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand-dark px-1 text-[11px] font-bold text-white">{shell.wishlistCount}</span> : null}</A>
                        <button type="button" className="relative grid h-11 w-11 place-items-center rounded-xl hover:bg-[#F2F2F2]" aria-label={`Cart, ${shell?.cartCount || 0} items`} onClick={() => setCartOpen(true)}>
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12Z" /><path d="M6 7 5 4H2" /></svg>
                            <span className="absolute top-0.5 right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand-dark px-1 text-[11px] font-bold text-white">{shell?.cartCount || 0}</span>
                        </button>
                        {auth ? (
                            <div className="relative hidden lg:block">
                                <button type="button" className="inline-flex min-h-11 max-w-48 items-center gap-2 rounded-xl px-3 text-[15px] font-bold hover:bg-[#F2F2F2]" aria-expanded={account} onClick={() => setAccount((value) => !value)}><span className="truncate">{auth.name}</span></button>
                                {account ? <div className="absolute right-0 z-50 mt-2 w-52 rounded-xl border border-[#E3E5E8] bg-white p-2 shadow-lg" role="menu"><A href={auth.dashboard} className="block min-h-11 rounded-lg px-3 py-2.5 hover:bg-[#F2F2F2]" role="menuitem">My Dashboard</A>{shell?.logoutHref ? <button type="button" className="block min-h-11 w-full rounded-lg px-3 py-2.5 text-left hover:bg-[#F2F2F2]" role="menuitem" onClick={() => send(shell.logoutHref, 'POST', {})}>Logout</button> : null}</div> : null}
                            </div>
                        ) : (
                            <div className="hidden items-center gap-2 lg:flex">
                                <A href="/login" className="btn-outline">Login</A>
                                <a href={shell?.accountHref || '/login'} className="btn-primary">My Account</a>
                            </div>
                        )}
                        <button type="button" className="grid h-11 w-11 place-items-center rounded-xl lg:hidden" aria-label="Open menu" aria-expanded={mobile} onClick={() => setMobile((value) => !value)}>☰</button>
                    </div>
                </div>
                {mobile ? (
                    <nav className="flex max-h-[70vh] flex-col gap-1 overflow-auto border-t border-[#E3E5E8] px-4 py-4 lg:hidden" aria-label="Mobile">
                        <A href="/" className="rounded-lg px-3 py-3 font-bold">Home</A>
                        <p className="eyebrow text-brand-dark px-3 pt-2">Services</p>
                        {(shell?.services || []).map((item) => {
                            const [icon, tone] = serviceMarks[item.slug] || ['link', 'brand'];

                            return (
                                <A key={item.slug} href={item.href} className="flex items-center gap-3 rounded-lg px-3 py-2">
                                    <MenuMark name={icon} tone={tone} />
                                    <span className="min-w-0"><span className="block font-sans text-[14px] font-semibold">{item.name}</span><span className="text-[12px] leading-4 text-[#5C6770]">{item.line}</span></span>
                                </A>
                            );
                        })}
                        <A href="/services" className="px-3 py-2 font-sans text-[14px] font-bold text-brand-dark">Compare all services →</A>
                        <p className="eyebrow text-brand-dark px-3 pt-2">Niches</p>
                        <div className="grid grid-cols-1 gap-0.5 sm:grid-cols-2">
                            {(shell?.niches || []).map((item) => {
                                const [icon, tone] = nicheMarks[item.label] || ['layers', 'navy'];

                                return (
                                    <A key={item.href} href={item.href} className="flex items-center gap-2.5 rounded-lg px-3 py-1.5">
                                        <MenuMark name={icon} tone={tone} />
                                        <span className="truncate font-sans text-[14px] font-semibold">{item.label}</span>
                                    </A>
                                );
                            })}
                        </div>
                        <p className="eyebrow text-brand-dark px-3 pt-2">Packages</p>
                        {(shell?.packages || []).flatMap((group) => group.sections || []).map((section) => {
                            const [icon, tone] = packageMarks[section.title] || ['layers', 'navy'];

                            return (
                                <div key={section.title}>
                                    <p className="flex items-center gap-2.5 px-3 pt-3 font-sans text-[13px] font-bold"><MenuMark name={icon} tone={tone} />{section.title}</p>
                                    {(section.items || []).map((item) => {
                                        const [tierIcon, tierTone] = tierMarks[item.label] || ['seed', 'slate'];

                                        return (
                                            <A key={item.slug} href={item.href || `/packages/${item.slug}`} className="flex items-center gap-2.5 px-3 py-1.5 font-sans text-[14px] font-semibold">
                                                <MenuMark name={tierIcon} tone={tierTone} />{item.label}
                                            </A>
                                        );
                                    })}
                                </div>
                            );
                        })}
                        <p className="eyebrow text-brand-dark px-3 pt-2">Sites</p>
                        {(shell?.sites || []).map((item) => {
                            const [icon, tone] = siteMarks[item.slug] || ['globe', 'navy'];

                            return (
                                <A key={item.slug} href={`/sites/${item.slug}`} className="flex items-center gap-3 px-3 py-2 font-sans text-[14px] font-semibold leading-5">
                                    <MenuMark name={icon} tone={tone} /><span>{item.label}</span>
                                </A>
                            );
                        })}
                        <p className="eyebrow text-brand-dark px-3 pt-2">SEO tools</p>
                        {(shell?.tools || []).map((item) => {
                            const [icon, tone] = toolMarks[item.slug] || ['search', 'navy'];

                            return (
                                <A key={item.slug} href={`/tools/${item.slug}`} className="flex items-center gap-3 px-3 py-2">
                                    <MenuMark name={icon} tone={tone} />
                                    <span className="min-w-0"><span className="block font-sans text-[14px] font-semibold">{item.label}</span>{item.summary ? <span className="block text-[12px] leading-4 text-[#5C6770]">{item.summary}</span> : null}</span>
                                </A>
                            );
                        })}
                        {auth ? <A href={auth.dashboard} className="btn-navy mt-2">My Dashboard</A> : <A href="/login" className="btn-primary mt-2">Login</A>}
                    </nav>
                ) : null}
            </header>
            {accountPage ? <AccountNav send={send} path={path} view={view} /> : null}
            <main className={accountPage ? 'mx-auto w-full max-w-[1600px] px-4 pb-10 sm:px-6 lg:px-8' : undefined}>{children}</main>
            {view === 'customer.pages.contact' || accountPage || loginPage ? null : <Testimonials />}
            {loginPage ? null : <CountryBrowser countries={shell?.countries || []} />}
            {loginPage ? null : <SiteFooter shell={shell} classic={accountPage} />}
            <CartDrawer open={cartOpen} shell={shell} send={send} onClose={() => setCartOpen(false)} />
            {accountPage && view !== 'orders.payment' ? <SavedCouponDialog popup={flash?.saved_coupon_popup} /> : null}
        </div>
    );
}

function SiteFooter({ shell, classic = false }) {
    if (classic) {
        const link = 'group inline-flex min-h-10 items-center gap-2 text-[15px] text-[#D5DCE3] transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
        const services = [
            ['Guest Post', '/services/guest-post'],
            ['Niche Edit', '/sites/link-insertions-niche-edits'],
            ['Contextual Link', '/services/contextual-link'],
            ['Text Link', '/services/text-link'],
        ];
        const explore = [
            ['Publisher list', '/publishers'],
            ['Niches', '/niches'],
            ['Sites', '/sites'],
            ['Packages', '/packages'],
            ['SEO Tools', '/tools'],
            ['Browse by Country', '/#countries'],
        ];
        const trust = ['White-hat only', 'Every link verified', 'Delivery in 3–14 days'];

        return (
            <footer className="border-t-4 border-brand bg-[#152029] text-white">
                <div className="w-full bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.16),transparent_34%)] px-4 pt-16 pb-12 lg:px-6 lg:pt-20">
                    <div className="grid gap-12 md:grid-cols-2 lg:grid-cols-[minmax(260px,340px)_repeat(3,minmax(0,1fr))] lg:gap-x-14">
                        <div>
                            <A href="/home" className="inline-flex items-center gap-2.5 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                <span className="grid h-9 w-9 place-items-center rounded-lg bg-brand text-sm text-white" aria-hidden="true">↗</span>
                                <span className="font-sans text-xl font-extrabold tracking-tight text-white">BacklinkPro</span>
                            </A>
                            <p className="mt-5 max-w-[32ch] text-[15px] leading-7 text-[#D5DCE3]">Backlinks on real publisher websites, placed and verified by our SEO team.</p>
                            <ul className="mt-6 space-y-2.5">
                                {trust.map((item) => (
                                    <li key={item} className="flex items-center gap-2.5 text-sm text-[#F2F4F6]">
                                        <span className="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white" aria-hidden="true">✓</span>
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <nav aria-label="Services">
                            <h2 className="text-brand uppercase">Services</h2>
                            <ul className="mt-4">
                                {services.map(([label, href]) => <li key={href}><A href={href} className={link}><span className="h-px w-3 bg-brand/70 transition-all group-hover:w-5" aria-hidden="true" />{label}</A></li>)}
                            </ul>
                        </nav>
                        <nav aria-label="Explore">
                            <h2 className="text-brand uppercase">Explore</h2>
                            <ul className="mt-4">
                                {explore.map(([label, href]) => <li key={href}><A href={href} className={link}><span className="h-px w-3 bg-brand/70 transition-all group-hover:w-5" aria-hidden="true" />{label}</A></li>)}
                            </ul>
                        </nav>
                        <div>
                            <h2 className="text-brand uppercase">Contact</h2>
                            <A href="/contact" className="btn-primary mt-5">Contact the team</A>
                            <p className="mt-4 max-w-[28ch] text-sm leading-6 text-[#B8C2CC]">Questions about a campaign, a package, or a publisher.</p>
                            <address className="mt-5 text-sm leading-6 text-[#D5DCE3] not-italic">
                                <span className="block font-semibold text-white">Prime One Global (PVT) LTD.</span>
                                #146B, Goodshed Road<br />
                                Thonikkal, Vavuniya<br />
                                NP, Sri Lanka
                            </address>
                        </div>
                    </div>
                    {(shell?.niches || []).length > 0 ? (
                        <div className="mt-14 border-t border-white/10 pt-10">
                            <div className="flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <h2 className="text-brand uppercase">Categories</h2>
                                <p className="text-sm text-[#B8C2CC]">Publisher websites by niche</p>
                            </div>
                            <ul className="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
                                {(shell.niches || []).map((item) => (
                                    <li key={item.href}><A href={item.href} className={link}>{item.label}</A></li>
                                ))}
                            </ul>
                        </div>
                    ) : null}
                </div>
                <div className="border-t border-white/10 px-4 py-5 lg:px-6">
                    <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-[#B8C2CC]">
                        <p>© {new Date().getFullYear()} BacklinkPro</p>
                        <p>White-hat placements, verified before delivery</p>
                    </div>
                </div>
            </footer>
        );
    }

    const settings = shell?.settings || {};
    const link = 'group inline-flex min-h-10 items-center gap-2 text-[15px] text-[#D5DCE3] transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
    const services = [
        ['Guest Post', '/services/guest-post'],
        ['Niche Edit', '/sites/link-insertions-niche-edits'],
        ['Contextual Link', '/services/contextual-link'],
        ['Text Link', '/services/text-link'],
    ];
    const explore = [
        ['Publisher list', '/publishers'],
        ['Niches', '/niches'],
        ['Sites', '/sites'],
        ['Packages', '/packages'],
        ['SEO Tools', '/tools'],
        ['Browse by Country', '/#countries'],
    ];
    const trust = ['White-hat only', 'Every link verified', 'Delivery in 3–14 days'];
    const websiteName = settings.websiteName || 'BacklinkPro';
    const accent = settings.theme?.accent;

    return (
        <footer className="border-t-4 border-brand bg-[#152029] text-white" style={accent ? { borderColor: accent } : undefined}>
            <div className="w-full bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.16),transparent_34%)] px-4 pt-16 pb-12 lg:px-6 lg:pt-20">
                <div className="grid gap-12 md:grid-cols-2 lg:grid-cols-[minmax(260px,340px)_repeat(3,minmax(0,1fr))] lg:gap-x-14">
                    <div>
                        <A href="/home" className="inline-flex items-center gap-2.5 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            {settings.logoUrl ? <img src={settings.logoUrl} alt="" className="h-10 w-auto max-w-[250px] object-contain" /> : <span className="grid h-9 w-9 place-items-center rounded-lg bg-brand text-sm text-white" aria-hidden="true">↗</span>}
                            <span className="font-sans text-xl font-extrabold tracking-tight text-white">{websiteName}</span>
                        </A>
                        <p className="mt-5 max-w-[32ch] text-[15px] leading-7 text-[#D5DCE3]">{settings.footerDescription || 'Backlinks on real publisher websites, placed and verified by our SEO team.'}</p>
                        <ul className="mt-6 space-y-2.5">
                            {trust.map((item) => (
                                <li key={item} className="flex items-center gap-2.5 text-sm text-[#F2F4F6]">
                                    <span className="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white" aria-hidden="true">✓</span>
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>
                    <nav aria-label="Services">
                        <h2 className="text-brand uppercase">Services</h2>
                        <ul className="mt-4">
                            {services.map(([label, href]) => <li key={href}><A href={href} className={link}><span className="h-px w-3 bg-brand/70 transition-all group-hover:w-5" aria-hidden="true" />{label}</A></li>)}
                        </ul>
                    </nav>
                    <nav aria-label="Explore">
                        <h2 className="text-brand uppercase">Explore</h2>
                        <ul className="mt-4">
                            {explore.map(([label, href]) => <li key={href}><A href={href} className={link}><span className="h-px w-3 bg-brand/70 transition-all group-hover:w-5" aria-hidden="true" />{label}</A></li>)}
                        </ul>
                    </nav>
                    <div>
                        <h2 className="text-brand uppercase">Contact</h2>
                        <A href="/contact" className="btn-primary mt-5">Contact the team</A>
                        <p className="mt-4 max-w-[28ch] text-sm leading-6 text-[#B8C2CC]">Questions about a campaign, a package, or a publisher.</p>
                        <address className="mt-5 text-sm leading-6 text-[#D5DCE3] not-italic">
                            <span className="block font-semibold text-white">{settings.companyName || 'BacklinkPro'}</span>
                            {settings.address || ''}<br />
                            {[settings.city, settings.state, settings.postalCode].filter(Boolean).join(', ')}<br />
                            {settings.country || ''}
                        </address>
                        {settings.companyEmail ? <p className="mt-3 text-sm text-[#D5DCE3]"><span className="block text-[12px] font-extrabold tracking-wide text-[#8B939C] uppercase">Email</span><a href={`mailto:${settings.companyEmail}`}>{settings.companyEmail}</a></p> : null}
                        {settings.phoneNumber ? <p className="mt-3 text-sm text-[#D5DCE3]"><span className="block text-[12px] font-extrabold tracking-wide text-[#8B939C] uppercase">Phone</span>{settings.phoneNumber}</p> : null}
                    </div>
                </div>
                {(shell?.niches || []).length > 0 ? (
                    <div className="mt-14 border-t border-white/10 pt-10">
                        <div className="flex flex-wrap items-baseline gap-x-4 gap-y-1">
                            <h2 className="text-brand uppercase">Categories</h2>
                            <p className="text-sm text-[#B8C2CC]">Publisher websites by niche</p>
                        </div>
                        <ul className="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
                            {(shell.niches || []).map((item) => (
                                <li key={item.href}><A href={item.href} className={link}>{item.label}</A></li>
                            ))}
                        </ul>
                    </div>
                ) : null}
                {settings.footerHtml ? <div className="mt-8 max-w-3xl text-sm leading-7 text-[#D5DCE3]" dangerouslySetInnerHTML={{ __html: settings.footerHtml }} /> : null}
            </div>
            <div className="border-t border-white/10 px-4 py-5 lg:px-6">
                <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-[#B8C2CC]">
                    <p>{settings.footerCopyright || settings.copyright || `© ${new Date().getFullYear()} ${websiteName}`}</p>
                    {(settings.footerLinks || []).length > 0 ? (
                        <ul className="flex flex-wrap gap-x-4 gap-y-2">
                            {settings.footerLinks.map((item) => (
                                <li key={item.url}><A href={item.url} className="hover:text-white" target={item.target === '_blank' ? '_blank' : undefined} rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}>{item.label}</A></li>
                            ))}
                        </ul>
                    ) : <p>White-hat placements, verified before delivery</p>}
                </div>
            </div>
        </footer>
    );
}

const accountViews = new Set([
    'customer.dashboard',
    'customer.profile',
    'customer.wallet',
    'customer.payment-methods',
    'customer.payment-history',
    'customer.invoices',
    'customer.addresses',
    'customer.saved-websites',
    'customer.coupons',
    'customer.notifications',
    'customer.projects',
    'customer.backlinks',
    'customer.reports',
    'customer.websites',
    'customer.services',
    'orders.order-history',
    'orders.order-detail',
    'orders.cart',
    'orders.checkout',
    'orders.payment',
    'orders.thanks',
]);

const accountLinks = [
    ['Dashboard', '/customer/dashboard', 'M3 12a9 9 0 1 1 18 0v6H3z M12 12l4-4'],
    ['Wallet', '/customer/wallet', 'M3 6h16v13H3z M19 10h3v5h-7v-5z'],
    ['Invoices', '/customer/invoices', 'M6 3h9l4 4v14H6z M9 12h7m-7 4h7'],
    ['Service orders', '/orders', 'M3 8h18v13H3z M7 8l2-5h6l2 5m-5 0v5'],
    ['Available coupons', '/customer/coupons', 'M4 7h16v10H4z M8 7v10 M4 12h4'],
    ['Projects', '/customer/projects', 'M3 6h7l2 2h9v12H3z'],
    ['Addresses', '/customer/addresses', 'M3 10l9-7 9 7v11H3z M9 21v-7h6v7'],
    ['Notifications', '/customer/notifications', 'M5 17h14l-2-3V9a5 5 0 0 0-10 0v5z M10 21h4'],
    ['Payment methods', '/customer/payment-methods', 'M3 5h18v14H3z M3 10h18 M6 15h4'],
    ['Account details', '/customer/profile', 'M3 5h18v14H3z M8 10h2m-3 5h4m3-5h4m-4 5h4'],
    ['Security settings', '/customer/profile#security-settings', 'M6 10h12v11H6z M8 10V7a4 4 0 0 1 8 0v3 M12 14v3'],
];

function accountActive(href, path) {
    if (href.includes('#')) {
        return false;
    }

    if (href === '/orders') {
        return path === '/orders' || /^\/orders\/[^/]+$/.test(path) || /^\/orders\/[^/]+\/(payment|thanks)$/.test(path);
    }

    return path === href;
}

function AccountNav({ send, path, view }) {
    const Heading = view === 'customer.dashboard' ? 'h1' : 'p';

    return (
        <div className="mx-auto w-full max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb" className="flex items-center gap-2 text-xs text-slate-600">
                <A className="hover:underline" href="/customer/dashboard">Home</A>
                <span aria-hidden="true">›</span>
                <span>My account</span>
            </nav>
            <Heading className="mt-5 font-display text-3xl text-black">My account</Heading>
            <nav aria-label="My account" className="mt-4 grid grid-cols-2 gap-x-2 gap-y-1 sm:flex sm:flex-wrap sm:justify-center sm:gap-x-5 lg:mt-8">
                {accountLinks.map(([label, href, icon]) => {
                    const active = accountActive(href, path);

                    return (
                        <A key={label} href={href} className={`inline-flex min-h-11 min-w-0 items-center gap-1.5 border-b px-2 py-2 text-sm font-medium text-brand-dark hover:border-brand-dark sm:px-0 ${active ? 'border-navy' : 'border-transparent'}`} aria-current={active ? 'page' : undefined}>
                            <svg className="size-4 shrink-0 text-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d={icon} /></svg>
                            {label}
                        </A>
                    );
                })}
            </nav>
            <form className="mt-2 mb-5 flex justify-end sm:mb-7 sm:justify-center" onSubmit={(event) => { event.preventDefault(); send('/logout', 'POST', {}); }}>
                <button type="submit" className="inline-flex min-h-11 items-center gap-1.5 px-2 py-2 text-sm font-medium text-brand-dark hover:underline">
                    <svg className="size-4 text-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M10 4H4v16h6m4-12 4 4-4 4m4-4H8" /></svg>
                    Logout
                </button>
            </form>
        </div>
    );
}

function SavedCouponDialog({ popup }) {
    const [open, setOpen] = useState(Boolean(popup?.code));

    useEffect(() => {
        setOpen(Boolean(popup?.code));
    }, [popup?.code]);

    if (!open || !popup?.code) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-navy/45 p-4" role="dialog" aria-modal="true" aria-labelledby="saved-coupon-popup-title">
            <div className="w-full max-w-md rounded-2xl border border-navy/10 bg-white p-6">
                <h2 id="saved-coupon-popup-title" className="font-display text-2xl font-semibold">Saved for later</h2>
                <p className="mt-2 text-sm leading-6 text-navy/70">You must select this coupon before you make a payment.</p>
                <p className="mt-4 rounded-xl bg-canvas px-4 py-3 text-center font-sans text-2xl font-semibold tracking-wide">{popup.code}</p>
                <p className="mt-3 text-sm leading-6 text-navy/70">{popup.summary} on a package or an individual backlink. Use it before {popup.deadline}.</p>
                <button className="btn-navy mt-4 w-full" type="button" onClick={() => setOpen(false)}>OK</button>
            </div>
        </div>
    );
}

function linkClass(current) {
    return `inline-flex min-h-11 items-center rounded-lg px-3 text-[15px] font-medium ${current ? 'bg-[#FFF1EC] text-brand-dark' : 'text-navy hover:bg-[#F2F2F2]'}`;
}

function useReviewColumns() {
    const [count, setCount] = useState(4);

    useEffect(() => {
        const wide = window.matchMedia('(min-width: 1200px)');
        const mid = window.matchMedia('(min-width: 700px)');
        const apply = () => setCount(wide.matches ? 4 : mid.matches ? 2 : 1);
        apply();
        wide.addEventListener('change', apply);
        mid.addEventListener('change', apply);

        return () => {
            wide.removeEventListener('change', apply);
            mid.removeEventListener('change', apply);
        };
    }, []);

    return count;
}

function Testimonials() {
    const [index, setIndex] = useState(0);
    const visible = useReviewColumns();
    const max = Math.max(0, reviews.length - visible);
    const start = Math.min(index, max);
    const shown = reviews.slice(start, start + visible);
    const end = start + shown.length;

    return (
        <section className="overflow-hidden bg-[#F6F7F9] px-6 py-16 lg:px-12 lg:py-24" aria-labelledby="reviews-heading">
            <div className="grid items-end gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(16rem,0.7fr)] lg:gap-16">
                <div>
                    <p className="eyebrow text-brand-dark">Testimonials</p>
                    <h2 id="reviews-heading" className="mt-3 max-w-xl text-4xl text-[#152029] lg:text-5xl">What our customers are saying</h2>
                </div>
                <div className="max-w-sm lg:justify-self-end">
                    <p className="text-[15px] leading-7 text-[#4A5560]">Campaign notes from teams who order placements through BacklinkPro.</p>
                    <div className="mt-4 flex items-center gap-3">
                        <p className="text-sm font-bold text-[#4A5560]">{start + 1}–{end} of {reviews.length}</p>
                        <button type="button" className="grid h-11 w-11 place-items-center rounded-full border border-[#E3E5E8] bg-white disabled:opacity-40" aria-label="Previous review" disabled={start <= 0} onClick={() => setIndex((value) => Math.max(0, Math.min(value, max) - 1))}>‹</button>
                        <button type="button" className="grid h-11 w-11 place-items-center rounded-full border border-[#E3E5E8] bg-white disabled:opacity-40" aria-label="Next review" disabled={start >= max} onClick={() => setIndex((value) => Math.min(max, Math.min(value, max) + 1))}>›</button>
                    </div>
                </div>
            </div>
            <div className="mt-10 grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {shown.map(([name, role, quote, photo]) => (
                    <figure key={name} className="flex h-full flex-col overflow-hidden rounded-2xl border border-[#152029] bg-[#152029] shadow-[0_10px_28px_rgba(21,32,41,0.12)]">
                        <img src={photo} alt="" className="h-52 w-full object-cover object-[center_18%]" />
                        <figcaption className="flex flex-1 flex-col px-5 py-5">
                            <blockquote className="font-sans text-[15px] leading-7 text-white">{quote}</blockquote>
                            <div className="mt-auto border-t border-white/15 pt-4">
                                <p className="font-sans text-[15px] font-extrabold text-white">{name}</p>
                                <p className="mt-1 font-sans text-[12px] font-bold tracking-[0.08em] text-[#D5DCE3] uppercase">{role}</p>
                            </div>
                        </figcaption>
                    </figure>
                ))}
            </div>
        </section>
    );
}

function CountryBrowser({ countries }) {
    return (
        <section id="countries" className="bg-white px-5 py-16" aria-labelledby="countries-heading">
            <div className="mx-auto w-full text-center">
                <p className="text-sm font-bold tracking-widest text-brand-dark uppercase">Browse by country</p>
                <h2 id="countries-heading" className="mt-3 text-3xl font-extrabold">Find Publisher Websites by Country</h2>
                <p className="mx-auto mt-3 max-w-2xl text-[#4A5560]">Target a specific market? Pick a country to see publisher websites with traffic from that audience.</p>
                <div className="mt-7 flex flex-wrap justify-center gap-3">
                    {countries.map((country) => (
                        <A key={country.code} href={`/publishers?country=${country.code}#all-publishers`} className="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#E4E7EB] bg-white px-3 text-sm font-semibold hover:border-[#C5CBD1]">
                            <Flag code={country.flag} className="h-[15px] w-[22px]" />
                            {country.label}
                        </A>
                    ))}
                </div>
                <A href="/publishers#all-publishers" className="mt-6 inline-flex min-h-11 items-center font-bold text-brand-dark">View All Websites →</A>
            </div>
        </section>
    );
}

function CartDrawer({ open, shell, send, onClose }) {
    const items = shell?.cartItems || [];
    const count = shell?.cartCount || 0;

    return (
        <div className={`${open ? '' : 'pointer-events-none'} fixed inset-0 z-[80]`} hidden={!open} aria-hidden={!open}>
            <button type="button" className={`absolute inset-0 bg-navy/45 transition-opacity ${open ? 'opacity-100' : 'opacity-0'}`} aria-label="Close cart" onClick={onClose} />
            <aside className={`absolute top-0 right-0 flex h-full w-full max-w-[420px] flex-col bg-white text-navy shadow-2xl transition-transform ${open ? 'translate-x-0' : 'translate-x-full'}`} role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title">
                <div className="flex items-center justify-between border-b border-[#E3E5E8] px-5 py-4">
                    <h2 id="cart-drawer-title" className="text-[22px] font-extrabold">Shopping Cart ({count})</h2>
                    <button type="button" className="grid h-10 w-10 place-items-center rounded-[10px] hover:bg-[#F2F2F2]" aria-label="Close cart" onClick={onClose}>✕</button>
                </div>
                <div className="flex-1 overflow-auto px-5">
                    {items.length === 0 ? <p className="mt-7 text-[15px] leading-6 text-[#4A5560]">Your cart is empty. Add a package or publisher to get started.</p> : (
                        <ul>
                            {items.map((item) => (
                                <li key={item.line} className="grid grid-cols-[72px_minmax(0,1fr)_auto] gap-3 border-b border-[#EDEFF1] py-4">
                                    <div className="relative h-[72px] overflow-hidden rounded-[10px] bg-navy p-2 text-[10px] font-extrabold text-white uppercase">{item.title}{item.badge ? <b className="absolute top-0 right-0 bg-brand px-1.5 py-0.5">{item.badge}</b> : null}</div>
                                    <div>
                                        <h3 className="text-[15px] font-extrabold">{item.title}</h3>
                                        <p className="mt-1 text-[13px] text-[#4A5560]"><span>Order Type:</span> {item.order_label}</p>
                                        {item.websites_label ? <p className="text-[13px] text-[#4A5560]">{item.websites_label}</p> : null}
                                        <strong className="mt-2 block text-base">{item.money}</strong>
                                    </div>
                                    <button type="button" className="grid h-9 w-9 place-items-center rounded-lg text-[#4A5560] hover:bg-[#FFF1EC] hover:text-brand-dark" aria-label={`Remove ${item.title}`} onClick={() => send('/cart', 'DELETE', { line: item.line, domain: item.domain })}>✕</button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
                <div className="border-t border-[#E3E5E8] px-5 py-4">
                    <p className="mb-3 flex justify-between text-[15px] font-extrabold"><span>Subtotal ({count} {count === 1 ? 'item' : 'items'})</span><span>${Number(shell?.cartSubtotal || 0).toFixed(2)}</span></p>
                    <A href="/cart/checkout" className="btn-primary w-full uppercase" onClick={onClose}>Checkout</A>
                    <A href="/cart" className="mt-3 block text-center text-[15px] font-bold underline" onClick={onClose}>View Cart</A>
                </div>
            </aside>
        </div>
    );
}
