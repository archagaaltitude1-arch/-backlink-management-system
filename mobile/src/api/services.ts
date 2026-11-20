import { apiRequest } from './client';
import { parseShell, type Shell } from './home';

export type ServiceOverviewCard = {
    slug: string;
    name: string;
    icon: string;
    menu: string;
    turnaround: string;
    price: string;
};

export type CompareRow = {
    label: string;
    cells: string[];
};

export type ServicesOverview = {
    h1: string;
    intro: string;
    cards: ServiceOverviewCard[];
    compareRows: CompareRow[];
};

export type ServiceReason = [string, string];

export type ServiceArea = {
    icon: string;
    title: string;
    text: string;
};

export type ServiceTier = {
    name: string;
    price: string;
    summary?: string;
    links?: string;
    popular?: boolean;
    icon?: string;
    features: string[];
};

export type PackageQuote = {
    custom?: boolean;
    unit?: number | null;
    final?: number;
    discount_percent?: number;
};

export type PackageTab = {
    key: string;
    label: string;
    min: number;
    max: number;
    discount: number;
    custom: boolean;
    summary: string;
    slug: string;
    quote: PackageQuote;
};

export type ServiceNiche = {
    name: string;
    count: number | null;
};

export type ServiceButtons = {
    primary?: string;
    secondary?: string;
    start?: string;
    contact?: string;
};

export type ServicePublisher = {
    domain: string;
    niches: string[];
    language: string;
    as?: number;
    da?: number;
    dr?: number;
    pa?: number;
    tf?: number;
    spam?: number;
    traffic?: number;
    semrush_traffic?: number;
    keywords?: number;
    backlinks?: number;
    referring_domains?: number;
    top_countries: [string, number][];
    price?: number;
    link_type?: string;
    link_validity?: string;
    sports_allowed?: boolean;
    pharmacy_allowed?: boolean;
    crypto_allowed?: boolean;
    google_news?: boolean;
    sponsored?: boolean;
    foreign_language?: boolean;
    sample_metrics?: boolean;
    added_at?: string | number;
};

export type ServicePage = {
    slug: string;
    name: string;
    pill: string;
    turnaround: string;
    h1: string;
    h1Highlight: string;
    intro: string;
    tagline: string;
    whyTitle: string;
    whyIntro: string;
    why: ServiceReason[];
    areasTitle: string;
    areasIntro: string;
    areasPoints: string[];
    areas: ServiceArea[];
    niches: ServiceNiche[];
    stepsTitle: string;
    steps: ServiceReason[];
    packagesTitle: string;
    packagesIntro: string;
    tiers: ServiceTier[];
    packageTabs: PackageTab[];
    faqs: ServiceReason[];
    ctaTitle: string;
    ctaText: string;
    trust: string[];
    buttons: ServiceButtons;
    startHref: string;
    publishers: ServicePublisher[];
    publisherCategories: string[];
    publishersDemo: boolean;
    sampleNote: boolean;
};

export async function loadServicesOverview(): Promise<{ overview: ServicesOverview; shell: Shell }> {
    const result = await apiRequest('/services', { timeoutMs: 20000 });

    if (result.status >= 400 || result.body.view !== 'customer.services.index') {
        throw new Error('The services page could not be loaded.');
    }

    return { overview: parseOverview(result.body.data), shell: parseShell(result.body.shell) };
}

export async function loadServicePage(slug: string): Promise<{ page: ServicePage; shell: Shell }> {
    const result = await apiRequest(`/services/${encodeURIComponent(slug)}`, { timeoutMs: 30000 });

    if (result.status >= 400 || result.body.view !== 'customer.services.show') {
        throw new Error('This service page could not be loaded.');
    }

    return { page: parseService(slug, result.body.data), shell: parseShell(result.body.shell) };
}

function parseOverview(value: unknown): ServicesOverview {
    const data = record(value);
    const overview = record(data.overview);

    return {
        h1: text(overview.h1, 'Link Building Services for Every Goal and Budget'),
        intro: text(overview.intro),
        cards: Array.isArray(data.cards) ? data.cards.filter(isCard).map(parseCard) : [],
        compareRows: Array.isArray(data.compareRows) ? data.compareRows.filter(isCompare).map(parseCompare) : [],
    };
}

function parseCard(value: Record<string, unknown>): ServiceOverviewCard {
    return {
        slug: text(value.slug),
        name: text(value.name),
        icon: text(value.icon, 'link'),
        menu: text(value.menu),
        turnaround: text(value.turnaround),
        price: text(value.price),
    };
}

function parseCompare(value: Record<string, unknown>): CompareRow {
    return {
        label: text(value.label),
        cells: Array.isArray(value.cells) ? value.cells.map((cell) => text(cell)) : [],
    };
}

function parseService(slug: string, value: unknown): ServicePage {
    const data = record(value);
    const page = record(data.page);
    const publishers = record(data.publishers);
    const rows = Array.isArray(publishers.rows) ? publishers.rows.filter(isPublisher).map(parsePublisher) : [];

    return {
        slug: text(page.slug, slug),
        name: text(page.name),
        pill: text(page.pill),
        turnaround: text(page.turnaround),
        h1: text(page.h1),
        h1Highlight: text(page.h1_highlight),
        intro: text(page.intro),
        tagline: text(page.tagline),
        whyTitle: text(page.why_title),
        whyIntro: text(page.why_intro),
        why: pairs(page.why),
        areasTitle: text(page.areas_title),
        areasIntro: text(page.areas_intro),
        areasPoints: strings(page.areas_points),
        areas: Array.isArray(page.areas) ? page.areas.filter(isArea).map(parseArea) : [],
        niches: Array.isArray(page.niches) ? page.niches.filter(isNiche).map(parseNiche) : [],
        stepsTitle: text(page.steps_title),
        steps: pairs(page.steps),
        packagesTitle: text(page.packages_title),
        packagesIntro: text(page.packages_intro, 'Choose a package that fits your requirements.'),
        tiers: Array.isArray(page.tiers) ? page.tiers.filter(isTier).map(parseTier) : [],
        packageTabs: Array.isArray(page.package_tabs) ? page.package_tabs.filter(isTab).map(parseTab) : [],
        faqs: pairs(page.faqs),
        ctaTitle: text(page.cta_title),
        ctaText: text(page.cta_text),
        trust: strings(page.trust),
        buttons: parseButtons(page.buttons),
        startHref: text(page.start_href),
        publishers: rows,
        publisherCategories: categoryNames(publishers.categories),
        publishersDemo: publishers.demo === true,
        sampleNote: rows.some((row) => row.sample_metrics),
    };
}

function parseArea(value: Record<string, unknown>): ServiceArea {
    return { icon: text(value.icon, 'layers'), title: text(value.title), text: text(value.text) };
}

function parseNiche(value: Record<string, unknown>): ServiceNiche {
    return { name: text(value.name), count: typeof value.count === 'number' ? value.count : null };
}

function parseTier(value: Record<string, unknown>): ServiceTier {
    return {
        name: text(value.name),
        price: text(value.price),
        summary: text(value.summary),
        links: text(value.links),
        popular: value.popular === true,
        icon: text(value.icon, 'layers'),
        features: strings(value.features),
    };
}

function parseTab(value: Record<string, unknown>): PackageTab {
    const quote = record(value.quote);

    return {
        key: text(value.key),
        label: text(value.label),
        min: number(value.min, 1),
        max: number(value.max, 1),
        discount: number(value.discount, number(quote.discount_percent, 0)),
        custom: value.custom === true || quote.custom === true,
        summary: text(value.summary),
        slug: text(value.slug),
        quote: {
            custom: quote.custom === true,
            unit: typeof quote.unit === 'number' ? quote.unit : null,
            final: typeof quote.final === 'number' ? quote.final : undefined,
            discount_percent: typeof quote.discount_percent === 'number' ? quote.discount_percent : undefined,
        },
    };
}

function parsePublisher(value: Record<string, unknown>): ServicePublisher {
    return {
        domain: text(value.domain),
        niches: strings(value.niches),
        language: text(value.language),
        as: optionalNumber(value.as),
        da: optionalNumber(value.da),
        dr: optionalNumber(value.dr),
        pa: optionalNumber(value.pa),
        tf: optionalNumber(value.tf),
        spam: optionalNumber(value.spam),
        traffic: optionalNumber(value.traffic),
        semrush_traffic: optionalNumber(value.semrush_traffic),
        keywords: optionalNumber(value.keywords),
        backlinks: optionalNumber(value.backlinks),
        referring_domains: optionalNumber(value.referring_domains),
        top_countries: countries(value.top_countries),
        price: optionalNumber(value.price),
        link_type: text(value.link_type),
        link_validity: text(value.link_validity),
        sports_allowed: flag(value.sports_allowed),
        pharmacy_allowed: flag(value.pharmacy_allowed),
        crypto_allowed: flag(value.crypto_allowed),
        google_news: flag(value.google_news),
        sponsored: flag(value.sponsored),
        foreign_language: flag(value.foreign_language),
        sample_metrics: value.sample_metrics === true,
        added_at: typeof value.added_at === 'string' || typeof value.added_at === 'number' ? value.added_at : undefined,
    };
}

function parseButtons(value: unknown): ServiceButtons {
    const buttons = record(value);

    return {
        primary: text(buttons.primary, 'Publisher Websites & Pricing'),
        secondary: text(buttons.secondary, 'View Packages'),
        start: text(buttons.start, 'Start Your Order'),
        contact: text(buttons.contact, 'Talk to Our Team'),
    };
}

function pairs(value: unknown): ServiceReason[] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.flatMap((item) => {
        if (!Array.isArray(item) || typeof item[0] !== 'string') {
            return [];
        }

        return [[item[0], typeof item[1] === 'string' ? item[1] : ''] as ServiceReason];
    });
}

function strings(value: unknown): string[] {
    return Array.isArray(value) ? value.filter((item): item is string => typeof item === 'string' && item !== '') : [];
}

function countries(value: unknown): [string, number][] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.flatMap((item) => {
        if (!Array.isArray(item) || typeof item[0] !== 'string') {
            return [];
        }

        return [[item[0], typeof item[1] === 'number' ? item[1] : Number(item[1]) || 0] as [string, number]];
    });
}

function isCard(value: unknown): value is Record<string, unknown> {
    return record(value).slug !== undefined && typeof record(value).name === 'string';
}

function isCompare(value: unknown): value is Record<string, unknown> {
    return typeof record(value).label === 'string';
}

function isArea(value: unknown): value is Record<string, unknown> {
    return typeof record(value).title === 'string';
}

function isNiche(value: unknown): value is Record<string, unknown> {
    return typeof record(value).name === 'string';
}

function isTier(value: unknown): value is Record<string, unknown> {
    return typeof record(value).name === 'string';
}

function isTab(value: unknown): value is Record<string, unknown> {
    return typeof record(value).slug === 'string' && typeof record(value).label === 'string';
}

function isPublisher(value: unknown): value is Record<string, unknown> {
    return typeof record(value).domain === 'string' && record(value).domain !== '';
}

function record(value: unknown): Record<string, unknown> {
    return value && typeof value === 'object' && !Array.isArray(value) ? value as Record<string, unknown> : {};
}

function text(value: unknown, fallback = ''): string {
    return typeof value === 'string' ? value : fallback;
}

function number(value: unknown, fallback: number): number {
    return typeof value === 'number' && Number.isFinite(value) ? value : fallback;
}

function optionalNumber(value: unknown): number | undefined {
    return typeof value === 'number' && Number.isFinite(value) ? value : undefined;
}

function flag(value: unknown): boolean | undefined {
    return typeof value === 'boolean' ? value : undefined;
}

function categoryNames(value: unknown): string[] {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return [];
    }

    return Object.keys(value).filter((name) => name !== '').sort();
}
