import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { apiUrl } from '../config';
import { useAuth } from '../context/AuthContext';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';
import { openDashboard } from './account-pages';
import { SiteBar } from './site-bar';

export function LoginScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { login } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [pending, setPending] = useState(false);

    async function submit() {
        setError('');
        setPending(true);
        try {
            await login(email, password);
            openDashboard(navigation);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Sign-in failed.');
        } finally {
            setPending(false);
        }
    }

    return (
        <View style={styles.frame}>
            <SiteBar />
            <ScrollView style={styles.frame}>
            <View style={styles.screen}>
            <View style={styles.brandRow}>
                <View style={styles.mark}><Text style={styles.markText}>↗</Text></View>
                <Text style={styles.brand}>BacklinkPro</Text>
            </View>
            <Text style={styles.kicker}>Customer account</Text>
            <Text style={styles.title}>Sign in</Text>
            <Text style={styles.copy}>Use the same email and password as the website. Laravel keeps the session.</Text>
            {error ? <Text style={styles.error} accessibilityRole="alert">{error}</Text> : null}
            <Text style={styles.label}>Email</Text>
            <TextInput
                style={styles.input}
                autoCapitalize="none"
                autoComplete="email"
                keyboardType="email-address"
                value={email}
                onChangeText={setEmail}
                placeholder="you@example.com"
                placeholderTextColor={colors.muted}
            />
            <Text style={styles.label}>Password</Text>
            <TextInput
                style={styles.input}
                secureTextEntry
                autoComplete="password"
                value={password}
                onChangeText={setPassword}
                placeholder="Password"
                placeholderTextColor={colors.muted}
            />
            <Pressable style={[styles.button, pending && styles.buttonDisabled]} disabled={pending} onPress={submit}>
                {pending ? <ActivityIndicator color={colors.white} /> : <Text style={styles.buttonText}>Sign in</Text>}
            </Pressable>
            <Pressable onPress={() => navigation.navigate('Register')} style={styles.linkButton}>
                <Text style={styles.link}>Create an account</Text>
            </Pressable>
            <Text style={styles.endpoint}>API {apiUrl}</Text>
            </View>
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    frame: { flex: 1, backgroundColor: colors.canvas },
    screen: { backgroundColor: colors.canvas, paddingHorizontal: 24, paddingTop: 28, paddingBottom: 28 },
    brandRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    mark: { width: 36, height: 36, borderRadius: 8, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center' },
    markText: { color: colors.white, fontSize: 18, fontWeight: '800' },
    brand: { color: colors.navy, fontSize: 22, fontWeight: '800' },
    kicker: { marginTop: 28, color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    title: { marginTop: 8, color: colors.navy, fontSize: 34, fontWeight: '800' },
    copy: { marginTop: 8, color: colors.muted, fontSize: 15, lineHeight: 22 },
    error: { marginTop: 16, backgroundColor: colors.dangerBg, color: colors.danger, borderRadius: 12, padding: 12, overflow: 'hidden' },
    label: { marginTop: 16, marginBottom: 6, color: colors.navy, fontSize: 13, fontWeight: '700' },
    input: { minHeight: 48, borderWidth: 1, borderColor: colors.line, borderRadius: 12, backgroundColor: colors.white, paddingHorizontal: 14, color: colors.navy, fontSize: 16 },
    button: { marginTop: 22, minHeight: 48, borderRadius: 12, backgroundColor: colors.brand, alignItems: 'center', justifyContent: 'center' },
    buttonDisabled: { opacity: 0.7 },
    buttonText: { color: colors.white, fontSize: 16, fontWeight: '800' },
    linkButton: { marginTop: 16, minHeight: 44, alignItems: 'center', justifyContent: 'center' },
    link: { color: colors.brandDark, fontSize: 15, fontWeight: '800' },
    endpoint: { marginTop: 12, color: colors.muted, fontSize: 12 },
});
