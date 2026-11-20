import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useRef, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import type { Shell } from '../api/home';
import { useHome } from '../hooks/useHome';
import { Flag } from '../flags';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';

const reviews: [string, string, string][] = [
    ['Amira Shah', 'Northline', 'Contextual links went onto checked publisher sites, and the live URL was in our report the same week.'],
    ['Leo Grant', 'Harbor Digital', 'Filtering by niche and DR made the shortlist simple. Every site we ordered was a real publisher.'],
    ['Priya Nair', 'Field & Co', 'Guest posts went live inside the delivery window, and the verified report was ready for our client.'],
    ['Jonah Ellis', 'Brightpath', 'Niche edits on older articles gave us a faster start than waiting for brand-new posts to index.'],
    ['Sofia Marquez', 'Lumen SEO', 'Traffic, spam score and price are on every listing, so we stopped guessing which sites were worth buying.'],
    ['Kenji Sato', 'Atlas Links', 'The white-label report was clean enough to send to our clients without rebuilding it.'],
];

const footerServices: [string, string][] = [
    ['Guest Post', 'guest-post'],
    ['Niche Edit', 'niche-edit'],
    ['Contextual Link', 'contextual-link'],
    ['Text Link', 'text-link'],
];

export function CountryAndFooter({ onBrowseCountry }: { onBrowseCountry: () => void }) {
    const { shell } = useHome();

    return (
        <View>
            <Countries countries={shell.countries} />
            <Footer niches={shell.niches} onBrowseCountry={onBrowseCountry} />
        </View>
    );
}

export function ServicePageEnd({ shell, onBrowseCountry }: { shell: Shell; onBrowseCountry: (y: number) => void }) {
    const origin = useRef(0);
    const countriesOffset = useRef(0);

    return (
        <View onLayout={(event) => { origin.current = event.nativeEvent.layout.y; }}>
            <Testimonials />
            <View onLayout={(event) => { countriesOffset.current = event.nativeEvent.layout.y; }}>
                <Countries countries={shell.countries} />
            </View>
            <Footer niches={shell.niches} onBrowseCountry={() => onBrowseCountry(origin.current + countriesOffset.current)} />
        </View>
    );
}

function Testimonials() {
    const [index, setIndex] = useState(0);
    const max = Math.max(0, reviews.length - 1);
    const shown = reviews.slice(index, index + 4);

    return (
        <View style={styles.reviews}>
            <Text style={styles.kicker}>Testimonials</Text>
            <Text style={styles.title}>What our customers are saying</Text>
            <Text style={styles.copy}>Campaign notes from teams who order placements through BacklinkPro.</Text>
            <View style={styles.pagerRow}>
                <Text style={styles.note}>{index + 1} of {reviews.length}</Text>
                <Pressable disabled={index <= 0} onPress={() => setIndex((value) => Math.max(0, value - 1))} style={styles.pager}><Text style={styles.pagerMark}>‹</Text></Pressable>
                <Pressable disabled={index >= max} onPress={() => setIndex((value) => Math.min(max, value + 1))} style={styles.pager}><Text style={styles.pagerMark}>›</Text></Pressable>
            </View>
            {shown.map((item) => (
                <View key={item[0]} style={styles.card}>
                    <Text style={styles.quoteMark}>“</Text>
                    <Text style={styles.quote}>{item[2]}</Text>
                    <Text style={styles.name}>{item[0]}</Text>
                    <Text style={styles.role}>{item[1]}</Text>
                </View>
            ))}
        </View>
    );
}

function Countries({ countries }: { countries: Shell['countries'] }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    return (
        <View style={styles.countries}>
            <Text style={styles.kicker}>Browse by country</Text>
            <Text style={styles.title}>Find Publisher Websites by Country</Text>
            <Text style={styles.copy}>Target a specific market? Pick a country to see publisher websites with traffic from that audience.</Text>
            <View style={styles.wrap}>
                {countries.map((country) => (
                    <Pressable key={country.code} style={styles.country} onPress={() => navigation.navigate('MenuPage', { path: `/publishers?country=${encodeURIComponent(country.code)}` })}>
                        <Flag code={country.flag} />
                        <Text style={styles.countryLabel}>{country.label}</Text>
                    </Pressable>
                ))}
            </View>
            <Pressable onPress={() => navigation.navigate('MenuPage', { path: '/publishers' })}><Text style={styles.viewAll}>View All Websites →</Text></Pressable>
        </View>
    );
}

function Footer({ niches, onBrowseCountry }: { niches: Shell['niches']; onBrowseCountry: () => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();

    return (
        <View style={styles.footer}>
            <Text style={styles.brand}>BacklinkPro</Text>
            <Text style={styles.footerCopy}>Backlinks on real publisher websites, placed and verified by our SEO team.</Text>
            <View style={styles.chips}>
                {['White-hat only', 'Every link verified', 'Delivery in 3–14 days'].map((item) => <Text key={item} style={styles.chip}>{item}</Text>)}
            </View>
            <Text style={styles.heading}>Services</Text>
            {footerServices.map(([label, slug]) => (
                <Pressable key={slug} onPress={() => navigation.navigate('Service', { slug })}>
                    <Text style={styles.link}>{label}</Text>
                </Pressable>
            ))}
            <Text style={styles.heading}>Explore</Text>
            {[['Publisher list', '/publishers'], ['Niches', '/niches'], ['Sites', '/sites'], ['Packages', '/packages'], ['SEO Tools', '/tools']].map(([label, path]) => (
                <Pressable key={path} onPress={() => navigation.navigate('MenuPage', { path })}><Text style={styles.link}>{label}</Text></Pressable>
            ))}
            <Pressable onPress={onBrowseCountry}><Text style={styles.link}>Browse by Country</Text></Pressable>
            <Text style={styles.heading}>Contact</Text>
            <Pressable onPress={() => navigation.navigate('MenuPage', { path: '/contact' })}><Text style={styles.contactButton}>Contact the team</Text></Pressable>
            <Text style={styles.footerCopy}>Questions about a campaign, a package, or a publisher.</Text>
            <Text style={styles.address}>Prime One Global (PVT) LTD. #146B, Goodshed Road, Thonikkal, Vavuniya, NP, Sri Lanka.</Text>
            <Text style={styles.heading}>Categories</Text>
            {niches.map((item) => (
                <Pressable key={item.label} onPress={() => navigation.navigate('MenuPage', { path: item.href || `/niches/${item.label.toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}` })}>
                    <Text style={styles.link}>{item.label}</Text>
                </Pressable>
            ))}
        </View>
    );
}

const styles = StyleSheet.create({
    reviews: { backgroundColor: '#F6F7F9', padding: 20 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textAlign: 'center', textTransform: 'uppercase' },
    title: { marginTop: 8, color: colors.navy, fontSize: 28, lineHeight: 34, fontWeight: '800', textAlign: 'center' },
    copy: { marginTop: 10, color: colors.muted, fontSize: 16, lineHeight: 24, textAlign: 'center' },
    pagerRow: { marginTop: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10 },
    note: { color: colors.muted, fontSize: 14, fontWeight: '700' },
    pager: { width: 44, height: 44, borderRadius: 22, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, alignItems: 'center', justifyContent: 'center' },
    pagerMark: { color: colors.navy, fontSize: 22, fontWeight: '700' },
    card: { marginTop: 14, minHeight: 180, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 18 },
    quoteMark: { color: colors.brand, fontSize: 36, lineHeight: 40 },
    quote: { color: colors.navy, fontSize: 16, lineHeight: 26 },
    name: { marginTop: 16, color: colors.navy, fontSize: 16, fontWeight: '800', textAlign: 'center' },
    role: { marginTop: 4, color: colors.muted, fontSize: 14, fontWeight: '600', textAlign: 'center' },
    countries: { backgroundColor: colors.white, padding: 20 },
    wrap: { marginTop: 16, flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'center', gap: 10 },
    country: { flexDirection: 'row', alignItems: 'center', gap: 8, borderRadius: 12, borderWidth: 1, borderColor: '#E4E7EB', backgroundColor: colors.white, paddingHorizontal: 12, paddingVertical: 10 },
    countryLabel: { color: colors.navy, fontSize: 14, fontWeight: '600' },
    viewAll: { marginTop: 16, color: colors.brandDark, fontSize: 16, fontWeight: '800', textAlign: 'center' },
    footer: { borderTopWidth: 4, borderTopColor: colors.brand, backgroundColor: colors.navy, padding: 20, paddingBottom: 36 },
    brand: { color: colors.white, fontSize: 22, fontWeight: '800' },
    footerCopy: { marginTop: 12, color: '#D5DCE3', fontSize: 15, lineHeight: 24 },
    chips: { marginTop: 12, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { overflow: 'hidden', borderRadius: 999, backgroundColor: 'rgba(255,255,255,0.1)', color: '#F2F4F6', fontSize: 13, fontWeight: '700', paddingHorizontal: 12, paddingVertical: 6 },
    heading: { marginTop: 22, color: colors.brand, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    link: { marginTop: 8, color: '#D5DCE3', fontSize: 15, fontWeight: '700', lineHeight: 28 },
    contactButton: { marginTop: 12, alignSelf: 'flex-start', overflow: 'hidden', borderRadius: 999, backgroundColor: colors.brand, color: colors.white, fontSize: 15, fontWeight: '800', paddingHorizontal: 16, paddingVertical: 12 },
    address: { marginTop: 12, color: '#D5DCE3', fontSize: 14, lineHeight: 22 },
});
