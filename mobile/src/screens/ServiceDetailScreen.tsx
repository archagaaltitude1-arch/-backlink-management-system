import { useNavigation, useRoute, type RouteProp } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useEffect, useRef, useState, type ComponentRef } from 'react';
import { ActivityIndicator, Linking, Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { apiRequest, errorText, hasErrors } from '../api/client';
import { compactCount, money } from '../api/home';
import type { Shell } from '../api/home';
import { loadServicePage, type PackageTab, type ServicePage, type ServicePublisher } from '../api/services';
import { useAuth } from '../context/AuthContext';
import { Flag } from '../flags';
import { Mark } from '../marks';
import type { RootStack } from '../navigation/types';
import { ServicePageEnd } from './service-end';
import { SiteBar } from './site-bar';
import { colors } from '../theme';

export function ServiceDetailScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const route = useRoute<RouteProp<RootStack, 'Service'>>();
    const { customer } = useAuth();
    const [page, setPage] = useState<ServicePage | null>(null);
    const [shell, setShell] = useState<Shell | null>(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);
    const [openFaq, setOpenFaq] = useState<string | null>(null);
    const scrollRef = useRef<ComponentRef<typeof ScrollView>>(null);
    const offerY = useRef(0);

    useEffect(() => {
        let active = true;
        setLoading(true);
        loadServicePage(route.params.slug)
            .then((result) => {
                if (active) {
                    setPage(result.page);
                    setShell(result.shell);
                    setError('');
                }
            })
            .catch((reason: unknown) => {
                if (active) {
                    setError(reason instanceof Error ? reason.message : 'This service page could not be loaded.');
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
    }, [route.params.slug]);

    useEffect(() => {
        scrollRef.current?.scrollTo({ y: 0, animated: false });
    }, [route.params.slug]);

    function scrollToOffer() {
        scrollRef.current?.scrollTo({ y: Math.max(0, offerY.current - 12), animated: true });
    }

    function startOrder() {
        if (!customer) {
            navigation.navigate('Register');

            return;
        }

        scrollToOffer();
    }

    return (
        <View style={styles.screen}>
            <SiteBar />
            <ScrollView
                ref={scrollRef}
                style={styles.fill}
                contentContainerStyle={styles.content}
        >
            {loading ? <ActivityIndicator color={colors.brand} style={styles.loader} /> : null}
            {error ? <Text style={styles.error}>{error}</Text> : null}
            {page ? (
                <>
                    <View style={styles.hero}>
                        <Text style={styles.crumbs}>
                            <Text onPress={() => navigation.navigate('Home')}>Home</Text>
                            <Text> / </Text>
                            <Text onPress={() => navigation.navigate('Services')}>Services</Text>
                            <Text> / </Text>
                            <Text style={styles.crumbCurrent}>{page.name}</Text>
                        </Text>
                        <View style={styles.pills}>
                            {page.pill ? <Text style={styles.pill}>{page.pill}</Text> : null}
                            {page.turnaround ? <Text style={styles.turnPill}>{page.turnaround}</Text> : null}
                        </View>
                        <Highlighted title={page.h1} highlight={page.h1Highlight} />
                        <Text style={styles.intro}>{page.intro}</Text>
                        {page.tagline ? <Text style={styles.tagline}>{page.tagline}</Text> : null}
                        <View style={styles.actions}>
                            <Pressable style={styles.primary} onPress={scrollToOffer}>
                                <Text style={styles.primaryText}>{page.buttons.primary}</Text>
                            </Pressable>
                            <Pressable style={styles.outline} onPress={scrollToOffer}>
                                <Text style={styles.outlineText}>{page.buttons.secondary}</Text>
                            </Pressable>
                        </View>
                        <View style={styles.trust}>
                            {page.trust.map((item) => (
                                <View key={item} style={styles.trustItem}>
                                    <View style={styles.check}><Mark name="check" size={14} /></View>
                                    <Text style={styles.trustText}>{item}</Text>
                                </View>
                            ))}
                        </View>
                        <View style={styles.preview}>
                            <Text style={styles.previewLabel}>Placement preview</Text>
                            <Text style={styles.previewCopy}>Teams that want lasting authority publish on real websites. A useful guide on this topic is <Text style={styles.strong}>your brand</Text>, written for readers who already care about the subject.</Text>
                            <View style={styles.previewChips}>
                                {['DR 60+', 'Dofollow', 'Verified'].map((chip) => <Text key={chip} style={styles.previewChip}>{chip}</Text>)}
                            </View>
                        </View>
                    </View>

                    <View style={styles.band}>
                        <Text style={styles.kicker}>Benefits</Text>
                        <Text style={styles.h2}>{page.whyTitle}</Text>
                        {page.whyIntro ? <RichCopy html={page.whyIntro} /> : null}
                        {page.why.map((reason, index) => (
                            <View key={reason[0]} style={styles.panel}>
                                <Text style={styles.step}>{index + 1}</Text>
                                <Text style={styles.panelTitle}>{reason[0]}</Text>
                                <Text style={styles.panelCopy}>{reason[1]}</Text>
                            </View>
                        ))}
                    </View>

                    <View style={styles.white}>
                        <Text style={styles.kicker}>Our key areas</Text>
                        <Text style={styles.h2}>{page.areasTitle}</Text>
                        {page.areasIntro ? <Text style={styles.intro}>{page.areasIntro}</Text> : null}
                        {page.areasPoints.map((point) => (
                            <View key={point} style={styles.trustItem}>
                                <View style={styles.check}><Mark name="check" size={14} /></View>
                                <Text style={styles.trustText}>{point}</Text>
                            </View>
                        ))}
                        {page.areas.map((area) => (
                            <View key={area.title} style={styles.panel}>
                                <View style={styles.iconBox}><Mark name={area.icon} /></View>
                                <Text style={styles.panelTitle}>{area.title}</Text>
                                <Text style={styles.panelCopy}>{area.text}</Text>
                            </View>
                        ))}
                        <View style={styles.chips}>
                            {page.niches.map((niche) => (
                                <View key={niche.name} style={styles.niche}>
                                    <Text style={styles.nicheText}>{niche.name}</Text>
                                    {niche.count != null ? <Text style={styles.nicheCount}>{niche.count}</Text> : null}
                                </View>
                            ))}
                        </View>
                    </View>

                    <View style={styles.band}>
                        <Text style={styles.kicker}>How it works</Text>
                        <Text style={styles.h2}>{page.stepsTitle}</Text>
                        {page.steps.map((step, index) => (
                            <View key={step[0]} style={styles.panel}>
                                <Text style={styles.step}>{index + 1}</Text>
                                <Text style={styles.panelTitle}>{step[0]}</Text>
                                <Text style={styles.panelCopy}>{step[1]}</Text>
                            </View>
                        ))}
                        <Pressable style={styles.primary} onPress={startOrder}>
                            <Text style={styles.primaryText}>{page.buttons.start}</Text>
                        </Pressable>
                    </View>

                    <View onLayout={(event) => { offerY.current = event.nativeEvent.layout.y; }}>
                        {page.slug === 'niche-edit' ? (
                            <PublisherList rows={page.publishers} categories={page.publisherCategories} demo={page.publishersDemo} sampleNote={page.sampleNote} onPublish={(domain) => navigation.navigate('MenuPage', { path: `/publish/${encodeURIComponent(domain)}` })} />
                        ) : (
                            <View style={styles.white}>
                                <Text style={styles.kicker}>Packages</Text>
                                <Text style={styles.h2}>{page.packagesTitle}</Text>
                                <Text style={styles.intro}>{page.packagesIntro}</Text>
                                {page.packageTabs.length > 0 ? <PackageTabs tabs={page.packageTabs} /> : page.tiers.map((tier) => (
                                    <View key={tier.name} style={[styles.panel, tier.popular && styles.popularCard]}>
                                        {tier.popular ? <Text style={styles.popular}>Popular</Text> : null}
                                        <View style={styles.iconBox}><Mark name={tier.icon} /></View>
                                        <Text style={styles.panelTitle}>{tier.name}</Text>
                                        <Text style={styles.tierPrice}>{tier.price}</Text>
                                        <Text style={styles.panelCopy}>{tier.summary || (tier.links === 'Custom' ? 'Custom links' : `${tier.links} links`)}</Text>
                                        {tier.features.map((feature) => (
                                            <View key={feature} style={styles.feature}>
                                                <View style={styles.check}><Mark name="check" size={14} /></View>
                                                <Text style={styles.featureText}>{feature}</Text>
                                            </View>
                                        ))}
                                    </View>
                                ))}
                                <Text style={styles.note}>Check out the publisher sites and individual pricing</Text>
                                <Text style={styles.note}>Need a custom or bulk order? Contact us</Text>
                            </View>
                        )}
                    </View>

                    <View style={styles.band}>
                        <Text style={styles.kicker}>FAQs</Text>
                        <Text style={styles.h2}>Questions about {page.name}</Text>
                        <Text style={styles.intro}>What is included, how long it takes, and how we confirm the link is live.</Text>
                        {page.faqs.map((faq) => {
                            const open = openFaq === faq[0];

                            return (
                                <Pressable key={faq[0]} style={styles.faq} onPress={() => setOpenFaq(open ? null : faq[0])}>
                                    <View style={styles.faqHead}>
                                        <Text style={styles.faqQuestion}>{faq[0]}</Text>
                                        <Text style={styles.faqMark}>{open ? '–' : '+'}</Text>
                                    </View>
                                    {open ? <Text style={styles.panelCopy}>{faq[1]}</Text> : null}
                                </Pressable>
                            );
                        })}
                        <Text style={styles.faqTitle}>Still have a question?</Text>
                        <Text style={styles.intro}>Tell us the niche, website, or package you want and we will help you choose.</Text>
                        <View style={styles.outline}><Text style={styles.outlineText}>{page.buttons.contact}</Text></View>
                    </View>

                    <View style={styles.cta}>
                        <Text style={styles.ctaKicker}>{page.name}</Text>
                        <Text style={styles.ctaTitle}>{page.ctaTitle}</Text>
                        <Text style={styles.ctaCopy}>{page.ctaText}</Text>
                        <Pressable style={styles.primary} onPress={startOrder}><Text style={styles.primaryText}>{page.buttons.start}</Text></Pressable>
                        <Pressable style={styles.whiteOutline} onPress={scrollToOffer}><Text style={styles.whiteOutlineText}>{page.buttons.secondary}</Text></Pressable>
                        <View style={styles.whiteOutline}><Text style={styles.whiteOutlineText}>{page.buttons.contact}</Text></View>
                    </View>
                    {shell ? <ServicePageEnd shell={shell} onBrowseCountry={(y) => scrollRef.current?.scrollTo({ y, animated: true })} /> : null}
                </>
            ) : null}
            </ScrollView>
        </View>
    );
}

function Highlighted({ title, highlight }: { title: string; highlight: string }) {
    const start = highlight ? title.indexOf(highlight) : -1;

    if (start < 0) {
        return <Text style={styles.h1}>{title}</Text>;
    }

    return (
        <Text style={styles.h1}>
            {title.slice(0, start)}
            <Text style={styles.accent}>{highlight}</Text>
            {title.slice(start + highlight.length)}
        </Text>
    );
}

function RichCopy({ html }: { html: string }) {
    const parts = html.split(/(<strong>[\s\S]*?<\/strong>)/g);

    return (
        <Text style={styles.intro}>
            {parts.map((part, index) => {
                const match = part.match(/^<strong>([\s\S]*?)<\/strong>$/);

                if (match) {
                    return <Text key={index} style={styles.strong}>{match[1]}</Text>;
                }

                return <Text key={index}>{part.replace(/<[^>]+>/g, '')}</Text>;
            })}
        </Text>
    );
}

function PackageTabs({ tabs }: { tabs: PackageTab[] }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const [active, setActive] = useState(tabs[0]?.key ?? '');
    const tab = tabs.find((item) => item.key === active) ?? tabs[0];
    const [quantity, setQuantity] = useState(String(tab?.min ?? 1));
    const [pending, setPending] = useState(false);
    const [message, setMessage] = useState('');
    const [problem, setProblem] = useState('');
    const selected = Math.min(tab.max, Math.max(tab.min, Number(quantity) || tab.min));
    const priced = quoteFor(tab, selected);
    const counts = tab.custom ? [] : Array.from({ length: tab.max - tab.min + 1 }, (_, index) => tab.min + index);

    async function continueToCheckout() {
        setPending(true);
        setMessage('');
        setProblem('');
        const result = await apiRequest('/cart', {
            method: 'POST',
            body: { package_slug: tab.slug, order_type: 'package', backlinks: selected },
        });
        setPending(false);

        if (hasErrors(result.body) || result.status >= 400) {
            setProblem(errorText(result.body));

            return;
        }

        const redirect = typeof result.body.redirect === 'string' ? result.body.redirect : '';

        if (redirect === '') {
            return;
        }

        if (result.body.external === true) {
            await Linking.openURL(redirect);

            return;
        }

        navigation.push('MenuPage', { path: pagePath(redirect) });
    }

    return (
        <View style={styles.packageCard}>
            <View style={styles.tabGrid}>
                {tabs.map((item) => (
                    <Pressable key={item.key} style={[styles.tab, item.key === tab.key && styles.tabOn]} onPress={() => { setActive(item.key); setQuantity(String(item.min)); setMessage(''); setProblem(''); }}>
                        <Text style={[styles.tabText, item.key === tab.key && styles.tabTextOn]}>{item.label}</Text>
                    </Pressable>
                ))}
            </View>
            <Text style={styles.packageTitle}>{tab.label} Package</Text>
            <Text style={styles.panelCopy}>{tab.summary}</Text>
            <Text style={styles.discount}>{tab.custom ? 'Custom pricing' : `${tab.discount}% package discount`}</Text>
            <Text style={styles.fieldLabel}>Backlinks</Text>
            {tab.custom ? (
                <TextInput
                    style={styles.field}
                    keyboardType="number-pad"
                    value={quantity}
                    onChangeText={setQuantity}
                />
            ) : (
                <ScrollView horizontal showsHorizontalScrollIndicator style={styles.countScroll}>
                    {counts.map((count) => (
                        <Pressable key={count} style={[styles.count, count === selected && styles.countOn]} onPress={() => setQuantity(String(count))}>
                            <Text style={[styles.countText, count === selected && styles.countTextOn]}>{count}</Text>
                        </Pressable>
                    ))}
                </ScrollView>
            )}
            <View style={styles.quote}>
                <QuoteLine label="Quantity" value={String(priced.quantity)} />
                {priced.custom ? <QuoteLine label="Pricing" value="Custom quotation" /> : (
                    <>
                        <QuoteLine label="Price per backlink" value={money(priced.unit)} />
                        <QuoteLine label="Subtotal" value={money(priced.subtotal)} />
                        <QuoteLine label={`Discount (${tab.discount}%)`} value={`-${money(priced.discount)}`} green />
                    </>
                )}
                <QuoteLine label={priced.custom ? 'Starting price' : 'Final price'} value={money(priced.final)} strong />
            </View>
            {problem ? <Text style={styles.problem}>{problem}</Text> : null}
            {message ? <Text style={styles.saved}>{message}</Text> : null}
            <Pressable style={styles.navyButton} disabled={pending} onPress={continueToCheckout}>
                <Text style={styles.primaryText}>{pending ? 'Saving…' : 'Continue to Checkout'}</Text>
            </Pressable>
        </View>
    );
}

function pagePath(target: string): string {
    const withoutHash = target.split('#')[0];

    try {
        const url = new URL(withoutHash);

        return `${url.pathname}${url.search}`;
    } catch {
        return withoutHash.startsWith('/') ? withoutHash : `/${withoutHash}`;
    }
}

function quoteFor(tab: PackageTab, quantity: number): { custom: boolean; quantity: number; unit: number; subtotal: number; discount: number; final: number } {
    if (tab.custom) {
        return { custom: true, quantity, unit: 0, subtotal: 0, discount: 0, final: Number(tab.quote.final || 0) };
    }

    const unit = Number(tab.quote.unit || 0);
    const rate = Number(tab.discount || tab.quote.discount_percent || 0) / 100;
    const subtotal = Math.round(unit * quantity * 100) / 100;
    const discount = Math.round(subtotal * rate * 100) / 100;

    return { custom: false, quantity, unit, subtotal, discount, final: Math.round((subtotal - discount) * 100) / 100 };
}

function QuoteLine({ label, value, green = false, strong = false }: { label: string; value: string; green?: boolean; strong?: boolean }) {
    return (
        <View style={[styles.quoteRow, strong && styles.quoteStrong]}>
            <Text style={[styles.quoteLabel, green && styles.green, strong && styles.quoteStrongText]}>{label}</Text>
            <Text style={[styles.quoteValue, green && styles.green, strong && styles.quoteStrongText]}>{value}</Text>
        </View>
    );
}

const metricMins = [20, 30, 40, 50, 60];
const trafficBands: [number, string][] = [[1000, '1K+'], [5000, '5K+'], [10000, '10K+'], [50000, '50K+']];
const countBands = [10, 50, 100, 500, 1000];
const spamBands: [number, string][] = [[5, 'SS up to 5'], [10, 'SS up to 10'], [20, 'SS up to 20'], [30, 'SS up to 30']];
const yesNo: [keyof PublisherFilters, string, 'sports_allowed' | 'pharmacy_allowed' | 'crypto_allowed' | 'google_news' | 'sponsored' | 'foreign_language'][] = [
    ['sports', 'Sports/Gaming allowed?', 'sports_allowed'],
    ['pharmacy', 'Pharmacy allowed?', 'pharmacy_allowed'],
    ['crypto', 'Crypto allowed?', 'crypto_allowed'],
    ['news', 'Google News', 'google_news'],
    ['sponsored', 'Marked as sponsored', 'sponsored'],
    ['foreign', 'Foreign lang. allowed?', 'foreign_language'],
];

type PublisherFilters = {
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
    news: string;
    sponsored: string;
    foreign: string;
    backlinks: string;
    referring: string;
    spam: string;
    type: string;
    validity: string;
    country: string;
};

const emptyFilters: PublisherFilters = {
    as: '', da: '', dr: '', pa: '', tf: '', niche: '', price: '', traffic: '', semrush: '', language: '',
    sports: '', pharmacy: '', crypto: '', news: '', sponsored: '', foreign: '',
    backlinks: '', referring: '', spam: '', type: '', validity: '', country: '',
};

function PublisherList({ rows, categories, demo, sampleNote, onPublish }: { rows: ServicePublisher[]; categories: string[]; demo: boolean; sampleNote: boolean; onPublish: (domain: string) => void }) {
    const [query, setQuery] = useState('');
    const [bulk, setBulk] = useState('');
    const [bulkOpen, setBulkOpen] = useState(false);
    const [filters, setFilters] = useState<PublisherFilters>(emptyFilters);
    const [sort, setSort] = useState<'traffic' | 'dr' | 'da' | 'price' | 'added'>('traffic');
    const [page, setPage] = useState(1);
    const [pageSize, setPageSize] = useState(25);
    const languages = [...new Set(rows.map((row) => row.language.trim()).filter(Boolean))].sort();
    const countries = [...new Set(rows.map((row) => String(row.top_countries[0]?.[0] || '').toUpperCase()).filter(Boolean))].sort();
    const filtered = rows
        .filter((row) => publisherMatches(row, filters, query, bulk))
        .sort((left, right) => publisherSort(right, sort) - publisherSort(left, sort));
    const pages = Math.max(1, Math.ceil(filtered.length / pageSize));
    const current = Math.min(page, pages);
    const start = (current - 1) * pageSize;
    const shown = filtered.slice(start, start + pageSize);
    const from = filtered.length === 0 ? 0 : start + 1;

    function setFilter(name: keyof PublisherFilters, value: string) {
        setFilters((current) => ({ ...current, [name]: value }));
        setPage(1);
    }

    return (
        <View style={styles.publisherBand}>
            <Text style={styles.kicker}>Websites</Text>
            <Text style={styles.h2}>Buy Niche Edits Backlinks</Text>
            <Text style={styles.intro}>Websites that allow link insertions. Choose a relevant publisher and we place your link in an existing article on that site.</Text>
            {demo ? <Text style={styles.sample}>Sample data for preview. These figures belong to fictional .example publishers.</Text> : null}
            {sampleNote ? <Text style={styles.sample}>Language, country, keywords, Semrush, referring domains and link details are sample figures until they are stored on each website.</Text> : null}
            <View style={styles.specWrap}>
                {([['as', 'AS'], ['da', 'DA'], ['dr', 'DR'], ['pa', 'PA'], ['tf', 'TF']] as const).map(([name, label]) => (
                    <SpecSelect
                        key={name}
                        label={label}
                        value={filters[name]}
                        options={(name === 'dr' ? [...metricMins, 70] : metricMins).map((value) => ({ value: String(value), label: `${label} ${value}+` }))}
                        onChange={(value) => setFilter(name, value)}
                    />
                ))}
                <SpecSelect label="Niche" value={filters.niche} options={categories.map((name) => ({ value: name, label: name }))} onChange={(value) => setFilter('niche', value)} />
                <SpecSelect label="Price range" value={filters.price} options={[100, 250, 500, 1000].map((value) => ({ value: String(value), label: `Up to $${value.toLocaleString()}` }))} onChange={(value) => setFilter('price', value)} />
                <SpecSelect label="Ahrefs traffic" value={filters.traffic} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Ahrefs ${label}` }))} onChange={(value) => setFilter('traffic', value)} />
                <SpecSelect label="Semrush traffic" value={filters.semrush} options={trafficBands.map(([value, label]) => ({ value: String(value), label: `Semrush ${label}` }))} onChange={(value) => setFilter('semrush', value)} />
                <SpecSelect label="Language" value={filters.language} options={languages.map((language) => ({ value: language, label: language }))} onChange={(value) => setFilter('language', value)} />
                {yesNo.map(([name, label]) => (
                    <SpecSelect key={name} label={label} value={filters[name]} options={[{ value: 'yes', label: 'Yes' }, { value: 'no', label: 'No' }]} onChange={(value) => setFilter(name, value)} />
                ))}
                <SpecSelect label="No. of backlinks" value={filters.backlinks} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => setFilter('backlinks', value)} />
                <SpecSelect label="No. of referring domains" value={filters.referring} options={countBands.map((value) => ({ value: String(value), label: `${value.toLocaleString()}+` }))} onChange={(value) => setFilter('referring', value)} />
                <SpecSelect label="Spam Score" value={filters.spam} options={spamBands.map(([value, label]) => ({ value: String(value), label }))} onChange={(value) => setFilter('spam', value)} />
                <SpecSelect label="Link type" value={filters.type} options={['Guest post', 'Niche edit', 'Contextual', 'Text link'].map((type) => ({ value: type, label: type }))} onChange={(value) => setFilter('type', value)} />
                <SpecSelect label="Link validity" value={filters.validity} options={['Permanent', '12 months', '6 months'].map((item) => ({ value: item, label: item }))} onChange={(value) => setFilter('validity', value)} />
                <SpecSelect label="Country" value={filters.country} options={countries.map((country) => ({ value: country, label: country }))} onChange={(value) => setFilter('country', value)} />
                <Pressable style={styles.spec} onPress={() => setBulkOpen((open) => !open)}><Text style={styles.specText}>Bulk search</Text></Pressable>
            </View>
            {bulkOpen ? <TextInput style={[styles.field, styles.bulk]} multiline value={bulk} placeholder="One domain per line" placeholderTextColor="#8B939C" onChangeText={(value) => { setBulk(value); setPage(1); }} /> : null}
            <Text style={styles.showing}>Showing {from}–{start + shown.length} of {filtered.length} publishers</Text>
            <TextInput style={styles.field} value={query} placeholder="Search by website or niche" placeholderTextColor="#8B939C" onChangeText={(value) => { setQuery(value); setPage(1); }} />
            <View style={styles.sorts}>
                {([['traffic', 'Traffic'], ['dr', 'DR'], ['da', 'DA'], ['price', 'Price'], ['added', 'Recent']] as const).map(([key, label]) => (
                    <Pressable key={key} style={[styles.count, sort === key && styles.countOn]} onPress={() => setSort(key)}>
                        <Text style={[styles.countText, sort === key && styles.countTextOn]}>{label}</Text>
                    </Pressable>
                ))}
                {[10, 25, 50].map((size) => (
                    <Pressable key={size} style={[styles.count, pageSize === size && styles.countOn]} onPress={() => { setPageSize(size); setPage(1); }}>
                        <Text style={[styles.countText, pageSize === size && styles.countTextOn]}>{size}</Text>
                    </Pressable>
                ))}
            </View>
            {shown.length === 0 ? <Text style={styles.panelCopy}>No publisher matches these filters.</Text> : (
                <ScrollView horizontal nestedScrollEnabled showsHorizontalScrollIndicator>
                    <View style={styles.table}>
                        <View style={styles.tableHead}>
                            {['Website URL', 'AS', 'DA', 'DR', 'Niche', 'Language', 'Ahrefs traffic', 'Ahrefs keywords', 'Countries', 'Semrush traffic', 'Link insertion price', 'Buy now'].map((heading) => (
                                <Text key={heading} style={heading === 'Website URL' ? styles.thWide : styles.th}>{heading}</Text>
                            ))}
                        </View>
                        {shown.map((row) => (
                            <View key={row.domain} style={styles.tr}>
                                <Text style={styles.tdWide}>{row.domain}</Text>
                                <Text style={styles.td}>{row.as ?? ''}</Text>
                                <Text style={styles.td}>{row.da ?? ''}</Text>
                                <Text style={styles.td}>{row.dr ?? ''}</Text>
                                <Text style={styles.td}>{row.niches.join(', ')}</Text>
                                <Text style={styles.td}>{row.language}</Text>
                                <Text style={styles.td}>{compactCount(row.traffic)}</Text>
                                <Text style={styles.td}>{compactCount(row.keywords)}</Text>
                                <View style={styles.tdFlags}>
                                    {row.top_countries.slice(0, 3).map((country) => (
                                        <View key={country[0]} style={styles.flagLine}>
                                            <Flag code={country[0].toUpperCase() === 'UK' ? 'gb' : country[0]} />
                                            <Text style={styles.flagPercent}>{Number(country[1] || 0).toFixed(1)}%</Text>
                                        </View>
                                    ))}
                                </View>
                                <Text style={styles.td}>{compactCount(row.semrush_traffic)}</Text>
                                <Text style={styles.td}>{row.price == null ? '' : `$${Number(row.price).toFixed(2)}`}</Text>
                                <Pressable style={styles.tdAction} onPress={() => onPublish(row.domain)}><Text style={styles.publish}>Publish</Text></Pressable>
                            </View>
                        ))}
                    </View>
                </ScrollView>
            )}
            {pages > 1 ? (
                <View style={styles.pages}>
                    {Array.from({ length: pages }, (_, index) => index + 1).map((numberPage) => (
                        <Pressable key={numberPage} style={[styles.count, numberPage === current && styles.countOn]} onPress={() => setPage(numberPage)}>
                            <Text style={[styles.countText, numberPage === current && styles.countTextOn]}>{numberPage}</Text>
                        </Pressable>
                    ))}
                </View>
            ) : null}
        </View>
    );
}

function SpecSelect({ label, value, options, onChange }: { label: string; value: string; options: { value: string; label: string }[]; onChange: (value: string) => void }) {
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

function publisherMatches(row: ServicePublisher, filters: PublisherFilters, query: string, bulk: string): boolean {
    const search = `${row.domain} ${row.niches.join(' ')} ${row.language}`.toLowerCase();
    const terms = bulk.toLowerCase().split(/[\s,]+/).filter(Boolean);

    if (query.trim() && !search.includes(query.trim().toLowerCase())) {
        return false;
    }

    if (terms.length > 0 && !terms.some((term) => row.domain.toLowerCase().includes(term))) {
        return false;
    }

    const minimums: [keyof PublisherFilters, keyof ServicePublisher][] = [
        ['as', 'as'], ['da', 'da'], ['dr', 'dr'], ['pa', 'pa'], ['tf', 'tf'],
        ['traffic', 'traffic'], ['semrush', 'semrush_traffic'], ['backlinks', 'backlinks'], ['referring', 'referring_domains'],
    ];

    for (const [filter, field] of minimums) {
        if (filters[filter] !== '' && metric(row, field) < Number(filters[filter])) {
            return false;
        }
    }

    if (filters.price !== '' && metric(row, 'price') > Number(filters.price)) {
        return false;
    }

    if (filters.spam !== '' && metric(row, 'spam') > Number(filters.spam)) {
        return false;
    }

    if (filters.language !== '' && row.language !== filters.language) {
        return false;
    }

    if (filters.country !== '' && String(row.top_countries[0]?.[0] || '').toUpperCase() !== filters.country) {
        return false;
    }

    if (filters.type !== '' && (row.link_type || '') !== filters.type) {
        return false;
    }

    if (filters.validity !== '' && (row.link_validity || '') !== filters.validity) {
        return false;
    }

    for (const [name, , field] of yesNo) {
        if (filters[name] !== '' && yesNoValue(row[field]) !== filters[name]) {
            return false;
        }
    }

    if (filters.niche !== '' && !row.niches.includes(filters.niche)) {
        return false;
    }

    return true;
}

function publisherSort(row: ServicePublisher, key: 'traffic' | 'dr' | 'da' | 'price' | 'added'): number {
    if (key === 'added') {
        return typeof row.added_at === 'number' ? row.added_at : Date.parse(String(row.added_at || '')) || 0;
    }

    return metric(row, key);
}

function metric(row: ServicePublisher, key: keyof ServicePublisher): number {
    const value = Number(row[key] ?? 0);

    return Number.isFinite(value) ? value : 0;
}

function yesNoValue(value: boolean | undefined): string {
    if (typeof value !== 'boolean') {
        return '';
    }

    return value ? 'yes' : 'no';
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.white },
    fill: { flex: 1 },
    content: { paddingBottom: 48 },
    loader: { marginTop: 24 },
    error: { margin: 16, backgroundColor: colors.dangerBg, color: colors.danger, borderRadius: 12, padding: 12, overflow: 'hidden' },
    hero: { padding: 20, borderBottomWidth: 1, borderBottomColor: colors.line },
    crumbs: { color: colors.muted, fontSize: 14, fontWeight: '700' },
    crumbCurrent: { color: colors.navy },
    pills: { marginTop: 14, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    pill: { overflow: 'hidden', borderRadius: 999, backgroundColor: '#FFF1EC', color: colors.brandDark, fontSize: 13, fontWeight: '800', paddingHorizontal: 14, paddingVertical: 8 },
    turnPill: { overflow: 'hidden', borderRadius: 999, borderWidth: 1, borderColor: colors.line, backgroundColor: '#F6F7F9', color: colors.navy, fontSize: 13, fontWeight: '800', paddingHorizontal: 14, paddingVertical: 8 },
    h1: { marginTop: 14, color: colors.navy, fontSize: 32, lineHeight: 38, fontWeight: '800' },
    accent: { color: colors.brand },
    intro: { marginTop: 12, color: colors.muted, fontSize: 16, lineHeight: 24 },
    strong: { color: colors.navy, fontWeight: '800' },
    tagline: { marginTop: 12, color: colors.navy, fontSize: 16, fontWeight: '800' },
    actions: { marginTop: 16, gap: 10 },
    primary: { marginTop: 14, alignSelf: 'flex-start', minHeight: 46, borderRadius: 999, backgroundColor: colors.brandDark, paddingHorizontal: 16, alignItems: 'center', justifyContent: 'center' },
    primaryText: { color: colors.white, fontSize: 15, fontWeight: '800' },
    outline: { alignSelf: 'flex-start', minHeight: 46, borderRadius: 999, borderWidth: 1, borderColor: colors.navy, paddingHorizontal: 16, alignItems: 'center', justifyContent: 'center' },
    outlineText: { color: colors.navy, fontSize: 15, fontWeight: '800' },
    trust: { marginTop: 16, gap: 8 },
    trustItem: { marginTop: 8, flexDirection: 'row', alignItems: 'center', gap: 8 },
    check: { width: 22, height: 22, borderRadius: 11, backgroundColor: '#FFF1EC', alignItems: 'center', justifyContent: 'center' },
    trustText: { flex: 1, color: colors.navy, fontSize: 14, fontWeight: '700' },
    preview: { marginTop: 18, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: '#F6F7F9', padding: 14 },
    previewLabel: { color: colors.muted, fontSize: 12, fontWeight: '800', letterSpacing: 0.6, textTransform: 'uppercase' },
    previewCopy: { marginTop: 10, borderRadius: 16, backgroundColor: colors.white, color: colors.muted, fontSize: 15, lineHeight: 24, padding: 14 },
    previewChips: { marginTop: 10, flexDirection: 'row', gap: 8 },
    previewChip: { flex: 1, overflow: 'hidden', borderRadius: 12, backgroundColor: colors.white, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center', paddingVertical: 12 },
    band: { backgroundColor: '#F6F7F9', padding: 20 },
    white: { backgroundColor: colors.white, padding: 20 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    h2: { marginTop: 10, color: colors.navy, fontSize: 28, lineHeight: 34, fontWeight: '800' },
    panel: { marginTop: 14, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 16 },
    popularCard: { borderColor: colors.brand, backgroundColor: '#FFF8F6' },
    popular: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 0.6, textTransform: 'uppercase' },
    step: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF1EC', color: colors.brandDark, fontSize: 14, fontWeight: '800', textAlign: 'center', lineHeight: 36, overflow: 'hidden' },
    panelTitle: { marginTop: 12, color: colors.navy, fontSize: 18, fontWeight: '800' },
    panelCopy: { marginTop: 8, color: colors.muted, fontSize: 15, lineHeight: 22 },
    iconBox: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#FFF1EC', alignItems: 'center', justifyContent: 'center' },
    chips: { marginTop: 14, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    niche: { flexDirection: 'row', alignItems: 'center', gap: 8, borderRadius: 999, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 14, paddingVertical: 8 },
    nicheText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    nicheCount: { overflow: 'hidden', borderRadius: 999, backgroundColor: '#F6F7F9', color: colors.muted, fontSize: 12, fontWeight: '700', paddingHorizontal: 8, paddingVertical: 2 },
    tierPrice: { marginTop: 8, color: colors.navy, fontSize: 32, fontWeight: '800' },
    feature: { marginTop: 10, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    featureText: { flex: 1, color: colors.navy, fontSize: 14, lineHeight: 20 },
    note: { marginTop: 14, color: colors.muted, fontSize: 15, lineHeight: 22, fontWeight: '800' },
    packageCard: { marginTop: 16, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 16 },
    tabGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, borderRadius: 16, backgroundColor: '#F4F7FA', padding: 6 },
    tab: { width: '48%', minHeight: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    tabOn: { backgroundColor: colors.navy },
    tabText: { color: colors.navy, fontSize: 14, fontWeight: '800' },
    tabTextOn: { color: colors.white },
    packageTitle: { marginTop: 16, color: colors.navy, fontSize: 24, fontWeight: '800' },
    discount: { marginTop: 10, alignSelf: 'flex-start', overflow: 'hidden', borderRadius: 999, backgroundColor: '#E7F5EC', color: '#17663A', fontSize: 14, fontWeight: '800', paddingHorizontal: 12, paddingVertical: 6 },
    fieldLabel: { marginTop: 14, color: colors.navy, fontSize: 14, fontWeight: '800' },
    field: { marginTop: 8, minHeight: 46, borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, color: colors.navy, fontSize: 16, fontWeight: '700', paddingHorizontal: 12 },
    countScroll: { marginTop: 8 },
    count: { marginRight: 8, minWidth: 42, minHeight: 40, borderRadius: 10, backgroundColor: colors.white, borderWidth: 1, borderColor: colors.line, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 10 },
    countOn: { backgroundColor: colors.navy, borderColor: colors.navy },
    countText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    countTextOn: { color: colors.white },
    quote: { marginTop: 14, borderRadius: 16, backgroundColor: '#F7FAF8', padding: 14, gap: 8 },
    quoteRow: { flexDirection: 'row', justifyContent: 'space-between', gap: 12 },
    quoteStrong: { marginTop: 6, borderTopWidth: 1, borderTopColor: '#D7E5DC', paddingTop: 10 },
    quoteLabel: { color: colors.navy, fontSize: 14 },
    quoteValue: { color: colors.navy, fontSize: 14, fontWeight: '800' },
    quoteStrongText: { fontSize: 18, fontWeight: '800' },
    green: { color: '#17663A', fontWeight: '800' },
    problem: { marginTop: 10, color: colors.brandDark, fontSize: 14, fontWeight: '700' },
    saved: { marginTop: 10, color: '#17663A', fontSize: 14, fontWeight: '700' },
    navyButton: { marginTop: 14, minHeight: 48, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center' },
    faq: { marginTop: 10, borderRadius: 16, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 14 },
    faqHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
    faqQuestion: { flex: 1, color: colors.navy, fontSize: 16, fontWeight: '800' },
    faqMark: { color: colors.brandDark, fontSize: 22 },
    faqTitle: { marginTop: 22, color: colors.navy, fontSize: 24, fontWeight: '800', textAlign: 'center' },
    cta: { borderTopWidth: 3, borderTopColor: colors.brand, backgroundColor: colors.navy, padding: 20 },
    ctaKicker: { color: '#FF8A55', fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    ctaTitle: { marginTop: 10, color: colors.white, fontSize: 28, lineHeight: 34, fontWeight: '800' },
    ctaCopy: { marginTop: 10, color: '#D5DCE3', fontSize: 16, lineHeight: 24 },
    whiteOutline: { marginTop: 10, alignSelf: 'flex-start', minHeight: 46, borderRadius: 999, borderWidth: 1, borderColor: colors.white, paddingHorizontal: 16, alignItems: 'center', justifyContent: 'center' },
    whiteOutlineText: { color: colors.white, fontSize: 15, fontWeight: '800' },
    specWrap: { marginTop: 14, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    spec: { minHeight: 44, maxWidth: '100%', borderRadius: 12, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, justifyContent: 'center', paddingHorizontal: 12 },
    specText: { color: colors.navy, fontSize: 14, fontWeight: '700' },
    bulk: { minHeight: 96, textAlignVertical: 'top' },
    modalBackdrop: { flex: 1, justifyContent: 'flex-end', backgroundColor: 'rgba(21,32,41,0.45)' },
    modalSheet: { maxHeight: '70%', borderTopLeftRadius: 20, borderTopRightRadius: 20, backgroundColor: colors.white, padding: 16 },
    modalTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    modalOption: { color: colors.navy, fontSize: 15, fontWeight: '600', paddingVertical: 12 },
    modalOptionOn: { color: colors.brandDark, fontWeight: '800' },
    publisherBand: { backgroundColor: '#F7F8FA', padding: 20 },
    sample: { marginTop: 12, color: colors.brandDark, fontSize: 14, fontWeight: '700' },
    showing: { marginTop: 14, color: colors.navy, fontSize: 15, fontWeight: '800' },
    sorts: { marginTop: 10, flexDirection: 'row', flexWrap: 'wrap' },
    table: { marginTop: 14, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, overflow: 'hidden' },
    tableHead: { flexDirection: 'row', backgroundColor: colors.navy },
    th: { width: 110, color: colors.white, fontSize: 12, fontWeight: '800', textAlign: 'center', paddingVertical: 12, paddingHorizontal: 6 },
    thWide: { width: 180, color: colors.white, fontSize: 12, fontWeight: '800', textAlign: 'center', paddingVertical: 12, paddingHorizontal: 8 },
    tr: { flexDirection: 'row', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#B7C0C8', minHeight: 64 },
    td: { width: 110, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center', paddingHorizontal: 6 },
    tdWide: { width: 180, color: colors.navy, fontSize: 13, fontWeight: '800', textAlign: 'center', paddingHorizontal: 8 },
    tdFlags: { width: 110, alignItems: 'center', gap: 4, paddingVertical: 8 },
    flagLine: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    flagPercent: { color: colors.navy, fontSize: 11, fontWeight: '700' },
    tdAction: { width: 110, alignItems: 'center' },
    publish: { overflow: 'hidden', borderRadius: 10, backgroundColor: colors.brandDark, color: colors.white, fontSize: 11, fontWeight: '800', letterSpacing: 0.4, textTransform: 'uppercase', paddingHorizontal: 12, lineHeight: 36 },
    pages: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap' },
});
