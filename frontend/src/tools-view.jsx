import { useNavigate } from 'react-router-dom';
import { A } from './chrome';
import { Flag, compact } from './marks';

export function ToolView({ page }) {
    const tool = page.data.tool || {};
    const navigate = useNavigate();
    const site = page.data.site;
    const other = page.data.other;
    const report = page.data.anchorReport;

    function search(event) {
        event.preventDefault();
        const data = new FormData(event.currentTarget);
        const next = new URLSearchParams();
        data.forEach((value, key) => { if (value) next.set(key, value); });
        navigate(`/tools/${tool.slug}?${next.toString()}`);
    }

    return (
        <section className="bg-[#F2F2F2] px-5 py-14">
            <div className="mx-auto w-full">
                <nav className="text-sm font-bold text-[#4A5560]" aria-label="Breadcrumb"><A href="/">Home</A> / <A href="/tools">Tools</A> / <span className="text-navy">{tool.label}</span></nav>
                <div className="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                    <div>
                        <p className="text-[13px] font-extrabold tracking-wide text-brand-dark uppercase">SEO tools</p>
                        <h1 className="mt-2 text-3xl font-extrabold lg:text-5xl">{tool.label}</h1>
                        <p className="mt-4 max-w-3xl text-[17px] leading-8 text-[#4A5560]">{tool.about}</p>
                        {page.data.demo && tool.kind !== 'anchor' ? <p className="mt-4 rounded-xl bg-[#FFF1EC] px-4 py-3 text-[15px] text-brand-dark">Sample data for preview. These figures belong to fictional .example publishers.</p> : null}
                    </div>
                    <aside className="rounded-[20px] border border-[#E3E5E8] bg-white p-5">
                        <h2 className="text-lg font-extrabold">What this tool shows</h2>
                        <ul className="mt-3 space-y-2 text-[15px] text-[#4A5560]">{(tool.checks || []).map((check) => <li key={check}>{check}</li>)}</ul>
                    </aside>
                </div>

                <form className="mt-8 grid gap-4 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6" onSubmit={search}>
                    {tool.kind === 'anchor' ? (
                        <label className="text-[15px] font-bold">Anchors, one per line
                            <textarea className="field mt-2 min-h-36" name="anchors" defaultValue={page.data.anchors || ''} />
                        </label>
                    ) : (
                        <div className={`grid gap-4 ${tool.kind === 'compare' || tool.kind === 'competitor' ? 'md:grid-cols-2' : ''}`}>
                            <label className="text-[15px] font-bold">{tool.kind === 'competitor' ? 'First website' : 'Publisher domain'}
                                <input className="field mt-2" name="domain" defaultValue={page.data.domain || ''} maxLength={253} placeholder="publisher-domain.example" />
                            </label>
                            {tool.kind === 'competitor' ? (
                                <label className="text-[15px] font-bold">Second website
                                    <input className="field mt-2" name="compare" defaultValue={page.data.compare || ''} maxLength={253} placeholder="another-domain.example" />
                                </label>
                            ) : null}
                        </div>
                    )}
                    <button className="btn-primary w-fit" type="submit">{tool.kind === 'anchor' ? 'Analyze anchors' : tool.kind === 'competitor' ? 'Compare' : 'Check website'}</button>
                    {tool.kind !== 'anchor' && (page.data.suggestions || []).length > 0 ? (
                        <div>
                            <p className="text-[13px] font-bold tracking-wide text-[#4A5560] uppercase">Listed publishers</p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                {page.data.suggestions.map((suggestion) => (
                                    <A key={suggestion.domain} href={`/tools/${tool.slug}?domain=${encodeURIComponent(suggestion.domain)}${tool.kind === 'competitor' && page.data.compare ? `&compare=${encodeURIComponent(page.data.compare)}` : ''}`} className="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-sm font-bold hover:bg-[#FFF1EC]">{suggestion.domain}</A>
                                ))}
                            </div>
                        </div>
                    ) : null}
                </form>

                {tool.kind === 'anchor' && report ? <AnchorChart report={report} /> : null}
                {tool.kind === 'competitor' && page.data.checked ? <CompareTable domain={page.data.domain} compare={page.data.compare} site={site} other={other} /> : null}
                {page.data.checked && tool.kind !== 'anchor' && tool.kind !== 'competitor' ? (
                    site ? <MetricLayout tool={tool} site={site} /> : <p className="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4" role="status">That website is not in the publisher list, so this checker has no stored metrics for it.</p>
                ) : null}

                <div className="mt-10">
                    <h2 className="text-[22px] font-extrabold">Other tools</h2>
                    <ul className="mt-4 grid gap-3 sm:grid-cols-2">
                        {(page.data.tools || []).filter((item) => item.slug !== tool.slug).map((item) => (
                            <li key={item.slug}><A href={`/tools/${item.slug}`} className="flex min-h-11 flex-col justify-center rounded-xl border border-[#E3E5E8] bg-white px-4 py-3 hover:border-brand-dark"><span className="font-bold">{item.label}</span><span className="mt-1 text-sm text-[#4A5560]">{item.summary}</span></A></li>
                        ))}
                    </ul>
                </div>
            </div>
        </section>
    );
}

function AnchorChart({ report }) {
    return (
        <div className="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
            <h2 className="text-[22px] font-extrabold">Anchor profile</h2>
            <div className="mt-4 grid gap-4 sm:grid-cols-3">
                {[['Total', report.total], ['Unique', report.unique], ['Largest share', `${report.topShare}%`]].map(([label, value]) => (
                    <p key={label} className="rounded-xl bg-[#F2F2F2] p-4"><span className="block text-[13px] font-bold text-[#4A5560] uppercase">{label}</span><span className="mt-1 block text-[32px] font-extrabold">{value}</span></p>
                ))}
            </div>
            <p className="mt-4 text-[15px] leading-7 text-[#4A5560]">{report.note}</p>
            <ul className="mt-5 space-y-3">
                {(report.rows || []).map((row) => (
                    <li key={row.label}>
                        <div className="flex justify-between gap-3 text-[15px] font-bold"><span>{row.label}</span><span>{row.count} · {row.share}%</span></div>
                        <div className="mt-1 h-2 overflow-hidden rounded-full bg-[#F2F2F2]" role="meter" aria-label={row.label} aria-valuemin={0} aria-valuemax={100} aria-valuenow={row.share}>
                            <div className="h-full rounded-full bg-brand" style={{ width: `${row.share}%` }} />
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function CompareTable({ domain, compare, site, other }) {
    const rows = [['backlinks', 'Backlinks'], ['referring_domains', 'Referring domains'], ['dr', 'Domain Rating'], ['da', 'Domain Authority'], ['traffic', 'Monthly traffic']];

    return (
        <div className="mt-6 overflow-x-auto rounded-[20px] border border-[#E3E5E8] bg-white">
            <table className="w-full min-w-[640px] text-left text-[15px]">
                <caption className="px-5 py-4 text-left text-[22px] font-extrabold">Side by side</caption>
                <thead className="bg-[#F7F8F9] text-[13px] tracking-wide text-[#4A5560] uppercase"><tr><th className="px-5 py-3">Metric</th><th className="px-5 py-3">{domain || 'First website'}</th><th className="px-5 py-3">{compare || 'Second website'}</th></tr></thead>
                <tbody>
                    {rows.map(([key, label]) => (
                        <tr key={key} className="border-t border-[#E3E5E8]"><th className="px-5 py-3">{label}</th><td className="px-5 py-3">{cell(site, key)}</td><td className="px-5 py-3">{cell(other, key)}</td></tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function cell(row, key) {
    if (!row) {
        return 'Not listed';
    }

    return ['traffic', 'backlinks', 'referring_domains'].includes(key) ? compact(row[key]) : (row[key] ?? '');
}

function MetricLayout({ tool, site }) {
    const score = { authority: site.as, dr: site.dr, da: site.da, spam: site.spam }[tool.kind];
    const label = { authority: 'Authority score', dr: 'Domain Rating', da: 'Domain Authority', spam: 'Spam score' }[tool.kind];
    const niche = site.niches?.[0];
    const nicheHref = niche && niche !== 'Publisher' ? `/niches/${niche.toLowerCase().replace(/&/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}` : null;

    return (
        <div className="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-[26px] font-extrabold">{site.domain}</h2>
                    <p className="mt-1 text-[15px] text-[#4A5560]">{(site.niches || []).join(' · ')}{site.language ? ` · ${site.language}` : ''}</p>
                </div>
                {nicheHref ? <A href={nicheHref} className="font-bold text-brand-dark">View this niche</A> : null}
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                {label ? <Meter score={score} label={label} /> : null}
                {tool.kind === 'backlinks' ? (
                    <>
                        <Big label="Backlinks" value={compact(site.backlinks)} />
                        <Big label="Referring domains" value={compact(site.referring_domains)} />
                    </>
                ) : null}
                {tool.kind === 'traffic' ? (
                    <>
                        <Big label="Ahrefs traffic" value={compact(site.traffic)} />
                        <Big label="Semrush traffic" value={compact(site.semrush_traffic)} />
                    </>
                ) : null}
            </div>
            {tool.kind === 'spam' ? <p className="mt-4 text-[17px] font-bold">Band: {spamBand(site.spam)}</p> : null}
            {['authority', 'dr', 'da', 'spam'].includes(tool.kind) ? (
                <dl className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    {[['as', 'Authority score'], ['dr', 'Domain Rating'], ['da', 'Domain Authority'], ['spam', 'Spam score'], ['traffic', 'Ahrefs traffic'], ['referring_domains', 'Referring domains']].map(([key, name]) => (
                        <div key={key} className="rounded-xl border border-[#E3E5E8] px-4 py-3"><dt className="text-[13px] font-bold text-[#4A5560]">{name}</dt><dd className="mt-1 text-[22px] font-extrabold">{['traffic', 'referring_domains'].includes(key) ? compact(site[key]) : (site[key] ?? '')}</dd></div>
                    ))}
                </dl>
            ) : null}
            {tool.kind === 'backlinks' ? (
                <dl className="mt-6 grid gap-3 sm:grid-cols-2">
                    <div className="rounded-xl border border-[#E3E5E8] px-4 py-3"><dt className="text-[13px] font-bold text-[#4A5560]">Link type</dt><dd className="mt-1 text-[17px] font-bold">{site.link_type || ''}</dd></div>
                    <div className="rounded-xl border border-[#E3E5E8] px-4 py-3"><dt className="text-[13px] font-bold text-[#4A5560]">Link validity</dt><dd className="mt-1 text-[17px] font-bold">{site.link_validity || ''}</dd></div>
                </dl>
            ) : null}
            {tool.kind === 'traffic' ? <Countries site={site} /> : null}
        </div>
    );
}

function Meter({ score, label }) {
    const value = Number.isFinite(Number(score)) ? Math.max(0, Math.min(100, Number(score))) : null;

    return (
        <div>
            <div className="flex items-end justify-between"><p className="text-[13px] font-bold tracking-wide text-[#4A5560] uppercase">{label}</p><p className="text-[40px] leading-none font-extrabold">{value ?? ''}</p></div>
            <div className="mt-3 h-3 overflow-hidden rounded-full bg-[#F2F2F2]" role="meter" aria-label={label} aria-valuemin={0} aria-valuemax={100} aria-valuenow={value ?? undefined}>
                <div className="h-full rounded-full bg-brand" style={{ width: `${value ?? 0}%` }} />
            </div>
        </div>
    );
}

function Big({ label, value }) {
    return <p className="rounded-xl bg-[#F2F2F2] p-4"><span className="block text-[13px] font-bold text-[#4A5560] uppercase">{label}</span><span className="mt-1 block text-[40px] leading-none font-extrabold">{value}</span></p>;
}

function spamBand(spam) {
    if (!Number.isFinite(Number(spam))) {
        return 'Not stored';
    }

    const value = Number(spam);

    if (value <= 30) return 'Low';
    if (value <= 60) return 'Medium';

    return 'High';
}

function Countries({ site }) {
    const countries = site.top_countries || [];

    return (
        <div className="mt-6">
            <h3 className="text-[15px] font-extrabold">Top countries</h3>
            {countries.length === 0 ? <p className="mt-2 text-[15px] text-[#4A5560]">No country split is stored for this website.</p> : (
                <ul className="mt-3 space-y-2">
                    {countries.map((country) => {
                        const raw = String(country[0] || '').toUpperCase();
                        const share = Math.max(0, Math.min(100, Number(country[1]) || 0));

                        return (
                            <li key={raw} className="flex min-h-11 items-center gap-3">
                                <Flag code={raw === 'UK' ? 'gb' : raw} />
                                <span className="w-10 text-sm font-bold uppercase">{raw}</span>
                                <span className="h-2 flex-1 overflow-hidden rounded-full bg-[#F2F2F2]" role="meter" aria-label={raw} aria-valuemin={0} aria-valuemax={100} aria-valuenow={share}><span className="block h-full rounded-full bg-brand" style={{ width: `${share}%` }} /></span>
                                <span className="w-12 text-right text-sm font-bold">{share}%</span>
                            </li>
                        );
                    })}
                </ul>
            )}
            <p className="mt-4 text-[15px] text-[#4A5560]">Organic keywords: {compact(site.keywords)}</p>
        </div>
    );
}
