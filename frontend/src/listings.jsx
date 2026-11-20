import { useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { A } from './chrome';
import { Flag, Sparkline } from './marks';

const mins = [20, 30, 40, 50, 60];
const trafficBands = [[1000, '1K+'], [5000, '5K+'], [10000, '10K+'], [50000, '50K+']];
const countBands = [10, 50, 100, 500, 1000];
const spamBands = [[5, 'SS up to 5'], [10, 'SS up to 10'], [20, 'SS up to 20'], [30, 'SS up to 30']];
const yesNo = [['sports', 'Sports/Gaming allowed?'], ['pharmacy', 'Pharmacy allowed?'], ['crypto', 'Crypto allowed?'], ['news', 'Google News'], ['sponsored', 'Marked as sponsored'], ['foreign', 'Foreign lang. allowed?']];

function number(row, key) {
    const value = Number(row?.[key] ?? 0);

    return Number.isFinite(value) ? value : 0;
}

function flagOf(value) {
    if (typeof value !== 'boolean') {
        return '';
    }

    return value ? 'yes' : 'no';
}

function countryCode(row) {
    return String(row?.top_countries?.[0]?.[0] || '').toUpperCase();
}

export function PublisherBoard({
    rows = [],
    shell,
    send,
    title = 'List of posting websites (All publishers)',
    lead = 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.',
    breadcrumb = 'Publisher list',
    demo = false,
    categories = {},
    defaults = {},
    paidLabel = '',
    sampleNote = false,
    selectable = false,
    picked = [],
    limit = 0,
    onToggle,
    onSubmit,
    submitLabel = 'Continue to Checkout',
    pending = false,
    embedded = false,
    sectionId = '',
    priceHeading = 'Price',
}) {
    const [filters, setFilters] = useState({
        as: '', da: '', dr: defaults.dr || '', pa: '', tf: '', niche: defaults.niche || '', price: '',
        traffic: '', semrush: '', language: '', sports: '', pharmacy: '', crypto: '', backlinks: '',
        referring: '', spam: '', type: defaults.type || '', validity: '', news: '', sponsored: '',
        country: defaults.country || '', foreign: '',
    });
    const [query, setQuery] = useState('');
    const [bulk, setBulk] = useState('');
    const [bulkOpen, setBulkOpen] = useState(false);
    const [sort, setSort] = useState('traffic');
    const [pageSize, setPageSize] = useState(25);
    const [page, setPage] = useState(1);
    const [projectOpen, setProjectOpen] = useState(false);
    const [project, setProject] = useState('');
    const [projectNote, setProjectNote] = useState('');
    const languages = useMemo(() => unique(rows.map((row) => row.language)), [rows]);
    const countries = useMemo(() => unique(rows.map(countryCode)), [rows]);
    const categoryNames = Array.isArray(categories) ? categories : Object.keys(categories || {});

    const visible = useMemo(() => {
        const terms = bulk.toLowerCase().split(/[\s,]+/).filter(Boolean);
        const matched = rows.filter((row) => matches(row, filters, query, terms));
        matched.sort((left, right) => sortValue(right, sort) - sortValue(left, sort));

        return matched;
    }, [rows, filters, query, bulk, sort]);

    const pages = Math.max(1, Math.ceil(visible.length / pageSize));
    const current = Math.min(page, pages);
    const start = (current - 1) * pageSize;
    const shown = visible.slice(start, start + pageSize);
    const from = visible.length === 0 ? 0 : start + 1;

    function setFilter(name, value) {
        setFilters((currentFilters) => ({ ...currentFilters, [name]: value }));
        setPage(1);
    }

    return (
        <section id={sectionId || undefined} className={`scroll-mt-24 px-4 py-8 lg:px-6 ${embedded ? 'bg-[#F7F8FA]' : 'bg-canvas'}`} aria-labelledby={embedded ? 'publishers-heading' : undefined}>
            {paidLabel ? <p className="mb-4 rounded-xl bg-[#FFF1EC] px-4 py-3 text-sm font-bold text-brand-dark" role="status">Payment confirmed. These websites match your {paidLabel}.</p> : null}
            {embedded ? (
                <div className="mx-auto max-w-3xl text-center">
                    <p className="text-xs font-extrabold tracking-[0.1em] text-brand-dark uppercase">Websites</p>
                    <h2 id="publishers-heading" className="mt-3 text-3xl font-extrabold lg:text-4xl">{title}</h2>
                    {lead ? <p className="mx-auto mt-4 text-[17px] leading-7 text-[#4A5560]">{lead}</p> : null}
                </div>
            ) : (
                <>
                    <nav className="text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb"><A href="/">Home</A> <span aria-hidden="true"> / </span> <span>{breadcrumb}</span></nav>
                    <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <h1 className="max-w-3xl text-3xl font-extrabold lg:text-4xl">{title}</h1>
                        <button type="button" className="btn-outline border-brand text-brand" onClick={() => { setSort('added'); setPage(1); document.getElementById('all-publishers')?.scrollIntoView({ behavior: 'smooth' }); }}>See recently added sites</button>
                    </div>
                    <p className="mt-3 max-w-3xl text-[#4A5560]">{lead}</p>
                </>
            )}
            {demo ? <p className="mt-3 text-sm font-bold text-brand-dark">Sample data for preview. These figures belong to fictional .example publishers.</p> : null}
            {sampleNote && !selectable ? <p className="mt-3 text-sm font-bold text-brand-dark">Language, country, keywords, Semrush, referring domains and link details are sample figures until they are stored on each website.</p> : null}
            {selectable ? <p className="mt-3 max-w-3xl text-sm font-bold text-[#17663A]">Backlink charts use the DataForSEO counts after a publisher is refreshed. Until then, the chart uses the stored sample count. AS, DA, DR, Ahrefs, Semrush and country bars stay on every row.</p> : null}

            {selectable ? (
                <div className="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#E3E5E8] bg-white p-4">
                    <p className="font-extrabold" id="selection-count">{limit > 0 ? `${picked.length}/${limit}` : `${picked.length} selected`}</p>
                    <button type="button" className="btn-navy" disabled={pending || (limit > 0 ? picked.length !== limit : picked.length < 1)} onClick={onSubmit}>{pending ? 'Saving…' : submitLabel}</button>
                </div>
            ) : embedded ? null : (
                <>
                    <div className="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#E3E5E8] bg-white p-4">
                        <div>
                            <h2 className="text-base font-extrabold">Create project</h2>
                            <p className="text-sm text-[#4A5560]">Create your first project to organize the websites you choose.</p>
                        </div>
                        <button type="button" className="btn-primary" aria-expanded={projectOpen} onClick={() => setProjectOpen((open) => !open)}>Create project</button>
                    </div>
                    {projectOpen ? (
                        <form className="mt-3 flex flex-wrap gap-2 rounded-2xl border border-[#E3E5E8] bg-white p-4" onSubmit={(event) => { event.preventDefault(); setProjectNote(`${project.trim()} is ready on this page. Choose publishers from the list below.`); }}>
                            <input className="field max-w-sm" required maxLength={80} placeholder="Project name" value={project} onChange={(event) => setProject(event.target.value)} aria-label="Project name" />
                            <button className="btn-primary" type="submit">Save project</button>
                            {projectNote ? <p className="w-full text-sm font-bold text-brand-dark" role="status">{projectNote}</p> : null}
                        </form>
                    ) : null}
                </>
            )}

            <div className="mt-4 flex flex-wrap gap-2" aria-label="Website specifications">
                {[['as', 'AS'], ['da', 'DA'], ['dr', 'DR'], ['pa', 'PA'], ['tf', 'TF']].map(([name, label]) => (
                    <Select key={name} label={`Minimum ${label}`} value={filters[name]} onChange={(value) => setFilter(name, value)}>
                        <option value="">{label}</option>
                        {(name === 'dr' ? [...mins, 70] : mins).map((value) => <option key={value} value={value}>{label} {value}+</option>)}
                    </Select>
                ))}
                <Select label="Search by niche" value={filters.niche} onChange={(value) => setFilter('niche', value)}>
                    <option value="">Niche</option>
                    {categoryNames.map((name) => <option key={name} value={name}>{name}</option>)}
                </Select>
                <Select label="Price range" value={filters.price} onChange={(value) => setFilter('price', value)}>
                    <option value="">Price range</option>
                    {[100, 250, 500, 1000].map((value) => <option key={value} value={value}>Up to ${value.toLocaleString()}</option>)}
                </Select>
                <Select label="Minimum Ahrefs traffic" value={filters.traffic} onChange={(value) => setFilter('traffic', value)}>
                    <option value="">Ahrefs traffic</option>
                    {trafficBands.map(([value, label]) => <option key={value} value={value}>Ahrefs {label}</option>)}
                </Select>
                <Select label="Minimum Semrush traffic" value={filters.semrush} onChange={(value) => setFilter('semrush', value)}>
                    <option value="">Semrush traffic</option>
                    {trafficBands.map(([value, label]) => <option key={value} value={value}>Semrush {label}</option>)}
                </Select>
                <Select label="Language" value={filters.language} onChange={(value) => setFilter('language', value)}>
                    <option value="">Language</option>
                    {languages.map((language) => <option key={language}>{language}</option>)}
                </Select>
                {yesNo.map(([name, label]) => (
                    <Select key={name} label={label} value={filters[name]} onChange={(value) => setFilter(name, value)}>
                        <option value="">{label}</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </Select>
                ))}
                <Select label="Number of backlinks" value={filters.backlinks} onChange={(value) => setFilter('backlinks', value)}>
                    <option value="">No. of backlinks</option>
                    {countBands.map((value) => <option key={value} value={value}>{value.toLocaleString()}+</option>)}
                </Select>
                <Select label="Number of referring domains" value={filters.referring} onChange={(value) => setFilter('referring', value)}>
                    <option value="">No. of referring domains</option>
                    {countBands.map((value) => <option key={value} value={value}>{value.toLocaleString()}+</option>)}
                </Select>
                <Select label="Spam Score" value={filters.spam} onChange={(value) => setFilter('spam', value)}>
                    <option value="">Spam Score</option>
                    {spamBands.map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                </Select>
                <Select label="Link type" value={filters.type} onChange={(value) => setFilter('type', value)}>
                    <option value="">Link type</option>
                    {['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => <option key={type}>{type}</option>)}
                </Select>
                <Select label="Link validity" value={filters.validity} onChange={(value) => setFilter('validity', value)}>
                    <option value="">Link validity</option>
                    {['Permanent', '12 months', '6 months'].map((item) => <option key={item}>{item}</option>)}
                </Select>
                <Select label="Country" value={filters.country} onChange={(value) => setFilter('country', value)}>
                    <option value="">Country</option>
                    {countries.map((country) => <option key={country}>{country}</option>)}
                </Select>
                <button type="button" className="btn-outline border-brand text-brand uppercase" aria-expanded={bulkOpen} onClick={() => setBulkOpen((open) => !open)}>Bulk search</button>
                {bulkOpen ? (
                    <label className="basis-full text-sm font-bold">Domains to search, one per line
                        <textarea className="field mt-2 min-h-24" value={bulk} placeholder="One domain per line" onChange={(event) => { setBulk(event.target.value); setPage(1); }} />
                    </label>
                ) : null}
            </div>

            <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p className="font-extrabold" aria-live="polite">Showing {from}–{start + shown.length} of {visible.length} publishers</p>
                <input className="field max-w-xs" type="search" placeholder="Search by website or niche" aria-label="Search by website or niche" value={query} onChange={(event) => { setQuery(event.target.value); setPage(1); }} />
                <select className="field max-w-40" aria-label="Rows per page" value={pageSize} onChange={(event) => { setPageSize(Number(event.target.value)); setPage(1); }}>
                    {[10, 25, 50].map((size) => <option key={size} value={size}>{size} per page</option>)}
                </select>
                <select className="field max-w-56" aria-label="Sort publishers" value={sort} onChange={(event) => { setSort(event.target.value); setPage(1); }}>
                    <option value="traffic">Sort by traffic</option>
                    <option value="dr">Sort by DR</option>
                    <option value="da">Sort by DA</option>
                    <option value="price">Sort by price</option>
                    <option value="added">Sort by recently added</option>
                </select>
            </div>

            <div id="all-publishers" className="mt-4">
                {shown.length === 0 ? <p className="rounded-2xl bg-white p-5 text-[#4A5560]">No publisher matches these filters.</p> : <PublisherTable rows={shown} shell={shell} send={send} selectable={selectable} picked={picked} limit={limit} onToggle={onToggle} priceHeading={priceHeading} />}
            </div>
            {pages > 1 ? (
                <div className="mt-4 flex flex-wrap gap-2">
                    {Array.from({ length: pages }, (_, index) => index + 1).map((numberPage) => (
                        <button key={numberPage} type="button" className={`rounded-lg px-3 py-2 text-sm font-bold ${numberPage === current ? 'bg-navy text-white' : 'bg-white'}`} onClick={() => setPage(numberPage)}>{numberPage}</button>
                    ))}
                </div>
            ) : null}
        </section>
    );
}

function Select({ label, value, onChange, children }) {
    return (
        <select className="field w-auto min-w-36" aria-label={label} value={value} onChange={(event) => onChange(event.target.value)}>
            {children}
        </select>
    );
}

function matches(row, filters, query, terms) {
    const search = `${row.domain || ''} ${(row.niches || []).join(' ')} ${row.language || ''}`.toLowerCase();

    if (query.trim() && !search.includes(query.trim().toLowerCase())) {
        return false;
    }

    if (terms.length && !terms.some((term) => String(row.domain || '').toLowerCase().includes(term))) {
        return false;
    }

    const minimums = { as: 'as', da: 'da', dr: 'dr', pa: 'pa', tf: 'tf', traffic: 'traffic', semrush: 'semrush_traffic', backlinks: 'backlinks', referring: 'referring_domains' };

    for (const [filter, field] of Object.entries(minimums)) {
        if (filters[filter] !== '' && number(row, field) < Number(filters[filter])) {
            return false;
        }
    }

    if (filters.price !== '' && number(row, 'price') > Number(filters.price)) {
        return false;
    }

    if (filters.spam !== '' && number(row, 'spam') > Number(filters.spam)) {
        return false;
    }

    const exact = {
        language: row.language || '',
        country: countryCode(row),
        type: row.link_type || '',
        validity: row.link_validity || '',
        sports: flagOf(row.sports_allowed),
        pharmacy: flagOf(row.pharmacy_allowed),
        crypto: flagOf(row.crypto_allowed),
        news: flagOf(row.google_news),
        sponsored: flagOf(row.sponsored),
        foreign: flagOf(row.foreign_language),
    };

    for (const [filter, value] of Object.entries(exact)) {
        if (filters[filter] !== '' && value !== filters[filter]) {
            return false;
        }
    }

    if (filters.niche && !(row.niches || []).includes(filters.niche)) {
        return false;
    }

    return true;
}

function sortValue(row, key) {
    if (key === 'added') {
        const added = row.added_at;

        return typeof added === 'number' ? added : Date.parse(added || 0) || 0;
    }

    if (key === 'semrush') {
        return number(row, 'semrush_traffic');
    }

    return number(row, key);
}

function unique(values) {
    return [...new Set(values.map((value) => String(value || '').trim()).filter(Boolean))].sort();
}

export function PublisherTable({ rows, shell, send, selectable = false, picked = [], limit = 0, onToggle, priceHeading = 'Price' }) {
    const saved = new Set(shell?.wishlistDomains || []);
    const headings = ['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic'];

    if (selectable) {
        headings.push('Backlinks', 'Referring domains');
    }

    headings.push(priceHeading, selectable ? 'Select' : 'Buy now');

    return (
        <div className="overflow-x-auto rounded-[20px] border border-[#E3E5E8] bg-white">
            <table className="w-full min-w-[1330px] border-separate border-spacing-0 text-left text-[13px]">
                <thead>
                    <tr>
                        {headings.map((heading) => (
                            <th key={heading} className="border-r border-white/30 border-b border-b-[#B7C0C8] bg-navy px-2 py-3 text-center text-xs font-extrabold text-white last:border-r-0">{heading}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => {
                        const domain = row.domain;
                        const on = saved.has(String(domain || '').toLowerCase());
                        const publish = `/publish/${encodeURIComponent(row.id && !row.demo ? row.id : domain)}`;

                        const chosen = picked.includes(domain);
                        const frozen = selectable && limit > 0 && !chosen && picked.length >= limit;

                        return (
                            <tr key={domain} className={`border-t border-[#E3E5E8] ${frozen ? 'opacity-45' : ''}`}>
                                <td className="border-r border-b border-[#B7C0C8] px-3 py-2 text-center font-extrabold">
                                    <span className="inline-flex items-center gap-2">
                                        <A href={row.demo || !row.id ? publish : `/marketplace/${row.id}`}>{domain}</A>
                                        <button type="button" className={on ? 'text-brand' : 'text-navy/40'} aria-pressed={on} aria-label={on ? `Remove ${domain} from wishlist` : `Save ${domain}`} onClick={() => send(on ? '/wishlist' : '/wishlist', on ? 'DELETE' : 'POST', { domain })}>★</button>
                                    </span>
                                </td>
                                {['as', 'da', 'dr'].map((field) => <td key={field} className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center text-[15px] font-extrabold">{row[field] ?? ''}</td>)}
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center">{(row.niches || []).join(', ') || row.niche || ''}</td>
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center">{row.language || ''}</td>
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2"><div className="flex justify-center"><Sparkline seed={`${domain}-ahrefs`} color="#E98989" value={row.traffic} label={`Ahrefs traffic trend for ${domain}`} /></div></td>
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center font-extrabold">{compactNumber(row.keywords)}</td>
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2">
                                    <div className="flex flex-col items-center gap-1">
                                        {(row.top_countries || []).slice(0, 3).map((country) => {
                                            const code = String(country[0] || '');
                                            const flag = code.toUpperCase() === 'UK' ? 'gb' : code.toLowerCase();

                                            return (
                                                <span key={code} className="inline-flex items-center gap-1.5 text-xs font-bold">
                                                    <Flag code={flag} />
                                                    {Number(country[1] || 0).toFixed(1)}%
                                                </span>
                                            );
                                        })}
                                    </div>
                                </td>
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2"><div className="flex justify-center"><Sparkline seed={`${domain}-semrush`} color="#7DCEA0" value={row.semrush_traffic} label={`Semrush traffic trend for ${domain}`} /></div></td>
                                {selectable ? (
                                    <>
                                        <td className="border-r border-b border-[#B7C0C8] px-2 py-2">
                                            <div className="flex flex-col items-center">
                                                <Sparkline seed={`${domain}-backlinks`} color="#3D7BC4" value={row.backlinks} label={`Backlinks for ${domain}`} />
                                                {row.metrics_source === 'dataforseo' ? <p className="text-[10px] font-extrabold text-[#17663A]">DataForSEO</p> : null}
                                            </div>
                                        </td>
                                        <td className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center font-extrabold">{compactNumber(row.referring_domains)}</td>
                                    </>
                                ) : null}
                                <td className="border-r border-b border-[#B7C0C8] px-2 py-2 text-center font-extrabold">{row.price == null ? '' : `$${Number(row.price).toFixed(2)}`}</td>
                                <td className="border-b border-[#B7C0C8] px-2 py-2 text-center">
                                    {selectable ? (
                                        <label className="inline-flex min-h-11 min-w-11 items-center justify-center">
                                            <input type="checkbox" name="websites[]" value={domain} checked={chosen} disabled={frozen} onChange={() => onToggle?.(domain)} />
                                            <span className="sr-only">Select {domain}</span>
                                        </label>
                                    ) : <A href={publish} className="inline-flex min-h-9 items-center rounded-[10px] bg-brand-dark px-3 text-[11px] font-extrabold tracking-wide text-white uppercase hover:bg-brand">Publish</A>}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

function compactNumber(value) {
    const numberValue = Number(value);

    if (!Number.isFinite(numberValue)) {
        return '';
    }

    if (numberValue >= 1000000) {
        return `${(numberValue / 1000000).toFixed(1).replace(/\.0$/, '')}M`;
    }

    if (numberValue >= 1000) {
        return `${(numberValue / 1000).toFixed(1).replace(/\.0$/, '')}K`;
    }

    return String(Math.round(numberValue));
}

function applyNicheFilters(form, slug, navigate) {
    const data = new FormData(form);
    const next = new URLSearchParams();
    data.forEach((value, key) => {
        if (String(value).trim() !== '') {
            next.set(key, value);
        }
    });
    navigate(`/niches/${slug}?${next.toString()}`);
}

export function NicheFilters({ page, search }) {
    const navigate = useNavigate();
    const current = new URLSearchParams(search);
    const fields = [
        ['as_min', 'AS', mins],
        ['da_min', 'DA', mins],
        ['dr_min', 'DR', [...mins, 70]],
        ['pa_min', 'PA', mins],
        ['tf_min', 'TF', mins],
    ];

    return (
        <form key={search} className="space-y-3" onSubmit={(event) => {
            event.preventDefault();
            applyNicheFilters(event.currentTarget, page.data.slug, navigate);
        }} onChange={(event) => {
            if (event.target.tagName === 'SELECT') {
                applyNicheFilters(event.currentTarget, page.data.slug, navigate);
            }
        }}>
            <div className="flex flex-wrap gap-2">
                {fields.map(([name, label, values]) => (
                    <select key={name} className="field w-auto min-w-32" name={name} aria-label={`Minimum ${label}`} defaultValue={current.get(name) || ''}>
                        <option value="">{label}</option>
                        {values.map((value) => <option key={value} value={value}>{label} {value}+</option>)}
                    </select>
                ))}
                <select className="field w-auto" name="price_max" aria-label="Price range" defaultValue={current.get('price_max') || ''}>
                    <option value="">Price range</option>
                    {[100, 250, 500, 1000].map((value) => <option key={value} value={value}>Up to ${value.toLocaleString()}</option>)}
                </select>
                <select className="field w-auto" name="traffic_min" aria-label="Minimum Ahrefs traffic" defaultValue={current.get('traffic_min') || ''}>
                    <option value="">Ahrefs traffic</option>
                    {trafficBands.map(([value, label]) => <option key={value} value={value}>Ahrefs {label}</option>)}
                </select>
                <select className="field w-auto" name="semrush_min" aria-label="Minimum Semrush traffic" defaultValue={current.get('semrush_min') || ''}>
                    <option value="">Semrush traffic</option>
                    {trafficBands.map(([value, label]) => <option key={value} value={value}>Semrush {label}</option>)}
                </select>
                <select className="field w-auto" name="language" aria-label="Language" defaultValue={current.get('language') || ''}>
                    <option value="">Language</option>
                    {(page.data.languages || []).map((language) => <option key={language}>{language}</option>)}
                </select>
                {[['sports', 'Sports/Gaming allowed?'], ['pharmacy', 'Pharmacy allowed?'], ['crypto', 'Crypto allowed?']].map(([name, label]) => (
                    <select key={name} className="field w-auto" name={name} aria-label={label} defaultValue={current.get(name) || ''}>
                        <option value="">{label}</option><option value="yes">Yes</option><option value="no">No</option>
                    </select>
                ))}
                <select className="field w-auto" name="backlinks_min" aria-label="Number of backlinks" defaultValue={current.get('backlinks_min') || ''}>
                    <option value="">No. of backlinks</option>
                    {countBands.map((value) => <option key={value} value={value}>{value.toLocaleString()}+</option>)}
                </select>
                <select className="field w-auto" name="referring_min" aria-label="Number of referring domains" defaultValue={current.get('referring_min') || ''}>
                    <option value="">No. of referring domains</option>
                    {countBands.map((value) => <option key={value} value={value}>{value.toLocaleString()}+</option>)}
                </select>
                <select className="field w-auto" name="spam_max" aria-label="Spam Score" defaultValue={current.get('spam_max') || ''}>
                    <option value="">Spam Score</option>
                    {spamBands.map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                </select>
                <select className="field w-auto" name="link_type" aria-label="Link type" defaultValue={current.get('link_type') || ''}>
                    <option value="">Link type</option>
                    {['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => <option key={type}>{type}</option>)}
                </select>
                <select className="field w-auto" name="link_validity" aria-label="Link validity" defaultValue={current.get('link_validity') || ''}>
                    <option value="">Link validity</option>
                    {['Permanent', '12 months', '6 months'].map((item) => <option key={item}>{item}</option>)}
                </select>
                {[['google_news', 'Google News'], ['sponsored', 'Marked as sponsored'], ['foreign_language', 'Foreign lang. allowed?']].map(([name, label]) => (
                    <select key={name} className="field w-auto" name={name} aria-label={label} defaultValue={current.get(name) || ''}>
                        <option value="">{label}</option><option value="yes">Yes</option><option value="no">No</option>
                    </select>
                ))}
                <select className="field w-auto" name="country" aria-label="Country" defaultValue={current.get('country') || ''}>
                    <option value="">Country</option>
                    {(page.data.countries || []).map((country) => <option key={country}>{country}</option>)}
                </select>
                <select className="field w-auto" name="per_page" aria-label="Publishers per page" defaultValue={current.get('per_page') || '20'}>
                    {[20, 25, 50, 100].map((size) => <option key={size} value={size}>{size} per page</option>)}
                </select>
                <select className="field w-auto" name="sort" aria-label="Sort publishers" defaultValue={current.get('sort') || 'traffic_desc'}>
                    <option value="traffic_desc">Sort by Ahrefs traffic</option>
                    <option value="semrush_desc">Sort by Semrush traffic</option>
                    <option value="dr_desc">Sort by DR</option>
                    <option value="da_desc">Sort by DA</option>
                    <option value="as_desc">Sort by AS</option>
                    <option value="price_asc">Price: low to high</option>
                    <option value="price_desc">Price: high to low</option>
                    <option value="newest">Newest</option>
                </select>
                <select className="field w-auto" name="view" aria-label="Change website view" defaultValue={current.get('view') || 'table'}>
                    <option value="table">Table</option>
                    <option value="cards">Cards</option>
                </select>
                <button className="btn-navy" type="submit">Filter</button>
                <A href={`/niches/${page.data.slug}`} className="inline-flex min-h-11 items-center text-sm font-bold text-brand-dark">Clear filters</A>
            </div>
            <div className="flex flex-wrap gap-2">
                <input className="field max-w-sm" type="search" name="search" defaultValue={current.get('search') || ''} placeholder="Search by website or niche" aria-label={`Search ${page.data.name} websites`} />
            </div>
            <label className="block text-sm font-bold">Bulk domains, one per line
                <textarea className="field mt-2 min-h-24" name="bulk" defaultValue={current.get('bulk') || ''} placeholder="One domain per line" />
            </label>
        </form>
    );
}
