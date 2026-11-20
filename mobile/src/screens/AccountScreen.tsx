import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useRef, type ComponentRef, type ReactNode } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';

import { useAuth } from '../context/AuthContext';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';
import { CountryAndFooter } from './service-end';
import { SiteBar } from './site-bar';

export function AccountScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { customer, logout } = useAuth();

    if (!customer) {
        return (
            <Page>
            <View style={styles.screen}>
                <Text style={styles.kicker}>Account</Text>
                <Text style={styles.title}>Your BacklinkPro account</Text>
                <Text style={styles.copy}>Sign in with the same customer email used on the website. The app keeps that Laravel session.</Text>
                <Pressable style={styles.button} onPress={() => navigation.navigate('Login')}>
                    <Text style={styles.buttonText}>Sign in</Text>
                </Pressable>
                <Pressable style={styles.secondary} onPress={() => navigation.navigate('Register')}>
                    <Text style={styles.secondaryText}>Create an account</Text>
                </Pressable>
            </View>
            </Page>
        );
    }

    return (
        <Page>
        <View style={styles.screen}>
            <Text style={styles.kicker}>Account</Text>
            <Text style={styles.title}>{customer.name}</Text>
            <Text style={styles.copy}>{customer.email}</Text>
            <View style={styles.card}>
                <Text style={styles.cardCopy}>This is the same customer session used by the website. The full dashboard, wallet and profile forms are the next account phase.</Text>
            </View>
            <Pressable style={styles.secondary} onPress={() => { void logout(); }}>
                <Text style={styles.secondaryText}>Log out</Text>
            </Pressable>
        </View>
        </Page>
    );
}

function Page({ children }: { children: ReactNode }) {
    const scrollRef = useRef<ComponentRef<typeof ScrollView>>(null);
    const endY = useRef(0);

    return (
        <View style={styles.frame}>
            <SiteBar />
            <ScrollView
                ref={scrollRef}
                style={styles.frame}
            >
                {children}
                <View onLayout={(event) => { endY.current = event.nativeEvent.layout.y; }}>
                    <CountryAndFooter onBrowseCountry={() => scrollRef.current?.scrollTo({ y: endY.current, animated: true })} />
                </View>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    frame: { flex: 1, backgroundColor: colors.canvas },
    screen: { backgroundColor: colors.canvas, padding: 20 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    title: { marginTop: 8, color: colors.navy, fontSize: 32, fontWeight: '800' },
    copy: { marginTop: 8, color: colors.muted, fontSize: 16, lineHeight: 24 },
    card: { marginTop: 16, backgroundColor: colors.white, borderRadius: 20, borderWidth: 1, borderColor: colors.line, padding: 16 },
    cardCopy: { color: colors.muted, fontSize: 15, lineHeight: 22 },
    button: { marginTop: 20, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center' },
    buttonText: { color: colors.white, fontSize: 16, fontWeight: '800' },
    secondary: { marginTop: 12, minHeight: 48, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center' },
    secondaryText: { color: colors.white, fontSize: 16, fontWeight: '800' },
});
