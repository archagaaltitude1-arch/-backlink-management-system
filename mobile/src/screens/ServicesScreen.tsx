import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useRef, useEffect, useState, type ComponentRef } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import type { Shell } from '../api/home';
import { loadServicesOverview, type ServicesOverview } from '../api/services';
import { Mark } from '../marks';
import { ServicePageEnd } from './service-end';
import { SiteBar } from './site-bar';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';

export function ServicesScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const [page, setPage] = useState<ServicesOverview | null>(null);
    const [shell, setShell] = useState<Shell | null>(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);
    const scrollRef = useRef<ComponentRef<typeof ScrollView>>(null);

    useEffect(() => {
        let active = true;
        setLoading(true);
        loadServicesOverview()
            .then((result) => {
                if (active) {
                    setPage(result.overview);
                    setShell(result.shell);
                    setError('');
                }
            })
            .catch((reason: unknown) => {
                if (active) {
                    setError(reason instanceof Error ? reason.message : 'The services page could not be loaded.');
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
    }, []);

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
                        <Text style={styles.kicker}>Services</Text>
                        <Text style={styles.h1}>{page.h1}</Text>
                        <Text style={styles.intro}>{page.intro}</Text>
                        <View style={styles.priceList}>
                            {page.cards.map((card, index) => (
                                <View key={card.slug} style={[styles.priceRow, index === 0 && styles.priceRowFirst]}>
                                    <Text style={styles.priceName}>{card.name}</Text>
                                    <Text style={styles.priceValue}>{card.price}</Text>
                                </View>
                            ))}
                        </View>
                    </View>
                    <View style={styles.list}>
                        {page.cards.map((card) => (
                            <View key={card.slug} style={styles.card}>
                                <View style={styles.rowBetween}>
                                    <View style={styles.iconBox}><Mark name={card.icon} /></View>
                                    <Text style={styles.cardPrice}>{card.price}</Text>
                                </View>
                                <Text style={styles.cardTitle}>{card.name}</Text>
                                <Text style={styles.cardCopy}>{card.menu}</Text>
                                <Text style={styles.turnaround}>{card.turnaround}</Text>
                                <Pressable style={styles.outline} onPress={() => navigation.navigate('Service', { slug: card.slug })}>
                                    <Text style={styles.outlineText}>View {card.name}</Text>
                                </Pressable>
                            </View>
                        ))}
                    </View>
                    <View style={styles.compare}>
                        <Text style={styles.h2}>Compare all services</Text>
                        <Text style={styles.intro}>Content, placement, turnaround and starting price for each link type.</Text>
                        <Text style={styles.scrollHint}>Scroll sideways to compare every column.</Text>
                        <ScrollView horizontal nestedScrollEnabled showsHorizontalScrollIndicator style={styles.tableScroll}>
                            <View>
                                <View style={styles.headRow}>
                                    <Text style={styles.headLabel} />
                                    {page.cards.map((card) => <Text key={card.slug} style={styles.headCell}>{card.name}</Text>)}
                                </View>
                                {page.compareRows.map((row) => (
                                    <View key={row.label} style={[styles.bodyRow, row.label === 'Price from' && styles.priceFrom]}>
                                        <Text style={[styles.rowLabel, row.label === 'Price from' && styles.priceFromText]}>{row.label}</Text>
                                        {row.cells.map((cell, index) => (
                                            <Text key={`${row.label}-${index}`} style={[styles.bodyCell, row.label === 'Price from' && styles.priceFromText]}>{cell}</Text>
                                        ))}
                                    </View>
                                ))}
                            </View>
                        </ScrollView>
                    </View>
                    {shell ? <ServicePageEnd shell={shell} onBrowseCountry={(y) => scrollRef.current?.scrollTo({ y, animated: true })} /> : null}
                </>
            ) : null}
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.white },
    fill: { flex: 1 },
    content: { paddingBottom: 40 },
    loader: { marginTop: 24 },
    error: { margin: 16, backgroundColor: colors.dangerBg, color: colors.danger, borderRadius: 12, padding: 12, overflow: 'hidden' },
    hero: { padding: 20, borderBottomWidth: 1, borderBottomColor: colors.line, backgroundColor: colors.white },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    h1: { marginTop: 12, color: colors.navy, fontSize: 32, lineHeight: 38, fontWeight: '800' },
    intro: { marginTop: 12, color: colors.muted, fontSize: 16, lineHeight: 24 },
    priceList: { marginTop: 18, overflow: 'hidden', borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: '#F6F7F9' },
    priceRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12, borderTopWidth: 1, borderTopColor: colors.line, paddingHorizontal: 16, paddingVertical: 14 },
    priceRowFirst: { borderTopWidth: 0 },
    priceName: { flex: 1, color: colors.navy, fontSize: 14, fontWeight: '700' },
    priceValue: { color: colors.brandDark, fontSize: 14, fontWeight: '800' },
    list: { backgroundColor: '#F6F7F9', padding: 16, gap: 14 },
    card: { borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white, padding: 20 },
    rowBetween: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 },
    iconBox: { width: 48, height: 48, borderRadius: 14, backgroundColor: '#FFF1EC', alignItems: 'center', justifyContent: 'center' },
    cardPrice: { color: colors.brandDark, fontSize: 15, fontWeight: '800' },
    cardTitle: { marginTop: 16, color: colors.navy, fontSize: 22, fontWeight: '800' },
    cardCopy: { marginTop: 8, color: colors.muted, fontSize: 15, lineHeight: 22 },
    turnaround: { marginTop: 14, color: colors.navy, fontSize: 13, fontWeight: '700' },
    outline: { marginTop: 18, alignSelf: 'flex-start', minHeight: 44, borderRadius: 999, borderWidth: 1, borderColor: colors.navy, paddingHorizontal: 16, alignItems: 'center', justifyContent: 'center' },
    outlineText: { color: colors.navy, fontSize: 14, fontWeight: '800' },
    compare: { backgroundColor: colors.white, padding: 20 },
    h2: { color: colors.navy, fontSize: 28, fontWeight: '800' },
    scrollHint: { marginTop: 12, color: colors.muted, fontSize: 13, fontWeight: '700' },
    tableScroll: { marginTop: 14, borderRadius: 20, borderWidth: 1, borderColor: colors.line, backgroundColor: colors.white },
    headRow: { flexDirection: 'row', backgroundColor: '#F6F7F9' },
    headLabel: { width: 132 },
    headCell: { width: 150, color: colors.navy, fontSize: 15, fontWeight: '800', paddingHorizontal: 12, paddingVertical: 14 },
    bodyRow: { flexDirection: 'row', borderTopWidth: 1, borderTopColor: colors.line, backgroundColor: colors.white },
    priceFrom: { backgroundColor: '#FFF1EC' },
    rowLabel: { width: 132, color: colors.navy, fontSize: 14, fontWeight: '800', paddingHorizontal: 12, paddingVertical: 14 },
    bodyCell: { width: 150, color: colors.muted, fontSize: 15, lineHeight: 22, paddingHorizontal: 12, paddingVertical: 14 },
    priceFromText: { color: colors.navy, fontWeight: '800' },
});
