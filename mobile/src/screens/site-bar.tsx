import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useState } from 'react';
import { Modal, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { apiRequest } from '../api/client';
import { money, type CartItem, type Shell } from '../api/home';
import { useAuth } from '../context/AuthContext';
import { useHome } from '../hooks/useHome';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';

export function SiteBar() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { customer, logout } = useAuth();
    const { shell, reload } = useHome();
    const [menu, setMenu] = useState(false);
    const [cart, setCart] = useState(false);

    function openAccount() {
        setMenu(false);

        if (customer) {
            navigation.navigate('MenuPage', { path: '/customer/dashboard' });

            return;
        }

        navigation.navigate('Login');
    }

    return (
        <View>
            <View style={styles.header}>
                <Pressable onPress={() => navigation.navigate('Home')} style={styles.logo}>
                    <Text style={styles.mark}>↗</Text>
                    <Text style={styles.brand}>BacklinkPro</Text>
                </Pressable>
                <View style={styles.headerActions}>
                    <Pressable style={styles.iconButton} accessibilityLabel={`Wishlist, ${shell.wishlistCount} saved`} onPress={() => navigation.navigate('MenuPage', { path: '/wishlist' })}>
                        <Text style={styles.icon}>♡</Text>
                        {shell.wishlistCount > 0 ? <Text style={styles.badgeCount}>{shell.wishlistCount}</Text> : null}
                    </Pressable>
                    <Pressable style={styles.iconButton} accessibilityLabel={`Cart, ${shell.cartCount} items`} onPress={() => setCart(true)}>
                        <Text style={styles.icon}>▣</Text>
                        <Text style={styles.badgeCount}>{shell.cartCount}</Text>
                    </Pressable>
                    <Pressable style={styles.iconButton} accessibilityLabel="Open menu" onPress={() => setMenu((value) => !value)}>
                        <Text style={styles.icon}>☰</Text>
                    </Pressable>
                </View>
            </View>
            {menu ? <SiteMenu shell={shell} signedIn={Boolean(customer)} onClose={() => setMenu(false)} onAccount={openAccount} onLogout={() => { setMenu(false); void logout(); }} /> : null}
            <CartDrawer open={cart} shell={shell} onClose={() => setCart(false)} onChanged={reload} />
        </View>
    );
}

function SiteMenu({ shell, signedIn, onClose, onAccount, onLogout }: { shell: Shell; signedIn: boolean; onClose: () => void; onAccount: () => void; onLogout: () => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();

    function open(target: string) {
        onClose();
        navigation.navigate('MenuPage', { path: appPath(target) });
    }

    return (
        <ScrollView style={styles.menu}>
            <Pressable onPress={() => { onClose(); navigation.navigate('Home'); }}><Text style={styles.menuItem}>Home</Text></Pressable>
            <Pressable onPress={() => { onClose(); navigation.navigate('Services'); }}><Text style={styles.menuLabel}>Services</Text></Pressable>
            {shell.services.map((item) => <Pressable key={item.slug} onPress={() => { onClose(); navigation.navigate('Service', { slug: item.slug }); }}><Text style={styles.menuItem}>{item.name}</Text><Text style={styles.menuLine}>{item.line}</Text></Pressable>)}
            <Pressable onPress={() => { onClose(); navigation.navigate('Services'); }}><Text style={styles.menuLink}>Compare all services →</Text></Pressable>
            <Pressable onPress={() => open('/niches')}><Text style={styles.menuLabel}>Niches</Text></Pressable>
            <View style={styles.menuGrid}>{shell.niches.map((item) => <Pressable key={item.label} onPress={() => open(item.href || `/niches/${slugify(item.label)}`)}><Text style={styles.menuGridItem}>{item.label}</Text></Pressable>)}</View>
            <Pressable onPress={() => open('/packages')}><Text style={styles.menuLabel}>Packages</Text></Pressable>
            {shell.packages.flatMap((group) => group.sections ?? []).map((section) => (
                <View key={section.title}>
                    <Text style={styles.menuSection}>{section.title}</Text>
                    {(section.items ?? []).map((item) => <Pressable key={item.slug || item.label} onPress={() => open(item.href || `/packages/${item.slug}`)}><Text style={styles.menuItem}>{item.label}</Text></Pressable>)}
                </View>
            ))}
            <Pressable onPress={() => open('/sites')}><Text style={styles.menuLabel}>Sites</Text></Pressable>
            {shell.sites.map((item) => <Pressable key={item.slug || item.label} onPress={() => open(item.href || `/sites/${item.slug}`)}><Text style={styles.menuItem}>{item.label}</Text></Pressable>)}
            <Pressable onPress={() => open('/tools')}><Text style={styles.menuLabel}>SEO tools</Text></Pressable>
            {shell.tools.map((item) => <Pressable key={item.slug || item.label} onPress={() => open(item.href || `/tools/${item.slug}`)}><Text style={styles.menuItem}>{item.label}</Text></Pressable>)}
            {signedIn ? <Pressable onPress={onAccount}><Text style={styles.navyButtonText}>My Dashboard</Text></Pressable> : <Pressable onPress={onAccount}><Text style={styles.primaryText}>Login</Text></Pressable>}
            {signedIn ? <Pressable onPress={onLogout}><Text style={styles.menuItem}>Logout</Text></Pressable> : null}
        </ScrollView>
    );
}

function CartDrawer({ open, shell, onClose, onChanged }: { open: boolean; shell: Shell; onClose: () => void; onChanged: () => void }) {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    async function remove(item: CartItem) {
        await apiRequest('/cart', { method: 'DELETE', json: true, body: { line: item.line, domain: item.domain } });
        onChanged();
    }

    return (
        <Modal visible={open} animationType="slide" transparent onRequestClose={onClose}>
            <View style={styles.drawerWrap}>
                <Pressable style={styles.backdrop} onPress={onClose} accessibilityLabel="Close cart" />
                <View style={styles.drawer}>
                    <View style={styles.rowBetween}><Text style={styles.drawerTitle}>Shopping Cart ({shell.cartCount})</Text><Pressable onPress={onClose}><Text style={styles.icon}>✕</Text></Pressable></View>
                    <ScrollView>
                        {shell.cartItems.length === 0 ? <Text style={styles.cardCopy}>Your cart is empty. Add a package or publisher to get started.</Text> : shell.cartItems.map((item) => (
                            <View key={item.line} style={styles.cartItem}>
                                <View style={styles.flex}>
                                    <Text style={styles.cardTitle}>{item.title}</Text>
                                    <Text style={styles.note}>Order Type: {item.order_label}</Text>
                                    {item.websites_label ? <Text style={styles.note}>{item.websites_label}</Text> : null}
                                    <Text style={styles.price}>{item.money}</Text>
                                </View>
                                <Pressable onPress={() => { void remove(item); }} accessibilityLabel={`Remove ${item.title}`}><Text style={styles.icon}>✕</Text></Pressable>
                            </View>
                        ))}
                    </ScrollView>
                    <Text style={styles.subtotal}>Subtotal ({shell.cartCount} {shell.cartCount === 1 ? 'item' : 'items'})  {money(shell.cartSubtotal)}</Text>
                    <Pressable onPress={() => { onClose(); navigation.navigate('MenuPage', { path: '/cart/checkout' }); }}><Text style={styles.navyButtonText}>CHECKOUT</Text></Pressable>
                    <Pressable onPress={() => { onClose(); navigation.navigate('MenuPage', { path: '/cart' }); }}><Text style={styles.menuLink}>View Cart</Text></Pressable>
                </View>
            </View>
        </Modal>
    );
}

function appPath(target: string): string {
    if (target.startsWith('/')) {
        return target;
    }

    try {
        const url = new URL(target);

        return `${url.pathname}${url.search}`;
    } catch {
        return `/${target}`;
    }
}

function slugify(value: string): string {
    return value.toLowerCase().replace(/&/g, ' ').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

const styles = StyleSheet.create({
    header: { minHeight: 64, paddingHorizontal: 16, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: colors.line, backgroundColor: colors.white },
    logo: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    mark: { width: 36, height: 36, borderRadius: 8, backgroundColor: colors.brand, color: colors.white, textAlign: 'center', lineHeight: 36, fontWeight: '800', overflow: 'hidden' },
    brand: { color: colors.navy, fontSize: 20, fontWeight: '800' },
    headerActions: { flexDirection: 'row', alignItems: 'center' },
    iconButton: { width: 44, height: 44, alignItems: 'center', justifyContent: 'center' },
    icon: { color: colors.navy, fontSize: 18, fontWeight: '700' },
    badgeCount: { position: 'absolute', top: 4, right: 2, minWidth: 18, borderRadius: 9, backgroundColor: colors.brandDark, color: colors.white, fontSize: 10, fontWeight: '800', textAlign: 'center', overflow: 'hidden', paddingHorizontal: 4 },
    menu: { maxHeight: 420, borderTopWidth: 1, borderTopColor: colors.line, backgroundColor: colors.white, paddingHorizontal: 16 },
    menuLabel: { marginTop: 12, color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    menuItem: { paddingVertical: 8, color: colors.navy, fontWeight: '700' },
    menuLine: { color: colors.muted, fontSize: 13 },
    menuLink: { paddingVertical: 8, color: colors.brandDark, fontWeight: '700' },
    menuSection: { marginTop: 8, color: colors.navy, fontWeight: '800' },
    menuGrid: { flexDirection: 'row', flexWrap: 'wrap' },
    menuGridItem: { width: '50%', paddingVertical: 6, color: colors.navy, fontWeight: '700' },
    navyButtonText: { marginVertical: 12, minHeight: 48, borderRadius: 12, backgroundColor: colors.navy, color: colors.white, fontWeight: '800', textAlign: 'center', lineHeight: 48, overflow: 'hidden' },
    primaryText: { marginVertical: 12, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, color: colors.white, fontWeight: '800', textAlign: 'center', lineHeight: 48, overflow: 'hidden' },
    drawerWrap: { flex: 1, justifyContent: 'flex-end' },
    backdrop: { flex: 1, backgroundColor: 'rgba(21,32,41,0.45)' },
    drawer: { maxHeight: '80%', backgroundColor: colors.white, borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20 },
    rowBetween: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    drawerTitle: { color: colors.navy, fontSize: 22, fontWeight: '800' },
    cardCopy: { marginTop: 8, color: colors.muted, fontSize: 14, lineHeight: 22 },
    cartItem: { flexDirection: 'row', gap: 12, borderBottomWidth: 1, borderBottomColor: '#EDEFF1', paddingVertical: 14 },
    flex: { flex: 1 },
    cardTitle: { color: colors.navy, fontSize: 16, fontWeight: '800' },
    note: { color: colors.muted, fontSize: 14 },
    price: { marginTop: 8, color: colors.brandDark, fontSize: 16, fontWeight: '800' },
    subtotal: { marginTop: 12, color: colors.navy, fontSize: 15, fontWeight: '800' },
});
