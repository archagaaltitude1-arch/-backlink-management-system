export type ServiceCard = {
    slug: string;
    title: string;
    description?: string;
    price?: string | null;
    best_for?: string;
    featured?: boolean;
    options?: { label?: string; href?: string }[];
    icon?: string;
    button?: string;
};

export type NicheTile = {
    name: string;
    slug: string;
};

export type SamplePublisher = {
    domain: string;
    niches?: string[];
    language?: string;
    dr?: number;
    da?: number;
    as?: number;
    traffic?: number;
    semrush_traffic?: number;
    keywords?: number;
    top_countries?: [string, number][];
    price?: number;
    link_type?: string;
    spam?: number;
    demo?: boolean;
};

export type HomeData = {
    publisherCount: number | null;
    nicheCount: number | null;
    servicesPublished: boolean;
    serviceCards: ServiceCard[];
    nicheTiles: NicheTile[];
    samplePublishers: SamplePublisher[];
};

export type ShellService = { name: string; line: string; slug: string };
export type ShellLabel = { label: string; href?: string; slug?: string };
export type CartItem = {
    line: string;
    domain?: string;
    title: string;
    order_label?: string;
    websites_label?: string;
    money?: string;
    badge?: string;
};
export type Shell = {
    services: ShellService[];
    niches: ShellLabel[];
    packages: { sections?: { title: string; items?: ShellLabel[] }[] }[];
    sites: ShellLabel[];
    tools: ShellLabel[];
    countries: { label: string; flag: string; code: string }[];
    cartCount: number;
    cartSubtotal: number;
    cartItems: CartItem[];
    wishlistCount: number;
    wishlistDomains: string[];
};

export function parseHome(body: Record<string, unknown>, status: number): HomeData {
    if (status >= 400) {
        throw new Error(`Homepage request failed with status ${status}.`);
    }

    const data = body.data;
    if (!data || typeof data !== 'object' || Array.isArray(data)) {
        throw new Error('Homepage response did not include a data object.');
    }

    const record = data as Record<string, unknown>;
    if (!Array.isArray(record.serviceCards) || !Array.isArray(record.nicheTiles)) {
        throw new Error('Homepage response did not include services and niches.');
    }

    return {
        publisherCount: typeof record.publisherCount === 'number' ? record.publisherCount : null,
        nicheCount: typeof record.nicheCount === 'number' ? record.nicheCount : null,
        servicesPublished: record.servicesPublished === true,
        serviceCards: record.serviceCards.filter(isServiceCard),
        nicheTiles: record.nicheTiles.filter(isNiche),
        samplePublishers: Array.isArray(record.samplePublishers) ? record.samplePublishers.filter(isPublisher) : [],
    };
}

export function parseShell(value: unknown): Shell {
    const shell = value && typeof value === 'object' && !Array.isArray(value) ? value as Record<string, unknown> : {};

    return {
        services: Array.isArray(shell.services) ? shell.services.filter(isShellService) : [],
        niches: Array.isArray(shell.niches) ? shell.niches.filter(isLabel) : [],
        packages: Array.isArray(shell.packages) ? shell.packages as Shell['packages'] : [],
        sites: Array.isArray(shell.sites) ? shell.sites.filter(isLabel) : [],
        tools: Array.isArray(shell.tools) ? shell.tools.filter(isLabel) : [],
        countries: Array.isArray(shell.countries) ? shell.countries.filter(isCountry) : [],
        cartCount: typeof shell.cartCount === 'number' ? shell.cartCount : 0,
        cartSubtotal: typeof shell.cartSubtotal === 'number' ? shell.cartSubtotal : 0,
        cartItems: Array.isArray(shell.cartItems) ? shell.cartItems.filter(isCartItem) : [],
        wishlistCount: typeof shell.wishlistCount === 'number' ? shell.wishlistCount : 0,
        wishlistDomains: Array.isArray(shell.wishlistDomains) ? shell.wishlistDomains.filter((item): item is string => typeof item === 'string') : [],
    };
}

function isServiceCard(value: unknown): value is ServiceCard {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const card = value as { slug?: unknown; title?: unknown };

    return typeof card.slug === 'string' && typeof card.title === 'string';
}

function isNiche(value: unknown): value is NicheTile {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const niche = value as { name?: unknown; slug?: unknown };

    return typeof niche.name === 'string' && typeof niche.slug === 'string';
}

function isPublisher(value: unknown): value is SamplePublisher {
    return Boolean(value && typeof value === 'object' && !Array.isArray(value) && typeof (value as { domain?: unknown }).domain === 'string');
}

function isShellService(value: unknown): value is ShellService {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const item = value as { name?: unknown; slug?: unknown; line?: unknown };
    if (typeof item.line !== 'string') {
        (item as { line: string }).line = '';
    }

    return typeof item.name === 'string' && typeof item.slug === 'string';
}

function isLabel(value: unknown): value is ShellLabel {
    return Boolean(value && typeof value === 'object' && !Array.isArray(value) && typeof (value as { label?: unknown }).label === 'string');
}

function isCountry(value: unknown): value is Shell['countries'][number] {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const country = value as { label?: unknown; code?: unknown; flag?: unknown };

    return typeof country.label === 'string' && typeof country.code === 'string' && typeof country.flag === 'string';
}

function isCartItem(value: unknown): value is CartItem {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    const item = value as { line?: unknown; title?: unknown };

    return typeof item.line === 'string' && typeof item.title === 'string';
}

export function publisherLabel(count: number | null): string {
    return count == null ? 'Checked' : `${count.toLocaleString()}+`;
}

export function money(amount: unknown): string {
    const number = Number(amount);

    if (!Number.isFinite(number)) {
        return '—';
    }

    return `$${number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function shownPrice(price: number | string | null | undefined): string | null {
    if (typeof price === 'string' && price.trim() !== '') {
        return price;
    }

    if (typeof price === 'number') {
        return money(price);
    }

    return null;
}

export function compactCount(value: number | undefined): string {
    if (typeof value !== 'number') {
        return '—';
    }

    if (value >= 1000000) {
        return `${(value / 1000000).toFixed(1)}M`;
    }

    if (value >= 1000) {
        return `${Math.round(value / 1000)}K`;
    }

    return String(value);
}
