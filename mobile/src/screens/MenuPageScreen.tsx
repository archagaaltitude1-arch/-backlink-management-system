import { useNavigation, useRoute, type RouteProp } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useEffect, useMemo, useRef, useState, type ComponentRef } from 'react';
import { ActivityIndicator, Linking, Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import Svg, { Polygon, Polyline } from 'react-native-svg';

import { apiRequest, errorText, hasErrors } from '../api/client';
import { apiUrl } from '../config';
import { compactCount, money, parseShell, type Shell } from '../api/home';
import { useAuth } from '../context/AuthContext';
import { Flag } from '../flags';
import type { RootStack } from '../navigation/types';
import { AccountFlow, isAccountPath, isAccountView } from './account-pages';
import { CartPage, CheckoutPage, ContactPage, CouponOverlay, couponOffer, PaymentPage, PublishOrder, PublisherCatalog, ThanksPage } from './live-pages';
import { CountryAndFooter, ServicePageEnd } from './service-end';
import { SiteBar } from './site-bar';
import { colors } from '../theme';

type PageBody = {
    view: string;
    data: Record<string, unknown>;
    flash?: Record<string, unknown>;
    errors?: Record<string, unknown>;
    payments?: Record<string, unknown>;
};

export function MenuPageScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const route = useRoute<RouteProp<RootStack, 'MenuPage'>>();
    const [page, setPage] = useState<PageBody | null>(null);
    const [shell, setShell] = useState<Shell | null>(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);
    const [closedOffer, setClosedOffer] = useState('');
    const [couponPending, setCouponPending] = useState(false);
    const [couponProblem, setCouponProblem] = useState('');
    const scrollRef = useRef<ComponentRef<typeof ScrollView>>(null);
    const nicheEndY = useRef(0);

    useEffect(() => {
        let active = true;
        setLoading(true);
        apiRequest(route.params.path, { timeoutMs: 30000 })
            .then((result) => {
                if (!active) {
                    return;
                }

                const redirect = typeof result.body.redirect === 'string' ? result.body.redirect : '';

                if (isAccountPath(route.params.path) && (result.status === 401 || result.status === 419 || redirect.includes('/login'))) {
                    navigation.navigate('Login');

                    return;
                }

                if (redirect !== '' && isAccountPath(route.params.path)) {
                    const target = appPath(redirect);

                    if (target.split('?')[0] !== route.params.path.split('?')[0]) {
                        navigation.replace('MenuPage', { path: target });
                    }

                    return;
                }

                if (result.status >= 400 || typeof result.body.view !== 'string') {
                    setError('This page could not be loaded.');
                    setPage(null);
                    setShell(null);

                    return;
                }

                setPage({
                    view: result.body.view,
                    data: record(result.body.data),
                    flash: record(result.body.flash),
                    errors: record(result.body.errors),
                    payments: record(result.body.payments),
                });
                setShell(parseShell(result.body.shell));
                setError('');
            })
            .catch(() => {
                if (active) {
                    setError('This page could not be loaded.');
                }
            })
            .finally(() => {
                if (active) {
                    setLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, [route.params.path]);

    const pathname = route.params.path.split('#')[0].split('?')[0];

    useEffect(() => {
        scrollRef.current?.scrollTo({ y: 0, animated: false });
    }, [pathname]);

    function open(path: string) {
        const target = appPath(path).split('#')[0];

        if (target === '/login' || target.startsWith('/login?')) {
            navigation.navigate('Login');

            return;
        }

        if (target === '/register' || target.startsWith('/register?')) {
            navigation.navigate('Register');

            return;
        }

        const current = route.params.path.split('#')[0];

        if (target.split('?')[0] === current.split('?')[0]) {
            if (target !== current) {
                navigation.setParams({ path: target });
            }

            return;
        }

        navigation.push('MenuPage', { path: target });
    }

    const nichePage = page?.view === 'customer.niches.index' || page?.view === 'customer.niches.show';
    const offer = page ? couponOffer(page.view, page.data, page.flash) : null;
    const offerKey = offer ? `${route.params.path.split('?')[0]}:${offer.coupons.map((coupon) => text(coupon.code)).join(',')}` : '';

    async function applyOffer(code: string) {
        if (!page || !offer) {
            return;
        }

        setCouponPending(true);
        setCouponProblem('');
        const orderId = text(record(page.data.order).id);
        const endpoint = page.view === 'orders.payment' ? `/orders/${orderId}/coupon` : '/cart/coupon';
        const result = await apiRequest(endpoint, { method: 'POST', body: { coupon_code: code } });
        setCouponPending(false);

        if (hasErrors(result.body) || result.status >= 400) {
            setCouponProblem(errorText(result.body));

            return;
        }

        const redirect = typeof result.body.redirect === 'string' ? appPath(result.body.redirect) : '';
        const next = redirect !== '' && !redirect.startsWith('http')
            ? redirect
            : page.view === 'orders.payment'
                ? `/orders/${orderId}/payment`
                : '/cart/checkout';
        setClosedOffer(offerKey);
        navigation.setParams({ path: reloadPath(next) });
    }

    return (
        <View style={styles.screen}>
            <SiteBar />
            <ScrollView
                ref={scrollRef}
                style={styles.fill}
                contentContainerStyle={styles.content}
            >
            {loading ? <ActivityIndicator color={colors.brand} /> : null}
            {error ? <Text style={styles.error}>{error}</Text> : null}
            {page?.view === 'customer.niches.index' ? <NicheIndex data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.niches.show' ? <NicheShow key={route.params.path} data={page.data} path={route.params.path} onOpen={open} /> : null}
            {page?.view === 'customer.packages.index' ? <PackageIndex data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.packages.show' ? <PackageShow data={page.data} flash={page.flash} path={route.params.path} onOpen={open} /> : null}
            {page?.view === 'customer.packages.websites' ? <WebsitePick data={page.data} shell={shell} /> : null}
            {page?.view === 'customer.sites.index' ? <SiteIndex data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.sites.show' ? <SiteShow data={page.data} shell={shell} onOpen={open} /> : null}
            {page?.view === 'customer.tools.index' ? <ToolIndex data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.tools.show' ? <ToolShow data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.wishlist.index' ? <Wishlist data={page.data} onChanged={() => navigation.setParams({ path: `/wishlist?t=${Date.now()}` })} /> : null}
            {page?.view === 'customer.publishers.index' || page?.view === 'customer.sites.marketplace' ? <PublisherCatalog data={page.data} onOpen={open} /> : null}
            {page?.view === 'customer.publish.show' ? <PublishOrder data={page.data} flash={page.flash} onOpen={open} /> : null}
            {page?.view === 'customer.pages.contact' ? <ContactPage /> : null}
            {page?.view === 'customer.shopping.index' ? <CartPage data={page.data} flash={page.flash} onOpen={open} /> : null}
            {page?.view === 'customer.shopping.checkout' ? <CheckoutPage data={page.data} flash={page.flash} onOpen={open} /> : null}
            {page?.view === 'orders.payment' ? <PaymentPage data={page.data} flash={page.flash} payments={page.payments} onOpen={open} /> : null}
            {page?.view === 'orders.thanks' ? <ThanksPage data={page.data} flash={page.flash} /> : null}
            {page && isAccountView(page.view) ? (
                <AccountFlow
                    key={route.params.path.split('?')[0]}
                    view={page.view}
                    data={page.data}
                    flash={page.flash}
                    errors={page.errors}
                    payments={page.payments}
                    path={route.params.path}
                    onOpen={open}
                    onReload={() => navigation.setParams({ path: reloadPath(route.params.path) })}
                />
            ) : null}
            {nichePage && shell ? (
                <View style={styles.nicheEnd} onLayout={(event) => { nicheEndY.current = event.nativeEvent.layout.y; }}>
                    <ServicePageEnd shell={shell} onBrowseCountry={(y) => scrollRef.current?.scrollTo({ y: nicheEndY.current + y, animated: true })} />
                </View>
            ) : null}
            {!nichePage ? (
                <View style={styles.nicheEnd} onLayout={(event) => { nicheEndY.current = event.nativeEvent.layout.y; }}>
                    <CountryAndFooter onBrowseCountry={() => scrollRef.current?.scrollTo({ y: nicheEndY.current, animated: true })} />
                </View>
            ) : null}
            </ScrollView>
            {offer && closedOffer !== offerKey ? (
                <CouponOverlay
                    coupons={offer.coupons}
                    pending={couponPending}
                    problem={couponProblem}
                    title={offer.title}
                    copy={offer.copy}
                    onApply={(code) => { void applyOffer(code); }}
                    onClose={() => { setCouponProblem(''); setClosedOffer(offerKey); }}
                />
            ) : null}
        </View>
    );
}

function NicheIndex({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    const niches = rows(data.niches);

    return (
        <View>
            <Text style={styles.h1}>Niches</Text>
            {niches.map((niche) => (
                <Pressable key={text(niche.slug)} style={styles.card} onPress={() => onOpen(`/niches/${text(niche.slug)}`)}>
                    <Text style={styles.cardTitle}>{text(niche.name)}</Text>
                    <Text style={styles.muted}>{text(niche.total)}</Text>
                </Pressable>
            ))}
        </View>
    );
}

function NicheShow({ data, path, onOpen }: { data: Record<string, unknown>; path: string; onOpen: (path: string) => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const name = text(data.name, 'Niche');
    const slug = text(data.slug);
    const websites = record(data.websites);
    const list = rows(websites.data);
    const params = queryOf(path);
    const cards = params.get('view') === 'cards';
    const customIntro = Array.isArray(data.custom_intro) ? data.custom_intro.filter((item): item is string => typeof item === 'string') : null;
    const intro = customIntro ?? [
        `Looking to reach readers in ${name}? Browse our list of ${name} websites that accept guest posts, contextual links, niche edits and text links. Every site is checked for real traffic before it's listed.`,
        'Compare publishers by DR, DA, monthly traffic, spam score and price, then add the ones that fit your campaign to your cart.',
    ];
    const introRelated = rows(data.intro_related);
    const languages = strings(data.languages);
    const countries = strings(data.countries);
    const [search, setSearch] = useState(params.get('search') || '');
    const [bulk, setBulk] = useState(params.get('bulk') || '');

    function apply(key: string, value: string) {
        const next = queryOf(path);
        next.delete('page');

        if (value === '') {
            next.delete(key);
        } else {
            next.set(key, value);
        }

        onOpen(nichePath(slug, next));
    }

    function filter() {
        const next = queryOf(path);
        next.delete('page');
        if (search.trim() === '') {
            next.delete('search');
        } else {
            next.set('search', search.trim());
        }
        if (bulk.trim() === '') {
            next.delete('bulk');
        } else {
            next.set('bulk', bulk);
        }
        onOpen(nichePath(slug, next));
    }

    function newest() {
        const next = queryOf(path);
        next.set('sort', 'newest');
        next.delete('page');
        onOpen(nichePath(slug, next));
    }

    return (
        <View style={styles.nichePage}>
            <View style={styles.nicheHero}>
                <Text style={styles.crumbs}>
                    <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                    <Text> / </Text>
                    <Text onPress={() => onOpen('/niches')}>Niches</Text>
                    <Text> / </Text>
                    <Text style={styles.crumbCurrent}>{name}</Text>
                </Text>
                <Text style={styles.h1}>{name} Guest Posting Sites</Text>
                <Text style={styles.pill}>{text(data.total)} Publishers</Text>
                <Pressable onPress={newest}><Text style={styles.recent}>See recently added sites →</Text></Pressable>
                <Text style={styles.intro}>{intro[0]}</Text>
                <Text style={styles.intro}>
                    {intro[1]}
                    {!customIntro && introRelated.length > 0 ? <Text> Related niches: {introRelated.map((item, index) => <Text key={text(item.slug)}>{index > 0 ? ', ' : ''}<Text style={styles.recent} onPress={() => onOpen(`/niches/${text(item.slug)}`)}>{text(item.name)}</Text></Text>)}.</Text> : null}
                </Text>
            </View>
            <View style={styles.nicheFilters}>
                <View style={styles.specWrap}>
                    <NicheSelect label="AS" value={params.get('as_min') || ''} options={metricMins.map((value) => ({ value: String(value), label: `AS ${value}+` }))} onChange={(value) => apply('as_min', value)} />
                    <NicheSelect label="DA" value={params.get('da_min') || ''} options={metricMins.map((value) => ({ value: String(value), label: `DA ${value}+` }))} onChange={(value) => apply('da_min', value)} />
                    <NicheSelect label="DR" value={params.get('dr_min') || ''} options={[...metricMins, 70].map((value) => ({ value: String(value), label: `DR ${value}+` }))} onChange={(value) => apply('dr_min', value)} />
                    <NicheSelect label="PA" value={params.get('pa_min') || ''} options={metricMins.map((value) => ({ value: String(value), label: `PA ${value}+` }))} onChange={(value) => apply('pa_min', value)} />
                    <NicheSelect label="TF" value={params.get('tf_min') || ''} options={metricMins.map((value) => ({ value: String(value), label: `TF ${value}+` }))} onChange={(value) => apply('tf_min', value)} />
                    <NicheSelect label="Price range" value={params.get('price_max') || ''} options={[100, 250, 500, 1000].map((value) => ({ value: String(value), label: `Up to $${value.toLocaleString()}` }))} onChange={(value) => apply('price_max', value)} />
                    <NicheSelect label="Ahrefs traffic" value={params.get('traffic_min') || ''} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Ahrefs ${label}` }))} onChange={(value) => apply('traffic_min', value)} />
                    <NicheSelect label="Semrush traffic" value={params.get('semrush_min') || ''} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Semrush ${label}` }))} onChange={(value) => apply('semrush_min', value)} />
                    <NicheSelect label="Language" value={params.get('language') || ''} options={languages.map((language) => ({ value: language, label: language }))} onChange={(value) => apply('language', value)} />
                    <NicheSelect label="Sports/Gaming allowed?" value={params.get('sports') || ''} options={yesNo} onChange={(value) => apply('sports', value)} />
                    <NicheSelect label="Pharmacy allowed?" value={params.get('pharmacy') || ''} options={yesNo} onChange={(value) => apply('pharmacy', value)} />
                    <NicheSelect label="Crypto allowed?" value={params.get('crypto') || ''} options={yesNo} onChange={(value) => apply('crypto', value)} />
                    <NicheSelect label="No. of backlinks" value={params.get('backlinks_min') || ''} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => apply('backlinks_min', value)} />
                    <NicheSelect label="No. of referring domains" value={params.get('referring_min') || ''} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => apply('referring_min', value)} />
                    <NicheSelect label="Spam Score" value={params.get('spam_max') || ''} options={spamBands.map(([value, label]) => ({ value: String(value), label }))} onChange={(value) => apply('spam_max', value)} />
                    <NicheSelect label="Link type" value={params.get('link_type') || ''} options={['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => ({ value: type, label: type }))} onChange={(value) => apply('link_type', value)} />
                    <NicheSelect label="Link validity" value={params.get('link_validity') || ''} options={['Permanent', '12 months', '6 months'].map((item) => ({ value: item, label: item }))} onChange={(value) => apply('link_validity', value)} />
                    <NicheSelect label="Google News" value={params.get('google_news') || ''} options={yesNo} onChange={(value) => apply('google_news', value)} />
                    <NicheSelect label="Marked as sponsored" value={params.get('sponsored') || ''} options={yesNo} onChange={(value) => apply('sponsored', value)} />
                    <NicheSelect label="Foreign lang. allowed?" value={params.get('foreign_language') || ''} options={yesNo} onChange={(value) => apply('foreign_language', value)} />
                    <NicheSelect label="Country" value={params.get('country') || ''} options={countries.map((country) => ({ value: country, label: country }))} onChange={(value) => apply('country', value)} />
                    <NicheSelect label="20 per page" value={params.get('per_page') || '20'} options={[20, 25, 50, 100].map((size) => ({ value: String(size), label: `${size} per page` }))} onChange={(value) => apply('per_page', value)} />
                    <NicheSelect label="Sort by Ahrefs traffic" value={params.get('sort') || 'traffic_desc'} options={sortOptions} onChange={(value) => apply('sort', value)} />
                    <NicheSelect label="Table" value={params.get('view') || 'table'} options={[{ value: 'table', label: 'Table' }, { value: 'cards', label: 'Cards' }]} onChange={(value) => apply('view', value)} />
                </View>
                <TextInput style={styles.field} value={search} onChangeText={setSearch} placeholder="Search by website or niche" placeholderTextColor="#8B939C" />
                <Text style={styles.fieldLabel}>Bulk domains, one per line</Text>
                <TextInput style={[styles.field, styles.area]} multiline value={bulk} onChangeText={setBulk} placeholder="One domain per line" placeholderTextColor="#8B939C" />
                <View style={styles.filterActions}>
                    <Pressable style={styles.navy} onPress={filter}><Text style={styles.primaryText}>Filter</Text></Pressable>
                    <Pressable onPress={() => onOpen(`/niches/${slug}`)}><Text style={styles.recent}>Clear filters</Text></Pressable>
                </View>
            </View>
            <View style={styles.nicheList}>
                <Text style={styles.showing}>Showing {text(websites.from, '0')} – {text(websites.to, '0')} of {text(websites.total, text(data.total, '0'))} publishers</Text>
                {list.length === 0 ? <Text style={styles.empty}>No {name} websites match these filters.</Text> : cards ? list.map((row) => <NicheCard key={text(row.domain)} row={row} onOpen={onOpen} />) : <NicheTable rows={list} onOpen={onOpen} />}
                <View style={styles.wrap}>
                    {rows(websites.links).filter((link) => text(link.url) !== '').map((link) => (
                        <Pressable key={`${text(link.url)}-${text(link.label)}`} style={[styles.page, link.active === true && styles.pageOn]} onPress={() => onOpen(appPath(text(link.url)))}>
                            <Text style={[styles.pageText, link.active === true && styles.pageTextOn]}>{plain(text(link.label))}</Text>
                        </Pressable>
                    ))}
                </View>
            </View>
            <View style={styles.request}>
                <Text style={styles.h2}>Can't find the website you need?</Text>
                <Text style={styles.copy}>Tell us the domain or niche you're after and our outreach team will try to add it.</Text>
                <View style={styles.requestButton}><Text style={styles.primaryText}>Request a Website</Text></View>
                <View style={styles.requestOutline}><Text style={styles.outlineText}>Add Your Website as a Publisher</Text></View>
            </View>
            {rows(data.related).length > 0 ? (
                <View style={styles.related}>
                    <Text style={styles.h2}>Related niches</Text>
                    <View style={styles.wrap}>
                        {rows(data.related).map((item) => (
                            <Pressable key={text(item.slug)} style={styles.chip} onPress={() => onOpen(`/niches/${text(item.slug)}`)}>
                                <Text style={styles.chipText}>{text(item.name)}</Text>
                            </Pressable>
                        ))}
                    </View>
                </View>
            ) : null}
        </View>
    );
}

const metricMins = [20, 30, 40, 50, 60];
const trafficBands: [number, string][] = [[1000, '1K+'], [5000, '5K+'], [10000, '10K+'], [50000, '50K+']];
const countBands = [10, 50, 100, 500, 1000];
const spamBands: [number, string][] = [[5, 'SS up to 5'], [10, 'SS up to 10'], [20, 'SS up to 20'], [30, 'SS up to 30']];
const yesNo = [{ value: 'yes', label: 'Yes' }, { value: 'no', label: 'No' }];
const sortOptions = [
    { value: 'traffic_desc', label: 'Sort by Ahrefs traffic' },
    { value: 'semrush_desc', label: 'Sort by Semrush traffic' },
    { value: 'dr_desc', label: 'Sort by DR' },
    { value: 'da_desc', label: 'Sort by DA' },
    { value: 'as_desc', label: 'Sort by AS' },
    { value: 'price_asc', label: 'Price: low to high' },
    { value: 'price_desc', label: 'Price: high to low' },
    { value: 'newest', label: 'Newest' },
];

function NicheSelect({ label, value, options, onChange }: { label: string; value: string; options: { value: string; label: string }[]; onChange: (value: string) => void }) {
    const [open, setOpen] = useState(false);
    const current = options.find((option) => option.value === value)?.label ?? label;

    return (
        <>
            <Pressable style={styles.spec} onPress={() => setOpen(true)}>
                <Text style={styles.specText} numberOfLines={1}>{current}</Text>
            </Pressable>
            <Modal visible={open} transparent animationType="fade" onRequestClose={() => setOpen(false)}>
                <Pressable style={styles.modalBackdrop} onPress={() => setOpen(false)}>
                    <Pressable style={styles.modalSheet} onPress={() => undefined}>
                        <Text style={styles.modalTitle}>{label}</Text>
                        <ScrollView>
                            <Pressable onPress={() => { onChange(''); setOpen(false); }}><Text style={styles.modalOption}>{label}</Text></Pressable>
                            {options.map((option) => (
                                <Pressable key={option.value} onPress={() => { onChange(option.value); setOpen(false); }}>
                                    <Text style={[styles.modalOption, option.value === value && styles.modalOptionOn]}>{option.label}</Text>
                                </Pressable>
                            ))}
                        </ScrollView>
                    </Pressable>
                </Pressable>
            </Modal>
        </>
    );
}

function NicheTable({ rows: list, onOpen }: { rows: Record<string, unknown>[]; onOpen: (path: string) => void }) {
    return (
        <ScrollView horizontal nestedScrollEnabled showsHorizontalScrollIndicator>
            <View style={styles.table}>
                <View style={styles.tableHead}>
                    {['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic', 'Price', 'Buy now'].map((heading) => (
                        <Text key={heading} style={heading === 'Website URL' ? styles.thWide : styles.th}>{heading}</Text>
                    ))}
                </View>
                {list.map((row) => {
                    const countries = countryPairs(row.top_countries);

                    return (
                        <View key={text(row.domain)} style={styles.tr}>
                            <Text style={styles.tdWide}>{text(row.domain)}</Text>
                            <Text style={styles.td}>{text(row.as)}</Text>
                            <Text style={styles.td}>{text(row.da)}</Text>
                            <Text style={styles.td}>{text(row.dr)}</Text>
                            <Text style={styles.td}>{strings(row.niches).join(', ') || text(row.niche)}</Text>
                            <Text style={styles.td}>{text(row.language)}</Text>
                            <Text style={styles.td}>{compactCount(typeof row.traffic === 'number' ? row.traffic : undefined)}</Text>
                            <Text style={styles.td}>{compactCount(typeof row.keywords === 'number' ? row.keywords : undefined)}</Text>
                            <View style={styles.tdFlags}>
                                {countries.slice(0, 3).map((country) => (
                                    <View key={country[0]} style={styles.flagLine}>
                                        <Flag code={country[0].toUpperCase() === 'UK' ? 'gb' : country[0]} />
                                        <Text style={styles.flagPercent}>{Number(country[1] || 0).toFixed(1)}%</Text>
                                    </View>
                                ))}
                            </View>
                            <Text style={styles.td}>{compactCount(typeof row.semrush_traffic === 'number' ? row.semrush_traffic : undefined)}</Text>
                            <Text style={styles.td}>{row.price == null ? '' : `$${Number(row.price).toFixed(2)}`}</Text>
                            <Pressable style={styles.tdAction} onPress={() => onOpen(`/publish/${encodeURIComponent(text(row.domain))}`)}><Text style={styles.publish}>Publish</Text></Pressable>
                        </View>
                    );
                })}
            </View>
        </ScrollView>
    );
}

function NicheCard({ row, onOpen }: { row: Record<string, unknown>; onOpen: (path: string) => void }) {
    const countries = countryPairs(row.top_countries);

    return (
        <View style={styles.nicheCard}>
            <Text style={styles.cardTitle}>{text(row.domain)}</Text>
            <Text style={styles.muted}>{strings(row.niches).join(' · ')}</Text>
            <Text style={styles.muted}>{text(row.language)}</Text>
            <Text style={styles.price}>{row.price == null ? '' : money(row.price)}</Text>
            <Pressable onPress={() => onOpen(`/publish/${encodeURIComponent(text(row.domain))}`)}><Text style={styles.publish}>Publish</Text></Pressable>
            <View style={styles.metrics}>
                {([['AS', text(row.as)], ['DA', text(row.da)], ['DR', text(row.dr)], ['Ahrefs keywords', compactCount(typeof row.keywords === 'number' ? row.keywords : undefined)]] as [string, string][]).map(([label, value]) => (
                    <View key={label} style={styles.metric}><Text style={styles.metricLabel}>{label}</Text><Text style={styles.metricValue}>{value}</Text></View>
                ))}
            </View>
            <Text style={styles.metricLabel}>Ahrefs traffic {compactCount(typeof row.traffic === 'number' ? row.traffic : undefined)}</Text>
            <View style={styles.flagLine}>
                {countries.slice(0, 3).map((country) => (
                    <View key={country[0]} style={styles.flagLine}>
                        <Flag code={country[0].toUpperCase() === 'UK' ? 'gb' : country[0]} />
                        <Text style={styles.flagPercent}>{Number(country[1] || 0).toFixed(1)}%</Text>
                    </View>
                ))}
            </View>
            <Text style={styles.metricLabel}>Semrush traffic {compactCount(typeof row.semrush_traffic === 'number' ? row.semrush_traffic : undefined)}</Text>
        </View>
    );
}

function queryOf(path: string): URLSearchParams {
    return new URLSearchParams(path.includes('?') ? path.slice(path.indexOf('?') + 1) : '');
}

function nichePath(slug: string, params: URLSearchParams): string {
    const query = params.toString();

    return `/niches/${slug}${query === '' ? '' : `?${query}`}`;
}

function countryPairs(value: unknown): [string, number][] {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.flatMap((item) => {
        if (!Array.isArray(item) || typeof item[0] !== 'string') {
            return [];
        }

        return [[item[0], Number(item[1]) || 0] as [string, number]];
    });
}

function PackageIndex({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    return (
        <View>
            <Text style={styles.h1}>Choose Backlinks</Text>
            {rows(data.packages).map((item) => (
                <View key={text(item.slug)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.label)}</Text>
                    <Text style={styles.copy}>{text(item.summary)}</Text>
                    <Text style={styles.price}>{text(item.price_label, item.price == null ? text(item.size) : money(item.price))}</Text>
                    <Pressable style={styles.primary} onPress={() => onOpen(`/packages/${text(item.slug)}/websites`)}>
                        <Text style={styles.primaryText}>Choose Backlinks</Text>
                    </Pressable>
                </View>
            ))}
        </View>
    );
}

const packageSamples: Record<string, string> = {
    'display-starter': 'reports/starter-backlink-sample-report.xlsx',
    'display-growth': 'reports/growth-backlink-sample-report.xlsx',
    'display-professional': 'reports/professional-backlink-sample-report.xlsx',
    'display-enterprise': 'reports/enterprise-backlink-sample-report.xlsx',
};

function packageMoney(prefix: string, amount: number | null): string {
    if (amount === null) {
        return 'Price on request';
    }

    return `${prefix === '' ? '' : `${prefix} `}${money(amount)}`;
}

function PackageShow({ data, flash, path, onOpen }: { data: Record<string, unknown>; flash?: Record<string, unknown>; path: string; onOpen: (path: string) => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const pack = record(data.package);
    const story = record(pack.story);
    const slug = text(pack.slug);
    const types = rows(story.order_types);
    const fallbackLabel = slug === 'display-enterprise'
        ? 'Custom campaign (We review your goals and confirm the quotation.)'
        : 'Backlink campaign (We place the links and send the report.)';
    const choices = types.length > 0 ? types : [{ value: 'package', label: fallbackLabel }];
    const [orderType, setOrderType] = useState(text(choices[0]?.value, 'package'));
    const [quantity, setQuantity] = useState(1);
    const prefix = text(pack.price_prefix).trim();
    const numericPrice = Number(pack.price);
    const unit = Number.isFinite(numericPrice) ? numericPrice : null;
    const selected = data.selectedBacklinks;
    const showQuantity = prefix === '' && (selected == null || selected === '' || selected === 0);
    const qty = showQuantity ? Math.min(10, Math.max(1, quantity)) : 1;
    const title = text(pack.label, text(pack.name));
    const name = text(pack.name, title);
    const priceLabel = packageMoney(prefix, unit);
    const lineLabel = packageMoney(prefix, unit === null ? null : unit * qty);
    const sample = text(story.sample, packageSamples[slug] ?? '');
    const hero = text(story.hero, `${name} Backlink Package`);
    const badge = text(story.badge, text(story.count, text(pack.size)));
    const chips = strings(story.chips).length > 0
        ? strings(story.chips)
        : [`${text(story.count, text(pack.size))} backlinks`, text(story.delivery, 'Delivery confirmed with the order'), 'Report included', 'Order tracking'];
    const rules = strings(story.rules).length > 0
        ? strings(story.rules)
        : [
            'Our team keeps each placement relevant to the niche in the order.',
            'Anchor text is placed so it reads naturally on the page.',
            'We do not publish adult content, hate speech, or misleading claims.',
            text(story.delivery_note),
        ].filter((rule) => rule !== '');
    const note = selected != null && selected !== '' && selected !== 0
        ? `Next, choose ${text(selected)} websites for this package.`
        : text(story.note, 'Next, choose the websites for this package. Anchor text and target pages are added at checkout.');
    const status = text(flash?.shopping_status);
    const offer = text(story.layout) === 'offer';

    function choose() {
        const current = new URLSearchParams(path.split('?')[1]?.split('#')[0] ?? '');
        const params = new URLSearchParams();
        params.set('quantity', String(qty));
        params.set('order_type', orderType);

        if (selected != null && selected !== '' && selected !== 0) {
            params.set('backlinks', text(selected));
        }

        const requested = current.get('backlinks');

        if (requested) {
            params.set('backlinks', requested);
        }

        onOpen(`/packages/${slug}/websites?${params.toString()}`);
    }

    return (
        <View style={styles.packagePage}>
            {status !== '' ? <Text style={styles.packageNote}>{status}</Text> : null}
            <Text style={styles.packageCrumb}>
                <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                <Text> / </Text>
                <Text onPress={() => onOpen('/packages')}>Packages</Text>
                <Text> / </Text>
                <Text style={styles.crumbCurrent}>{name}</Text>
            </Text>
            <View style={styles.packageHero}>
                <View style={styles.packageBadge} />
                <Text style={styles.packageBadgeText}>{badge}</Text>
                <Text style={styles.packageHeroTitle}>{hero}</Text>
                {strings(story.gets).map((point) => <Text key={point} style={styles.packageHeroPoint}>• {point}</Text>)}
                {strings(story.marks).length > 0 ? (
                    <View style={styles.packageMarks}>
                        {strings(story.marks).map((mark) => <Text key={mark} style={styles.packageMark}>{mark}</Text>)}
                    </View>
                ) : null}
            </View>
            <View style={styles.packagePanel}>
                <Text style={styles.packageTitle}>{title}</Text>
                <View style={styles.packagePriceRow}>
                    <Text style={styles.packagePrice}>{priceLabel}</Text>
                    {text(story.category) !== '' ? <Text style={styles.packageCat}>{text(story.category)}</Text> : null}
                </View>
                <Text style={styles.packageLead}>{text(story.lead, text(pack.summary))}</Text>
                {text(story.support) !== '' ? <Text style={styles.packageLead}>{text(story.support)}</Text> : null}
                <View style={styles.packageChips}>
                    {chips.map((chip) => <Text key={chip} style={styles.packageChip}>{chip}</Text>)}
                </View>
                {sample !== '' ? (
                    <Pressable style={styles.packageSample} onPress={() => { void Linking.openURL(`${apiUrl}/${sample}`); }}>
                        <Text style={styles.packageSampleText}>View Sample Report</Text>
                    </Pressable>
                ) : null}
                {offer ? null : (
                    <View>
                        {text(story.headline) !== '' ? <Text style={styles.packageSection}>{text(story.headline)}</Text> : null}
                        <Text style={styles.packageSection}>What you get</Text>
                        {strings(story.gets).map((point) => <Text key={`get-${point}`} style={styles.packageList}>• {point}</Text>)}
                        <Text style={styles.packageSection}>Who is this package for?</Text>
                        {strings(story.audience).map((point) => <Text key={`who-${point}`} style={styles.packageList}>• {point}</Text>)}
                        <Text style={styles.packageSection}>How it works</Text>
                        {strings(story.steps).map((point, index) => <Text key={`step-${point}`} style={styles.packageList}>{index + 1}. {point}</Text>)}
                    </View>
                )}
                <View style={styles.packageRules}>
                    <Text style={styles.packageRulesTitle}>Publishing rules</Text>
                    {rules.map((rule) => <Text key={rule} style={styles.packageList}>• {rule}</Text>)}
                </View>
                <Text style={styles.packageLegend}>Order type *</Text>
                {choices.map((type) => (
                    <Pressable key={text(type.value)} style={styles.choice} onPress={() => setOrderType(text(type.value))}>
                        <Text style={styles.choiceMark}>{orderType === text(type.value) ? '●' : '○'}</Text>
                        <Text style={styles.choiceText}>{text(type.label)}</Text>
                    </Pressable>
                ))}
                {showQuantity ? (
                    <View style={styles.quantity}>
                        <Pressable style={styles.step} accessibilityLabel="Decrease quantity" onPress={() => setQuantity((value) => Math.max(1, value - 1))}><Text style={styles.stepText}>−</Text></Pressable>
                        <TextInput
                            style={styles.qtyInput}
                            keyboardType="number-pad"
                            value={String(qty)}
                            accessibilityLabel="Quantity"
                            onChangeText={(value) => setQuantity(Math.min(10, Math.max(1, Number(value.replace(/[^0-9]/g, '')) || 1)))}
                        />
                        <Pressable style={styles.step} accessibilityLabel="Increase quantity" onPress={() => setQuantity((value) => Math.min(10, value + 1))}><Text style={styles.stepText}>+</Text></Pressable>
                    </View>
                ) : null}
                <View style={styles.packageSummary}>
                    <View style={styles.subtotal}><Text style={styles.packageLine}>{qty}× {title}</Text><Text style={styles.packageLine}>{lineLabel}</Text></View>
                    <View style={styles.packageSubRow}><Text style={styles.packageSubLabel}>Subtotal</Text><Text style={styles.packageSubValue}>{lineLabel}</Text></View>
                </View>
                <Text style={styles.packageNote}>{note}</Text>
                <Pressable style={styles.packageCart} onPress={choose}><Text style={styles.primaryText}>Choose Backlinks</Text></Pressable>
                <View style={styles.packageTat}>
                    <Text style={styles.packageTatBadge}>Estimated delivery</Text>
                    <Text style={styles.packageTatText}>{text(story.delivery, 'Confirmed with the order')}{text(story.delivery_note) !== '' ? `. ${text(story.delivery_note)}` : ''}</Text>
                </View>
                <Text style={styles.packageHelp}><Text style={styles.cardTitle}>Questions? </Text><Text style={styles.packageHelpLink} onPress={() => onOpen('/contact')}>Contact us</Text></Text>
                {slug === 'display-enterprise' ? <Text style={styles.packageHelp}>Contact our team to discuss your custom backlink campaign.</Text> : null}
            </View>
        </View>
    );
}

function WebsitePick({ data, shell }: { data: Record<string, unknown>; shell: Shell | null }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const pack = record(data.package);
    const limit = typeof data.limit === 'number' ? data.limit : 0;
    const source = rows(data.rows);
    const [picked, setPicked] = useState<string[]>([]);
    const [filters, setFilters] = useState<BoardFilters>(emptyFilters);
    const [query, setQuery] = useState('');
    const [bulk, setBulk] = useState('');
    const [bulkOpen, setBulkOpen] = useState(false);
    const [sort, setSort] = useState('traffic');
    const [pageSize, setPageSize] = useState(25);
    const [page, setPage] = useState(1);
    const [pending, setPending] = useState(false);
    const [message, setMessage] = useState('');
    const [problem, setProblem] = useState('');
    const [saved, setSaved] = useState<string[]>(shell?.wishlistDomains ?? []);
    const languages = useMemo(() => unique(source.map((row) => text(row.language))), [source]);
    const countries = useMemo(() => unique(source.map((row) => countryCode(row))), [source]);
    const niches = useMemo(() => categoryNames(data.categories), [data.categories]);
    const visible = useMemo(() => {
        const terms = bulk.toLowerCase().split(/[\s,]+/).filter(Boolean);
        const matched = source.filter((row) => boardMatches(row, filters, query, terms));
        matched.sort((left, right) => boardSort(right, sort) - boardSort(left, sort));

        return matched;
    }, [source, filters, query, bulk, sort]);
    const pages = Math.max(1, Math.ceil(visible.length / pageSize));
    const current = Math.min(page, pages);
    const start = (current - 1) * pageSize;
    const shown = visible.slice(start, start + pageSize);
    const from = visible.length === 0 ? 0 : start + 1;
    const ready = limit > 0 ? picked.length === limit : picked.length > 0;
    const submitLabel = data.orderId ? 'Confirm websites' : 'Continue to Checkout';
    const countLabel = limit > 0 ? `${picked.length}/${limit}` : `${picked.length} selected`;

    function setFilter(name: keyof BoardFilters, value: string) {
        setFilters((currentFilters) => ({ ...currentFilters, [name]: value }));
        setPage(1);
    }

    function toggle(domain: string) {
        setPicked((currentPicked) => {
            if (currentPicked.includes(domain)) {
                return currentPicked.filter((item) => item !== domain);
            }

            if (limit > 0 && currentPicked.length >= limit) {
                return currentPicked;
            }

            return [...currentPicked, domain];
        });
    }

    async function wish(domain: string) {
        const on = saved.some((item) => item.toLowerCase() === domain.toLowerCase());
        const result = await apiRequest('/wishlist', { method: on ? 'DELETE' : 'POST', json: true, body: { domain } });

        if (hasErrors(result.body) || result.status >= 400) {
            setProblem(errorText(result.body));

            return;
        }

        setSaved((currentSaved) => on ? currentSaved.filter((item) => item.toLowerCase() !== domain.toLowerCase()) : [...currentSaved, domain.toLowerCase()]);
    }

    async function save() {
        setPending(true);
        setMessage('');
        setProblem('');
        const orderType = text(data.orderType);
        const body: Record<string, unknown> = {
            package_slug: text(pack.slug),
            quantity: typeof data.quantity === 'number' && data.quantity > 0 ? data.quantity : 1,
            order_type: orderType !== '' ? orderType : 'package',
            websites: picked,
        };

        if (typeof pack.package_id === 'number') {
            body.package_id = pack.package_id;
        }

        if (data.backlinks) {
            body.backlinks = data.backlinks;
        }

        if (data.orderId) {
            body.order = data.orderId;
        } else if (data.backlinks) {
            body.website_selection = true;
        }

        const result = await apiRequest(data.orderId ? `/packages/${text(pack.slug)}/websites` : '/cart', { method: 'POST', body });
        setPending(false);
        const status = record(result.body.flash).shopping_status;

        if (typeof result.body.redirect === 'string' && result.body.redirect !== '') {
            navigation.push('MenuPage', { path: appPath(result.body.redirect) });

            return;
        }

        if (hasErrors(result.body) || result.status >= 400) {
            setProblem(errorText(result.body));

            return;
        }

        setMessage(typeof status === 'string' && status !== '' ? status : 'The websites were saved.');
    }

    const bar = (
        <View style={styles.pickBar}>
            <Text style={styles.showing}>{countLabel}</Text>
            <Pressable style={[styles.pickSubmit, (!ready || pending) && styles.pickDisabled]} disabled={!ready || pending} onPress={() => { void save(); }}>
                <Text style={styles.primaryText}>{pending ? 'Saving…' : submitLabel}</Text>
            </Pressable>
        </View>
    );

    return (
        <View style={styles.packagePage}>
            <Text style={styles.packageCrumb}>
                <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                <Text> / </Text>
                <Text style={styles.crumbCurrent}>{text(data.choice, 'Packages')}</Text>
            </Text>
            <View style={styles.pickHead}>
                <Text style={styles.packageTitle}>Select Backlink Websites</Text>
                <Pressable style={styles.packageSample} onPress={() => { setSort('added'); setPage(1); }}>
                    <Text style={styles.packageSampleText}>SEE RECENTLY ADDED SITES</Text>
                </Pressable>
            </View>
            <Text style={styles.packageLead}>{text(data.lead)}</Text>
            {data.demo === true ? <Text style={styles.packageSampleText}>Sample data for preview. These figures belong to fictional .example publishers.</Text> : null}
            <Text style={styles.pickLive}>Backlink charts use the DataForSEO counts after a publisher is refreshed. Until then, the chart uses the stored sample count. AS, DA, DR, Ahrefs, Semrush and country bars stay on every row.</Text>
            {problem ? <Text style={styles.error}>{problem}</Text> : null}
            {message ? <Text style={styles.saved}>{message}</Text> : null}
            {bar}
            <View style={styles.specWrap}>
                <NicheSelect label="AS" value={filters.as} options={metricMins.map((value) => ({ value: String(value), label: `AS ${value}+` }))} onChange={(value) => setFilter('as', value)} />
                <NicheSelect label="DA" value={filters.da} options={metricMins.map((value) => ({ value: String(value), label: `DA ${value}+` }))} onChange={(value) => setFilter('da', value)} />
                <NicheSelect label="DR" value={filters.dr} options={[...metricMins, 70].map((value) => ({ value: String(value), label: `DR ${value}+` }))} onChange={(value) => setFilter('dr', value)} />
                <NicheSelect label="PA" value={filters.pa} options={metricMins.map((value) => ({ value: String(value), label: `PA ${value}+` }))} onChange={(value) => setFilter('pa', value)} />
                <NicheSelect label="TF" value={filters.tf} options={metricMins.map((value) => ({ value: String(value), label: `TF ${value}+` }))} onChange={(value) => setFilter('tf', value)} />
                <NicheSelect label="Niche" value={filters.niche} options={niches.map((name) => ({ value: name, label: name }))} onChange={(value) => setFilter('niche', value)} />
                <NicheSelect label="Price range" value={filters.price} options={[100, 250, 500, 1000].map((value) => ({ value: String(value), label: `Up to $${value.toLocaleString()}` }))} onChange={(value) => setFilter('price', value)} />
                <NicheSelect label="Ahrefs traffic" value={filters.traffic} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Ahrefs ${label}` }))} onChange={(value) => setFilter('traffic', value)} />
                <NicheSelect label="Semrush traffic" value={filters.semrush} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Semrush ${label}` }))} onChange={(value) => setFilter('semrush', value)} />
                <NicheSelect label="Language" value={filters.language} options={languages.map((language) => ({ value: language, label: language }))} onChange={(value) => setFilter('language', value)} />
                <NicheSelect label="Sports/Gaming allowed?" value={filters.sports} options={yesNo} onChange={(value) => setFilter('sports', value)} />
                <NicheSelect label="Pharmacy allowed?" value={filters.pharmacy} options={yesNo} onChange={(value) => setFilter('pharmacy', value)} />
                <NicheSelect label="Crypto allowed?" value={filters.crypto} options={yesNo} onChange={(value) => setFilter('crypto', value)} />
                <NicheSelect label="Google News" value={filters.news} options={yesNo} onChange={(value) => setFilter('news', value)} />
                <NicheSelect label="Marked as sponsored" value={filters.sponsored} options={yesNo} onChange={(value) => setFilter('sponsored', value)} />
                <NicheSelect label="Foreign lang. allowed?" value={filters.foreign} options={yesNo} onChange={(value) => setFilter('foreign', value)} />
                <NicheSelect label="No. of backlinks" value={filters.backlinks} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => setFilter('backlinks', value)} />
                <NicheSelect label="No. of referring domains" value={filters.referring} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => setFilter('referring', value)} />
                <NicheSelect label="Spam Score" value={filters.spam} options={spamBands.map(([value, label]) => ({ value: String(value), label }))} onChange={(value) => setFilter('spam', value)} />
                <NicheSelect label="Link type" value={filters.type} options={['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => ({ value: type, label: type }))} onChange={(value) => setFilter('type', value)} />
                <NicheSelect label="Link validity" value={filters.validity} options={['Permanent', '12 months', '6 months'].map((item) => ({ value: item, label: item }))} onChange={(value) => setFilter('validity', value)} />
                <NicheSelect label="Country" value={filters.country} options={countries.map((country) => ({ value: country, label: country }))} onChange={(value) => setFilter('country', value)} />
                <Pressable style={styles.packageSample} onPress={() => setBulkOpen((open) => !open)}><Text style={styles.packageSampleText}>BULK SEARCH</Text></Pressable>
            </View>
            {bulkOpen ? <TextInput style={[styles.field, styles.area]} multiline value={bulk} onChangeText={(value) => { setBulk(value); setPage(1); }} placeholder="One domain per line" placeholderTextColor="#8B939C" autoCapitalize="none" /> : null}
            <Text style={styles.showing}>Showing {from}–{start + shown.length} of {visible.length} publishers</Text>
            <TextInput style={styles.field} value={query} onChangeText={(value) => { setQuery(value); setPage(1); }} placeholder="Search by website or niche" placeholderTextColor="#8B939C" autoCapitalize="none" />
            <View style={styles.specWrap}>
                <NicheSelect label="25 per page" value={String(pageSize)} options={[10, 25, 50].map((size) => ({ value: String(size), label: `${size} per page` }))} onChange={(value) => { setPageSize(Number(value) || 25); setPage(1); }} />
                <NicheSelect label="Sort by traffic" value={sort} options={[{ value: 'traffic', label: 'Sort by traffic' }, { value: 'dr', label: 'Sort by DR' }, { value: 'da', label: 'Sort by DA' }, { value: 'price', label: 'Sort by price' }, { value: 'added', label: 'Sort by recently added' }]} onChange={(value) => { setSort(value || 'traffic'); setPage(1); }} />
            </View>
            {shown.length === 0 ? <Text style={styles.empty}>No publisher matches these filters.</Text> : (
                <ScrollView horizontal nestedScrollEnabled showsHorizontalScrollIndicator>
                    <View style={styles.table}>
                        <View style={styles.tableHead}>
                            {['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic', 'Backlinks', 'Referring domains', 'Price', 'Select'].map((heading) => (
                                <Text key={heading} style={heading === 'Website URL' ? styles.thWide : styles.th}>{heading}</Text>
                            ))}
                        </View>
                        {shown.map((row) => {
                            const domain = text(row.domain);
                            const chosen = picked.includes(domain);
                            const frozen = limit > 0 && !chosen && picked.length >= limit;
                            const wished = saved.some((item) => item.toLowerCase() === domain.toLowerCase());
                            const countriesOnRow = countryPairs(row.top_countries);

                            return (
                                <View key={domain} style={[styles.tr, frozen && styles.pickFrozen]}>
                                    <View style={styles.pickDomain}>
                                        <Text style={styles.pickDomainText}>{domain}</Text>
                                        <Pressable accessibilityLabel={wished ? `Remove ${domain} from wishlist` : `Save ${domain}`} onPress={() => { void wish(domain); }}>
                                            <Text style={wished ? styles.pickStarOn : styles.pickStar}>★</Text>
                                        </Pressable>
                                    </View>
                                    <Text style={styles.td}>{text(row.as)}</Text>
                                    <Text style={styles.td}>{text(row.da)}</Text>
                                    <Text style={styles.td}>{text(row.dr)}</Text>
                                    <Text style={styles.td}>{strings(row.niches).join(', ') || text(row.niche)}</Text>
                                    <Text style={styles.td}>{text(row.language)}</Text>
                                    <BoardSpark seed={`${domain}-ahrefs`} value={row.traffic} color="#E98989" />
                                    <Text style={styles.td}>{compactCount(metricNumber(row.keywords))}</Text>
                                    <View style={styles.tdFlags}>
                                        {countriesOnRow.slice(0, 3).map((country) => (
                                            <View key={country[0]} style={styles.flagLine}>
                                                <Flag code={country[0].toUpperCase() === 'UK' ? 'gb' : country[0]} />
                                                <Text style={styles.flagPercent}>{Number(country[1] || 0).toFixed(1)}%</Text>
                                            </View>
                                        ))}
                                    </View>
                                    <BoardSpark seed={`${domain}-semrush`} value={row.semrush_traffic} color="#7DCEA0" />
                                    <View style={styles.pickSpark}>
                                        <BoardSpark seed={`${domain}-backlinks`} value={row.backlinks} color="#3D7BC4" />
                                        {text(row.metrics_source) === 'dataforseo' ? <Text style={styles.pickSeo}>DataForSEO</Text> : null}
                                    </View>
                                    <Text style={styles.td}>{compactCount(metricNumber(row.referring_domains))}</Text>
                                    <Text style={styles.td}>{row.price == null ? '' : `$${Number(row.price).toFixed(2)}`}</Text>
                                    <Pressable style={styles.tdAction} disabled={frozen} accessibilityLabel={`Select ${domain}`} onPress={() => toggle(domain)}>
                                        <View style={[styles.pickBox, chosen && styles.pickBoxOn]}>{chosen ? <Text style={styles.pickTick}>✓</Text> : null}</View>
                                    </Pressable>
                                </View>
                            );
                        })}
                    </View>
                </ScrollView>
            )}
            {pages > 1 ? (
                <View style={styles.wrap}>
                    {Array.from({ length: pages }, (_, index) => index + 1).map((numberPage) => (
                        <Pressable key={numberPage} style={[styles.page, numberPage === current && styles.pageOn]} onPress={() => setPage(numberPage)}>
                            <Text style={[styles.pageText, numberPage === current && styles.pageTextOn]}>{numberPage}</Text>
                        </Pressable>
                    ))}
                </View>
            ) : null}
            {bar}
        </View>
    );
}

type BoardFilters = {
    as: string;
    da: string;
    dr: string;
    pa: string;
    tf: string;
    niche: string;
    price: string;
    traffic: string;
    semrush: string;
    language: string;
    sports: string;
    pharmacy: string;
    crypto: string;
    backlinks: string;
    referring: string;
    spam: string;
    type: string;
    validity: string;
    news: string;
    sponsored: string;
    country: string;
    foreign: string;
};

const emptyFilters: BoardFilters = {
    as: '', da: '', dr: '', pa: '', tf: '', niche: '', price: '', traffic: '', semrush: '', language: '',
    sports: '', pharmacy: '', crypto: '', backlinks: '', referring: '', spam: '', type: '', validity: '',
    news: '', sponsored: '', country: '', foreign: '',
};

function BoardSpark({ seed, value, color }: { seed: string; value: unknown; color: string }) {
    const raw = boardSeries(seed, value);
    const low = Math.min(...raw);
    const high = Math.max(...raw);
    const pad = Math.max((high - low) * 0.7, high * 0.08, 1);
    const span = Math.max(0.001, (high + pad) - (low - pad));
    const width = 72;
    const height = 22;
    const step = width / Math.max(1, raw.length - 1);
    const coordinates = raw.map((point, index) => {
        const sample = (point - (low - pad)) / span;

        return [Math.round(index * step * 10) / 10, Math.round((2 + (1 - sample) * 16) * 10) / 10];
    });
    const line = coordinates.map((point) => point.join(',')).join(' ');

    return (
        <View style={styles.pickSpark}>
            <Svg width={72} height={22} viewBox={`0 0 ${width} ${height}`}>
                <Polygon points={`0,${height} ${line} ${width},${height}`} fill={color} opacity={0.28} />
                <Polyline points={line} fill="none" stroke={color} strokeWidth={1.5} strokeLinejoin="round" strokeLinecap="round" />
            </Svg>
            <Text style={styles.pickSparkValue}>{compactCount(metricNumber(value))}</Text>
        </View>
    );
}

function boardSeries(seed: string, value: unknown): number[] {
    let state = BigInt(boardCrc(seed));
    const end = Number.isFinite(Number(value)) ? Math.max(1, Math.round(Number(value))) : null;
    const target = end ?? 100;
    const raw: number[] = [];

    for (let month = 0; month < 18; month += 1) {
        state = ((state * 1103515245n) + 12345n) & 0x7fffffffn;
        const current = Number(state);
        const wave = Math.sin((month / 2.2) + (current % 6)) * 0.045;
        const noise = ((current % 15) - 7) / 220;
        const drift = ((month / 17) - 0.55) * 0.06;
        raw.push(Math.max(1, Math.round(target * (1 + wave + noise + drift))));
    }

    if (end !== null) {
        raw[17] = end;
    }

    return raw;
}

function boardCrc(textValue: string): number {
    let crc = 0xffffffff;

    for (let index = 0; index < textValue.length; index += 1) {
        crc ^= textValue.charCodeAt(index);

        for (let bit = 0; bit < 8; bit += 1) {
            crc = (crc >>> 1) ^ ((crc & 1) ? 0xedb88320 : 0);
        }
    }

    return (crc ^ 0xffffffff) >>> 0;
}

function boardMatches(row: Record<string, unknown>, filters: BoardFilters, query: string, terms: string[]): boolean {
    const search = `${text(row.domain)} ${strings(row.niches).join(' ')} ${text(row.language)}`.toLowerCase();

    if (query.trim() !== '' && !search.includes(query.trim().toLowerCase())) {
        return false;
    }

    if (terms.length > 0 && !terms.some((term) => text(row.domain).toLowerCase().includes(term))) {
        return false;
    }

    const minimums: [keyof BoardFilters, string][] = [
        ['as', 'as'], ['da', 'da'], ['dr', 'dr'], ['pa', 'pa'], ['tf', 'tf'],
        ['traffic', 'traffic'], ['semrush', 'semrush_traffic'], ['backlinks', 'backlinks'], ['referring', 'referring_domains'],
    ];

    for (const [filter, field] of minimums) {
        if (filters[filter] !== '' && boardNumber(row[field]) < Number(filters[filter])) {
            return false;
        }
    }

    if (filters.price !== '' && boardNumber(row.price) > Number(filters.price)) {
        return false;
    }

    if (filters.spam !== '' && boardNumber(row.spam) > Number(filters.spam)) {
        return false;
    }

    const exact: [keyof BoardFilters, string][] = [
        ['language', text(row.language)],
        ['country', countryCode(row)],
        ['type', text(row.link_type)],
        ['validity', text(row.link_validity)],
        ['sports', flagOf(row.sports_allowed)],
        ['pharmacy', flagOf(row.pharmacy_allowed)],
        ['crypto', flagOf(row.crypto_allowed)],
        ['news', flagOf(row.google_news)],
        ['sponsored', flagOf(row.sponsored)],
        ['foreign', flagOf(row.foreign_language)],
    ];

    for (const [filter, value] of exact) {
        if (filters[filter] !== '' && value !== filters[filter]) {
            return false;
        }
    }

    if (filters.niche !== '' && !strings(row.niches).includes(filters.niche)) {
        return false;
    }

    return true;
}

function boardSort(row: Record<string, unknown>, key: string): number {
    if (key === 'added') {
        return typeof row.added_at === 'number' ? row.added_at : Date.parse(text(row.added_at)) || 0;
    }

    if (key === 'semrush') {
        return boardNumber(row.semrush_traffic);
    }

    return boardNumber(row[key]);
}

function boardNumber(value: unknown): number {
    const number = Number(value);

    return Number.isFinite(number) ? number : 0;
}

function metricNumber(value: unknown): number | undefined {
    const number = Number(value);

    return Number.isFinite(number) ? number : undefined;
}

function flagOf(value: unknown): string {
    if (typeof value !== 'boolean') {
        return '';
    }

    return value ? 'yes' : 'no';
}

function countryCode(row: Record<string, unknown>): string {
    const pairs = countryPairs(row.top_countries);

    return pairs.length > 0 ? pairs[0][0].toUpperCase() : '';
}

function categoryNames(value: unknown): string[] {
    if (Array.isArray(value)) {
        return unique(value.filter((item): item is string => typeof item === 'string'));
    }

    if (value && typeof value === 'object') {
        return unique(Object.keys(value));
    }

    return [];
}

function unique(values: string[]): string[] {
    return [...new Set(values.map((value) => value.trim()).filter(Boolean))].sort();
}

function SiteIndex({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();

    return (
        <View style={styles.packagePage}>
            <Text style={styles.packageCrumb}>
                <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                <Text> / </Text>
                <Text style={styles.crumbCurrent}>Sites</Text>
            </Text>
            <Text style={styles.packageTitle}>Sites</Text>
            <Text style={styles.packageLead}>Choose a publisher list. Each one opens the websites that match.</Text>
            {rows(data.sites).map((site) => (
                <Pressable key={text(site.slug)} style={styles.card} onPress={() => onOpen(`/sites/${text(site.slug)}`)}>
                    <Text style={styles.cardTitle}>{text(site.label)}</Text>
                    <Text style={styles.copy}>{text(site.about)}</Text>
                </Pressable>
            ))}
        </View>
    );
}

const siteIcons: Record<string, string> = {
    'premium-guest': '⭐',
    'guest-traffic': '📈',
    'niche-edit': '🔗',
    'by-country': '🌐',
    sidebar: '📌',
    favorites: '❤️',
    keywords: '🔍',
};

function SiteShow({ data, shell, onOpen }: { data: Record<string, unknown>; shell: Shell | null; onOpen: (path: string) => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { customer } = useAuth();
    const site = record(data.site);
    const kind = text(site.kind);
    const slug = text(site.slug);
    const marketplace = kind === 'niche-edit';
    const groups = rows(data.groups).map((group) => ({ country: text(group.country), rows: rows(group.rows) })).filter((group) => group.country !== '');
    const flat = groups.length > 0 ? groups.flatMap((group) => group.rows) : rows(data.rows);
    const [filters, setFilters] = useState<BoardFilters>({ ...emptyFilters, dr: text(data.defaultDr), niche: text(data.defaultNiche), type: text(data.defaultType) });
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState('traffic');
    const [pageSize, setPageSize] = useState(25);
    const [page, setPage] = useState(1);
    const [projectOpen, setProjectOpen] = useState(false);
    const [project, setProject] = useState('');
    const [projectNote, setProjectNote] = useState('');
    const [saved, setSaved] = useState<string[]>(shell?.wishlistDomains ?? []);
    const languages = useMemo(() => unique(flat.map((row) => text(row.language))), [flat]);
    const countries = useMemo(() => unique(flat.map((row) => countryCode(row))), [flat]);
    const niches = useMemo(() => {
        const names = categoryNames(data.categories);

        if (names.length > 0) {
            return names;
        }

        return unique(flat.flatMap((row) => strings(row.niches)).filter((name) => name !== 'Publisher'));
    }, [data.categories, flat]);
    const matched = useMemo(() => flat.filter((row) => boardMatches(row, filters, query, [])), [flat, filters, query]);
    const ordered = useMemo(() => {
        if (!marketplace) {
            return matched;
        }

        return [...matched].sort((left, right) => boardSort(right, sort) - boardSort(left, sort));
    }, [matched, marketplace, sort]);
    const pages = Math.max(1, Math.ceil(ordered.length / pageSize));
    const current = Math.min(page, pages);
    const start = (current - 1) * pageSize;
    const shown = marketplace ? ordered.slice(start, start + pageSize) : ordered;
    const grouped = groups.map((group) => ({
        country: group.country,
        rows: group.rows.filter((row) => boardMatches(row, filters, query, [])),
    })).filter((group) => group.rows.length > 0);

    function setFilter(name: keyof BoardFilters, value: string) {
        setFilters((currentFilters) => ({ ...currentFilters, [name]: value }));
        setPage(1);
    }

    async function wish(domain: string) {
        const on = saved.some((item) => item.toLowerCase() === domain.toLowerCase());
        const result = await apiRequest('/wishlist', { method: on ? 'DELETE' : 'POST', json: true, body: { domain } });

        if (!hasErrors(result.body) && result.status < 400) {
            setSaved((currentSaved) => on ? currentSaved.filter((item) => item.toLowerCase() !== domain.toLowerCase()) : [...currentSaved, domain.toLowerCase()]);
        }
    }

    return (
        <View style={styles.packagePage}>
            <Text style={styles.packageCrumb}>
                <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                <Text> / </Text>
                <Text onPress={() => onOpen('/sites')}>Sites</Text>
                <Text> / </Text>
                <Text style={styles.crumbCurrent}>{text(site.label)}</Text>
            </Text>
            <Text style={styles.packageTitle}>{text(site.label, 'Sites')}</Text>
            <Text style={styles.packageLead}>{text(site.about)}</Text>
            <Text style={styles.siteHeadline}>{text(site.headline)}</Text>
            <Text style={styles.packageLead}>{text(site.detail)}</Text>
            {strings(site.checks).map((check) => <Text key={check} style={styles.siteCheck}>• {check}</Text>)}
            {data.demo === true && kind !== 'favorites' ? <Text style={styles.siteDemo}>Sample data for preview. These figures belong to fictional .example publishers.</Text> : null}
            <Text style={styles.siteAlsoLabel}>Also check:</Text>
            {rows(data.sites).map((option) => {
                const currentSite = text(option.slug) === slug;

                return (
                    <Pressable key={text(option.slug)} style={styles.siteAlso} onPress={() => { if (!currentSite) { onOpen(`/sites/${text(option.slug)}`); } }}>
                        <Text style={styles.siteAlsoIcon}>{siteIcons[text(option.kind)] ?? '●'}</Text>
                        <Text style={currentSite ? styles.siteAlsoCurrent : styles.siteAlsoLink}>{text(option.label)}</Text>
                    </Pressable>
                );
            })}
            <View style={styles.siteCard}>
                <Text style={styles.siteHeadline}>Categories</Text>
                {kind === 'favorites' ? <Text style={styles.packageLead}>Categories appear after you save publishers. Sign in to open the websites on your account.</Text> : niches.length === 0 ? <Text style={styles.packageLead}>No niche is stored for the publishers on this list.</Text> : (
                    <View style={styles.wrap}>
                        {niches.map((name) => (
                            <Pressable key={name} style={styles.chip} onPress={() => onOpen(`/niches/${siteSlug(name)}`)}>
                                <Text style={styles.chipText}>{name}</Text>
                            </Pressable>
                        ))}
                    </View>
                )}
            </View>
            {kind === 'favorites' ? (
                <View style={styles.siteCard}>
                    <Text style={styles.packageLead}>Save a publisher with the heart on its listing. Those websites stay on your account.</Text>
                    <Pressable style={styles.packageCart} onPress={() => { if (customer) { onOpen('/wishlist'); } else { navigation.navigate('Login'); } }}>
                        <Text style={styles.primaryText}>Open favorite websites</Text>
                    </Pressable>
                </View>
            ) : flat.length === 0 ? <Text style={styles.empty}>No publisher is listed for this yet.</Text> : (
                <View style={styles.siteCard}>
                    {marketplace ? (
                        <>
                            <Pressable style={styles.packageSample} onPress={() => { setSort('added'); setPage(1); }}><Text style={styles.packageSampleText}>SEE RECENTLY ADDED SITES</Text></Pressable>
                            <View style={styles.pickBar}>
                                <View style={styles.flexText}>
                                    <Text style={styles.cardTitle}>Create project</Text>
                                    <Text style={styles.copy}>Create your first project to organize the websites you choose.</Text>
                                </View>
                                <Pressable style={styles.pickSubmit} onPress={() => setProjectOpen((open) => !open)}><Text style={styles.primaryText}>Create project</Text></Pressable>
                            </View>
                            {projectOpen ? (
                                <View>
                                    <TextInput style={styles.field} value={project} onChangeText={setProject} placeholder="Project name" placeholderTextColor="#8B939C" maxLength={80} />
                                    <Pressable style={styles.pickSubmit} onPress={() => { if (project.trim() !== '') { setProjectNote(`${project.trim()} is ready on this page. Choose publishers from the list below.`); } }}>
                                        <Text style={styles.primaryText}>Save project</Text>
                                    </Pressable>
                                    {projectNote !== '' ? <Text style={styles.saved}>{projectNote}</Text> : null}
                                </View>
                            ) : null}
                        </>
                    ) : null}
                    <SiteFilters filters={filters} languages={languages} countries={countries} niches={niches} onChange={setFilter} />
                    <TextInput style={styles.field} value={query} onChangeText={(value) => { setQuery(value); setPage(1); }} placeholder="Search by website or niche" placeholderTextColor="#8B939C" autoCapitalize="none" />
                    <Text style={styles.showing}>{marketplace ? `Showing ${ordered.length === 0 ? 0 : start + 1}–${start + shown.length} of ${ordered.length} publishers` : `Showing ${ordered.length} of ${flat.length} publishers`}</Text>
                    {marketplace ? (
                        <View style={styles.specWrap}>
                            <NicheSelect label="25 per page" value={String(pageSize)} options={[10, 25, 50].map((size) => ({ value: String(size), label: `${size} per page` }))} onChange={(value) => { setPageSize(Number(value) || 25); setPage(1); }} />
                            <NicheSelect label="Sort by traffic" value={sort} options={[{ value: 'traffic', label: 'Sort by traffic' }, { value: 'dr', label: 'Sort by DR' }, { value: 'da', label: 'Sort by DA' }, { value: 'price', label: 'Sort by price' }, { value: 'added', label: 'Sort by recently added' }]} onChange={(value) => { setSort(value || 'traffic'); setPage(1); }} />
                        </View>
                    ) : null}
                    {ordered.length === 0 ? <Text style={styles.empty}>No publisher matches these filters.</Text> : groups.length > 0 ? grouped.map((group) => (
                        <View key={group.country}>
                            <Text style={styles.siteHeadline}>{group.country}</Text>
                            <SiteTable rows={group.rows} saved={saved} onWish={wish} onOpen={onOpen} />
                        </View>
                    )) : <SiteTable rows={shown} saved={saved} onWish={wish} onOpen={onOpen} />}
                    {marketplace && pages > 1 ? (
                        <View style={styles.wrap}>
                            {Array.from({ length: pages }, (_, index) => index + 1).map((numberPage) => (
                                <Pressable key={numberPage} style={[styles.page, numberPage === current && styles.pageOn]} onPress={() => setPage(numberPage)}>
                                    <Text style={[styles.pageText, numberPage === current && styles.pageTextOn]}>{numberPage}</Text>
                                </Pressable>
                            ))}
                        </View>
                    ) : null}
                </View>
            )}
        </View>
    );
}

function SiteFilters({ filters, languages, countries, niches, onChange }: { filters: BoardFilters; languages: string[]; countries: string[]; niches: string[]; onChange: (name: keyof BoardFilters, value: string) => void }) {
    return (
        <View style={styles.specWrap}>
            <NicheSelect label="AS" value={filters.as} options={metricMins.map((value) => ({ value: String(value), label: `AS ${value}+` }))} onChange={(value) => onChange('as', value)} />
            <NicheSelect label="DA" value={filters.da} options={metricMins.map((value) => ({ value: String(value), label: `DA ${value}+` }))} onChange={(value) => onChange('da', value)} />
            <NicheSelect label="DR" value={filters.dr} options={[...metricMins, 70].map((value) => ({ value: String(value), label: `DR ${value}+` }))} onChange={(value) => onChange('dr', value)} />
            <NicheSelect label="PA" value={filters.pa} options={metricMins.map((value) => ({ value: String(value), label: `PA ${value}+` }))} onChange={(value) => onChange('pa', value)} />
            <NicheSelect label="TF" value={filters.tf} options={metricMins.map((value) => ({ value: String(value), label: `TF ${value}+` }))} onChange={(value) => onChange('tf', value)} />
            <NicheSelect label="Niche" value={filters.niche} options={niches.map((name) => ({ value: name, label: name }))} onChange={(value) => onChange('niche', value)} />
            <NicheSelect label="Price range" value={filters.price} options={[100, 250, 500, 1000].map((value) => ({ value: String(value), label: `Up to $${value.toLocaleString()}` }))} onChange={(value) => onChange('price', value)} />
            <NicheSelect label="Ahrefs traffic" value={filters.traffic} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Ahrefs ${label}` }))} onChange={(value) => onChange('traffic', value)} />
            <NicheSelect label="Semrush traffic" value={filters.semrush} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Semrush ${label}` }))} onChange={(value) => onChange('semrush', value)} />
            <NicheSelect label="Language" value={filters.language} options={languages.map((language) => ({ value: language, label: language }))} onChange={(value) => onChange('language', value)} />
            <NicheSelect label="Sports/Gaming allowed?" value={filters.sports} options={yesNo} onChange={(value) => onChange('sports', value)} />
            <NicheSelect label="Pharmacy allowed?" value={filters.pharmacy} options={yesNo} onChange={(value) => onChange('pharmacy', value)} />
            <NicheSelect label="Crypto allowed?" value={filters.crypto} options={yesNo} onChange={(value) => onChange('crypto', value)} />
            <NicheSelect label="No. of backlinks" value={filters.backlinks} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => onChange('backlinks', value)} />
            <NicheSelect label="No. of referring domains" value={filters.referring} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => onChange('referring', value)} />
            <NicheSelect label="Spam Score" value={filters.spam} options={spamBands.map(([value, label]) => ({ value: String(value), label }))} onChange={(value) => onChange('spam', value)} />
            <NicheSelect label="Link type" value={filters.type} options={['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => ({ value: type, label: type }))} onChange={(value) => onChange('type', value)} />
            <NicheSelect label="Link validity" value={filters.validity} options={['Permanent', '12 months', '6 months'].map((item) => ({ value: item, label: item }))} onChange={(value) => onChange('validity', value)} />
            <NicheSelect label="Google News" value={filters.news} options={yesNo} onChange={(value) => onChange('news', value)} />
            <NicheSelect label="Marked as sponsored" value={filters.sponsored} options={yesNo} onChange={(value) => onChange('sponsored', value)} />
            <NicheSelect label="Foreign lang. allowed?" value={filters.foreign} options={yesNo} onChange={(value) => onChange('foreign', value)} />
            <NicheSelect label="Country" value={filters.country} options={countries.map((country) => ({ value: country, label: country }))} onChange={(value) => onChange('country', value)} />
        </View>
    );
}

function SiteTable({ rows: list, saved, onWish, onOpen }: { rows: Record<string, unknown>[]; saved: string[]; onWish: (domain: string) => void; onOpen: (path: string) => void }) {
    return (
        <ScrollView horizontal nestedScrollEnabled showsHorizontalScrollIndicator>
            <View style={styles.table}>
                <View style={styles.tableHead}>
                    {['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic', 'Price', 'Buy now'].map((heading) => (
                        <Text key={heading} style={heading === 'Website URL' ? styles.thWide : styles.th}>{heading}</Text>
                    ))}
                </View>
                {list.map((row) => {
                    const domain = text(row.domain);
                    const wished = saved.some((item) => item.toLowerCase() === domain.toLowerCase());
                    const countriesOnRow = countryPairs(row.top_countries);

                    return (
                        <View key={domain} style={styles.tr}>
                            <View style={styles.pickDomain}>
                                <Text style={styles.pickDomainText}>{domain}</Text>
                                <Pressable accessibilityLabel={wished ? `Remove ${domain} from wishlist` : `Save ${domain}`} onPress={() => onWish(domain)}>
                                    <Text style={wished ? styles.pickStarOn : styles.pickStar}>★</Text>
                                </Pressable>
                            </View>
                            <Text style={styles.td}>{text(row.as)}</Text>
                            <Text style={styles.td}>{text(row.da)}</Text>
                            <Text style={styles.td}>{text(row.dr)}</Text>
                            <Text style={styles.td}>{strings(row.niches).join(', ') || text(row.niche)}</Text>
                            <Text style={styles.td}>{text(row.language)}</Text>
                            <BoardSpark seed={`${domain}-ahrefs`} value={row.traffic} color="#E98989" />
                            <Text style={styles.td}>{compactCount(metricNumber(row.keywords))}</Text>
                            <View style={styles.tdFlags}>
                                {countriesOnRow.slice(0, 3).map((country) => (
                                    <View key={country[0]} style={styles.flagLine}>
                                        <Flag code={country[0].toUpperCase() === 'UK' ? 'gb' : country[0]} />
                                        <Text style={styles.flagPercent}>{Number(country[1] || 0).toFixed(1)}%</Text>
                                    </View>
                                ))}
                            </View>
                            <BoardSpark seed={`${domain}-semrush`} value={row.semrush_traffic} color="#7DCEA0" />
                            <Text style={styles.td}>{row.price == null ? '' : `$${Number(row.price).toFixed(2)}`}</Text>
                            <Pressable style={styles.tdAction} onPress={() => onOpen(`/publish/${encodeURIComponent(domain)}`)}><Text style={styles.publish}>Publish</Text></Pressable>
                        </View>
                    );
                })}
            </View>
        </ScrollView>
    );
}

function siteSlug(value: string): string {
    return value.toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function ToolIndex({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    return (
        <View>
            <Text style={styles.h1}>Tools</Text>
            {rows(data.tools).map((tool) => (
                <Pressable key={text(tool.slug)} style={styles.card} onPress={() => onOpen(`/tools/${text(tool.slug)}`)}>
                    <Text style={styles.cardTitle}>{text(tool.label)}</Text>
                    <Text style={styles.copy}>{text(tool.summary)}</Text>
                </Pressable>
            ))}
        </View>
    );
}

function ToolShow({ data, onOpen }: { data: Record<string, unknown>; onOpen: (path: string) => void }) {
    const tool = record(data.tool);
    const kind = text(tool.kind);
    const [domain, setDomain] = useState(text(data.domain));
    const [compare, setCompare] = useState(text(data.compare));
    const [anchors, setAnchors] = useState(text(data.anchors));
    const site = record(data.site);
    const report = record(data.anchorReport);

    function run() {
        const params = new URLSearchParams();

        if (kind === 'anchor') {
            if (anchors.trim() !== '') {
                params.set('anchors', anchors);
            }
        } else if (domain.trim() !== '') {
            params.set('domain', domain.trim());
        }

        if (kind === 'competitor' && compare.trim() !== '') {
            params.set('compare', compare.trim());
        }

        const query = params.toString();
        onOpen(`/tools/${text(tool.slug)}${query === '' ? '' : `?${query}`}`);
    }

    return (
        <View>
            <Text style={styles.crumbs}><Text onPress={() => onOpen('/tools')}>Tools</Text> / {text(tool.label)}</Text>
            <Text style={styles.kicker}>SEO tools</Text>
            <Text style={styles.h1}>{text(tool.label)}</Text>
            <Text style={styles.copy}>{text(tool.about)}</Text>
            {strings(tool.checks).map((check) => <Text key={check} style={styles.point}>• {check}</Text>)}
            {kind === 'anchor' ? (
                <TextInput style={[styles.field, styles.area]} multiline value={anchors} onChangeText={setAnchors} placeholder="Anchors, one per line" placeholderTextColor="#8B939C" />
            ) : (
                <TextInput style={styles.field} value={domain} onChangeText={setDomain} placeholder="publisher-domain.example" placeholderTextColor="#8B939C" autoCapitalize="none" />
            )}
            {kind === 'competitor' ? <TextInput style={styles.field} value={compare} onChangeText={setCompare} placeholder="another-domain.example" placeholderTextColor="#8B939C" autoCapitalize="none" /> : null}
            <Pressable style={styles.primary} onPress={run}><Text style={styles.primaryText}>{kind === 'anchor' ? 'Analyze anchors' : kind === 'competitor' ? 'Compare' : 'Check website'}</Text></Pressable>
            <View style={styles.wrap}>
                {rows(data.suggestions).map((suggestion) => (
                    <Pressable key={text(suggestion.domain)} style={styles.chip} onPress={() => onOpen(`/tools/${text(tool.slug)}?domain=${encodeURIComponent(text(suggestion.domain))}`)}>
                        <Text style={styles.chipText}>{text(suggestion.domain)}</Text>
                    </Pressable>
                ))}
            </View>
            {data.checked === true && text(site.domain) !== '' ? (
                <View style={styles.card}>
                    <Text style={styles.cardTitle}>{text(site.domain)}</Text>
                    <Text style={styles.muted}>{strings(site.niches).join(' · ')}{text(site.language) ? ` · ${text(site.language)}` : ''}</Text>
                    <Text style={styles.copy}>AS {text(site.as)} · DR {text(site.dr)} · DA {text(site.da)} · Spam {text(site.spam)}</Text>
                    <Text style={styles.copy}>Traffic {compactCount(typeof site.traffic === 'number' ? site.traffic : undefined)} · Backlinks {compactCount(typeof site.backlinks === 'number' ? site.backlinks : undefined)}</Text>
                </View>
            ) : null}
            {data.checked === true && text(site.domain) === '' && kind !== 'anchor' && kind !== 'competitor' ? <Text style={styles.copy}>That website is not in the publisher list, so this checker has no stored metrics for it.</Text> : null}
            {kind === 'anchor' && text(report.total) !== '' ? (
                <View style={styles.card}>
                    <Text style={styles.cardTitle}>Anchor profile</Text>
                    <Text style={styles.copy}>Total {text(report.total)} · Unique {text(report.unique)} · Largest share {text(report.topShare)}%</Text>
                    <Text style={styles.copy}>{text(report.note)}</Text>
                </View>
            ) : null}
            <Text style={styles.h2}>Other tools</Text>
            {rows(data.tools).filter((item) => text(item.slug) !== text(tool.slug)).map((item) => (
                <Pressable key={text(item.slug)} style={styles.card} onPress={() => onOpen(`/tools/${text(item.slug)}`)}>
                    <Text style={styles.cardTitle}>{text(item.label)}</Text>
                    <Text style={styles.copy}>{text(item.summary)}</Text>
                </Pressable>
            ))}
        </View>
    );
}

function Wishlist({ data, onChanged }: { data: Record<string, unknown>; onChanged: () => void }) {
    const saved = rows(data.saved);
    const [pending, setPending] = useState('');

    async function remove(domain: string) {
        setPending(domain);
        await apiRequest('/wishlist', { method: 'DELETE', json: true, body: { domain } });
        setPending('');
        onChanged();
    }

    return (
        <View>
            <Text style={styles.h1}>Wishlist</Text>
            {saved.length === 0 ? <Text style={styles.copy}>No saved publishers yet.</Text> : saved.map((item) => (
                <View key={text(item.domain)} style={styles.card}>
                    <Text style={styles.cardTitle}>{text(item.domain)}</Text>
                    <Text style={styles.muted}>{[text(item.niche), text(item.dr) ? `DR ${text(item.dr)}` : ''].filter(Boolean).join(' · ')}</Text>
                    <Pressable onPress={() => { void remove(text(item.domain)); }}><Text style={styles.link}>{pending === text(item.domain) ? 'Removing…' : 'Remove'}</Text></Pressable>
                </View>
            ))}
        </View>
    );
}

function PublisherRow({ row }: { row: Record<string, unknown> }) {
    return (
        <View style={styles.card}>
            <Text style={styles.cardTitle}>{text(row.domain)}</Text>
            <Text style={styles.muted}>{strings(row.niches).join(', ')}{text(row.language) ? ` · ${text(row.language)}` : ''}</Text>
            <Text style={styles.copy}>AS {text(row.as)} · DA {text(row.da)} · DR {text(row.dr)} · {row.price == null ? '' : money(row.price)}</Text>
        </View>
    );
}

function rows(value: unknown): Record<string, unknown>[] {
    return Array.isArray(value) ? value.filter((item): item is Record<string, unknown> => Boolean(item) && typeof item === 'object' && !Array.isArray(item)) : [];
}

function record(value: unknown): Record<string, unknown> {
    return value && typeof value === 'object' && !Array.isArray(value) ? value as Record<string, unknown> : {};
}

function strings(value: unknown): string[] {
    return Array.isArray(value) ? value.filter((item): item is string => typeof item === 'string' && item !== '') : [];
}

function text(value: unknown, fallback = ''): string {
    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'number' && Number.isFinite(value)) {
        return String(value);
    }

    return fallback;
}

function plain(value: string): string {
    return value.replace(/<[^>]+>/g, '').replace('&laquo;', '«').replace('&raquo;', '»').trim();
}

function reloadPath(path: string): string {
    const [base, query = ''] = path.split('?');
    const params = new URLSearchParams(query.split('#')[0]);
    params.set('r', String(Date.now()));

    return `${base}?${params.toString()}`;
}

function appPath(target: string): string {
    try {
        const url = new URL(target);

        return `${url.pathname}${url.search}`;
    } catch {
        return target.startsWith('/') ? target : `/${target}`;
    }
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.canvas },
    fill: { flex: 1 },
    content: { padding: 20, paddingBottom: 40 },
    error: { marginTop: 12, backgroundColor: colors.dangerBg, color: colors.danger, borderRadius: 12, padding: 12, overflow: 'hidden' },
    h1: { color: colors.navy, fontSize: 32, lineHeight: 38, fontWeight: '800' },
    h2: { marginTop: 22, color: colors.navy, fontSize: 22, fontWeight: '800' },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    crumbs: { marginBottom: 10, color: colors.muted, fontSize: 14, fontWeight: '700' },
    copy: { marginTop: 8, color: colors.muted, fontSize: 15, lineHeight: 22 },
    card: { marginTop: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 14 },
    cardOn: { borderColor: colors.brand },
    cardTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    muted: { marginTop: 4, color: colors.muted, fontSize: 13, fontWeight: '700' },
    price: { marginTop: 8, color: colors.brandDark, fontSize: 18, fontWeight: '800' },
    pill: { marginTop: 10, alignSelf: 'flex-start', overflow: 'hidden', borderRadius: 999, backgroundColor: '#FFF1EC', color: colors.brandDark, fontWeight: '800', paddingHorizontal: 12, paddingVertical: 8 },
    showing: { marginTop: 16, color: colors.navy, fontSize: 15, fontWeight: '800' },
    point: { marginTop: 6, color: colors.navy, fontSize: 15, lineHeight: 22 },
    choice: { marginTop: 8, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    choiceMark: { color: colors.brandDark, fontSize: 16 },
    choiceText: { flex: 1, color: colors.navy, fontSize: 15, lineHeight: 22 },
    quantity: { marginTop: 14, flexDirection: 'row', alignItems: 'center', gap: 8 },
    step: { width: 44, height: 44, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, alignItems: 'center', justifyContent: 'center' },
    stepText: { color: colors.navy, fontSize: 20, fontWeight: '700' },
    qty: { minWidth: 32, color: colors.navy, fontSize: 18, fontWeight: '800', textAlign: 'center' },
    subtotal: { marginTop: 16, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    primary: { marginTop: 16, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    primaryText: { color: colors.white, fontSize: 16, fontWeight: '800' },
    wrap: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { borderRadius: 999, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 12, paddingVertical: 8 },
    chipText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    page: { borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 12, paddingVertical: 8 },
    pageOn: { backgroundColor: colors.brandDark, borderColor: colors.brandDark },
    pageText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    pageTextOn: { color: colors.white },
    field: { marginTop: 12, minHeight: 46, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, color: colors.navy, fontSize: 16, paddingHorizontal: 12 },
    area: { minHeight: 120, textAlignVertical: 'top', paddingTop: 12 },
    link: { marginTop: 8, color: colors.brandDark, fontSize: 14, fontWeight: '800' },
    saved: { marginTop: 12, color: '#17663A', fontSize: 14, fontWeight: '700' },
    nichePage: { marginHorizontal: -20 },
    nicheEnd: { marginHorizontal: -20, marginBottom: -40 },
    nicheHero: { backgroundColor: '#F2F2F2', padding: 20 },
    crumbCurrent: { color: colors.navy },
    intro: { marginTop: 14, color: colors.navy, fontSize: 17, lineHeight: 28 },
    recent: { marginTop: 14, color: colors.brandDark, fontSize: 15, fontWeight: '800' },
    nicheFilters: { backgroundColor: colors.white, borderBottomWidth: 1, borderBottomColor: colors.line, padding: 20 },
    specWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    spec: { minHeight: 44, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, justifyContent: 'center', paddingHorizontal: 12 },
    specText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    filterActions: { marginTop: 12, flexDirection: 'row', alignItems: 'center', gap: 16 },
    navy: { minHeight: 44, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    fieldLabel: { marginTop: 14, color: colors.navy, fontSize: 14, fontWeight: '800' },
    nicheList: { backgroundColor: '#F7F8F9', padding: 20 },
    empty: { marginTop: 12, borderRadius: 16, backgroundColor: colors.white, color: colors.navy, fontWeight: '800', textAlign: 'center', padding: 24 },
    table: { marginTop: 12, overflow: 'hidden', borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white },
    tableHead: { flexDirection: 'row', backgroundColor: colors.navy },
    th: { width: 120, color: colors.white, fontSize: 12, fontWeight: '800', textAlign: 'center', paddingVertical: 12, paddingHorizontal: 6 },
    thWide: { width: 180, color: colors.white, fontSize: 12, fontWeight: '800', textAlign: 'center', paddingVertical: 12, paddingHorizontal: 8 },
    tr: { flexDirection: 'row', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#B7C0C8', minHeight: 64 },
    td: { width: 120, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center', paddingHorizontal: 6 },
    tdWide: { width: 180, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center', paddingHorizontal: 8 },
    tdFlags: { width: 120, alignItems: 'center', gap: 4, paddingVertical: 8 },
    flagLine: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    flagPercent: { color: colors.navy, fontSize: 11, fontWeight: '700' },
    tdAction: { width: 110, alignItems: 'center' },
    publish: { overflow: 'hidden', borderRadius: 10, backgroundColor: colors.brandDark, color: colors.white, fontSize: 11, fontWeight: '800', letterSpacing: 0.4, textTransform: 'uppercase', paddingHorizontal: 12, lineHeight: 36 },
    nicheCard: { marginTop: 12, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 16 },
    metrics: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
    metric: { minWidth: 70 },
    metricLabel: { marginTop: 8, color: colors.muted, fontSize: 12, fontWeight: '700' },
    metricValue: { color: colors.navy, fontSize: 15, fontWeight: '800' },
    request: { backgroundColor: colors.white, padding: 20 },
    requestButton: { marginTop: 14, alignSelf: 'flex-start', minHeight: 44, borderRadius: 12, backgroundColor: colors.brandDark, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    requestOutline: { marginTop: 10, alignSelf: 'flex-start', minHeight: 44, borderRadius: 12, borderWidth: 1, borderColor: colors.navy, backgroundColor: colors.white, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    outlineText: { color: colors.navy, fontSize: 15, fontWeight: '800' },
    related: { backgroundColor: colors.white, paddingHorizontal: 20, paddingBottom: 28 },
    modalBackdrop: { flex: 1, justifyContent: 'flex-end', backgroundColor: 'rgba(21,32,41,0.45)' },
    modalSheet: { maxHeight: '70%', borderTopLeftRadius: 20, borderTopRightRadius: 20, backgroundColor: colors.white, padding: 16 },
    modalTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    modalOption: { color: colors.navy, fontSize: 15, fontWeight: '600', paddingVertical: 12 },
    modalOptionOn: { color: colors.brandDark, fontWeight: '800' },
    packagePage: { marginHorizontal: -20, marginTop: -20, backgroundColor: colors.canvas, paddingHorizontal: 20, paddingTop: 28, paddingBottom: 12 },
    packageCrumb: { marginBottom: 16, color: colors.muted, fontSize: 14, fontWeight: '700' },
    packageHero: { minHeight: 420, overflow: 'hidden', borderRadius: 16, backgroundColor: '#153A8A', paddingHorizontal: 28, paddingTop: 36, paddingBottom: 28 },
    packageBadge: { position: 'absolute', top: 0, right: 0, width: 0, height: 0, borderTopWidth: 168, borderLeftWidth: 168, borderTopColor: colors.brand, borderLeftColor: 'transparent' },
    packageBadgeText: { position: 'absolute', top: 28, right: 12, width: 92, color: colors.white, fontSize: 22, lineHeight: 24, fontWeight: '800', textAlign: 'right' },
    packageHeroTitle: { marginTop: 48, maxWidth: 280, color: colors.white, fontSize: 34, lineHeight: 34, fontWeight: '800', textTransform: 'uppercase' },
    packageHeroPoint: { marginTop: 10, paddingLeft: 16, color: colors.white, fontSize: 16, lineHeight: 22 },
    packageMarks: { marginTop: 28, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    packageMark: { overflow: 'hidden', borderRadius: 8, borderWidth: 1, borderColor: 'rgba(255,255,255,0.45)', backgroundColor: 'rgba(255,255,255,0.1)', color: colors.white, fontSize: 12, fontWeight: '800', letterSpacing: 0.4, textTransform: 'uppercase', paddingHorizontal: 10, paddingVertical: 8 },
    packagePanel: { marginTop: 20, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 22 },
    packageTitle: { color: colors.navy, fontSize: 32, lineHeight: 36, fontWeight: '800' },
    packagePriceRow: { marginTop: 16, flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', gap: 12 },
    packagePrice: { color: colors.navy, fontSize: 34, fontWeight: '800' },
    packageCat: { overflow: 'hidden', borderRadius: 8, borderWidth: 1, borderColor: colors.line, color: colors.muted, fontSize: 14, fontWeight: '700', paddingHorizontal: 10, paddingVertical: 8 },
    packageLead: { marginTop: 12, color: colors.navy, fontSize: 15, lineHeight: 24 },
    packageChips: { marginTop: 16, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    packageChip: { overflow: 'hidden', borderRadius: 8, borderWidth: 1, borderColor: colors.line, color: colors.navy, fontSize: 14, fontWeight: '700', paddingHorizontal: 10, paddingVertical: 8 },
    packageSample: { marginTop: 16, alignSelf: 'flex-start', minHeight: 44, borderRadius: 8, borderWidth: 1, borderColor: colors.brand, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    packageSampleText: { color: colors.brand, fontSize: 14, fontWeight: '800' },
    packageSection: { marginTop: 22, color: colors.navy, fontSize: 20, fontWeight: '800' },
    packageList: { marginTop: 6, color: colors.navy, fontSize: 15, lineHeight: 22 },
    packageRules: { marginTop: 18, borderRadius: 12, backgroundColor: '#EEF5FC', padding: 16 },
    packageRulesTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    packageLegend: { marginTop: 18, color: colors.navy, fontSize: 15, fontWeight: '800' },
    packageSummary: { marginTop: 16, borderTopWidth: 1, borderTopColor: colors.line, paddingTop: 12 },
    packageLine: { color: colors.navy, fontSize: 14 },
    packageSubRow: { marginTop: 12, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 1, borderTopColor: colors.line, paddingTop: 12 },
    packageSubLabel: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    packageSubValue: { color: colors.navy, fontSize: 22, fontWeight: '800' },
    packageNote: { marginTop: 14, color: colors.muted, fontSize: 14, lineHeight: 22 },
    packageCart: { marginTop: 4, minHeight: 52, borderRadius: 8, backgroundColor: colors.brandDark, alignItems: 'center', justifyContent: 'center' },
    packageTat: { marginTop: 12, flexDirection: 'row', alignItems: 'center', gap: 10, borderRadius: 10, backgroundColor: '#E7F5EC', padding: 12 },
    packageTatBadge: { overflow: 'hidden', borderRadius: 999, backgroundColor: '#17663A', color: colors.white, fontSize: 13, fontWeight: '800', paddingHorizontal: 8, paddingVertical: 4 },
    packageTatText: { flex: 1, color: colors.navy, fontSize: 14, lineHeight: 20 },
    packageHelp: { marginTop: 12, color: colors.navy, fontSize: 15 },
    packageHelpLink: { color: colors.brandDark, fontWeight: '700' },
    qtyInput: { width: 64, height: 44, borderRadius: 10, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, color: colors.navy, fontSize: 18, fontWeight: '800', textAlign: 'center' },
    pickHead: { marginTop: 8, gap: 12 },
    pickLive: { marginTop: 12, color: '#17663A', fontSize: 14, lineHeight: 20, fontWeight: '700' },
    pickBar: { marginTop: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 16 },
    pickSubmit: { minHeight: 44, borderRadius: 8, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16 },
    pickDisabled: { opacity: 0.45 },
    pickFrozen: { opacity: 0.45 },
    pickDomain: { width: 180, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingHorizontal: 8 },
    pickDomainText: { flexShrink: 1, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center' },
    pickStar: { color: '#8B939C', fontSize: 16 },
    pickStarOn: { color: colors.brand, fontSize: 16 },
    pickSpark: { width: 120, alignItems: 'center', justifyContent: 'center', paddingVertical: 6 },
    pickSparkValue: { color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center' },
    pickSeo: { color: '#17663A', fontSize: 10, fontWeight: '800' },
    pickBox: { width: 22, height: 22, borderRadius: 4, borderWidth: 2, borderColor: colors.navy, alignItems: 'center', justifyContent: 'center' },
    pickBoxOn: { backgroundColor: colors.navy },
    pickTick: { color: colors.white, fontSize: 14, fontWeight: '800' },
    flexText: { flex: 1 },
    siteHeadline: { marginTop: 22, color: colors.navy, fontSize: 22, lineHeight: 28, fontWeight: '800' },
    siteCheck: { marginTop: 8, color: colors.muted, fontSize: 16, lineHeight: 24 },
    siteDemo: { marginTop: 16, borderRadius: 12, backgroundColor: '#FFF1EC', color: colors.brandDark, fontSize: 14, fontWeight: '700', lineHeight: 20, padding: 12, overflow: 'hidden' },
    siteAlsoLabel: { marginTop: 22, color: colors.navy, fontSize: 16, fontWeight: '800' },
    siteAlso: { marginTop: 8, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    siteAlsoIcon: { width: 22, fontSize: 16, textAlign: 'center' },
    siteAlsoLink: { flex: 1, color: colors.brandDark, fontSize: 15, fontWeight: '700', lineHeight: 22 },
    siteAlsoCurrent: { flex: 1, color: colors.navy, fontSize: 15, fontWeight: '700', lineHeight: 22, textDecorationLine: 'underline' },
    siteCard: { marginTop: 18, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 16 },
});
