import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useRef, useState, type ComponentRef } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';

import { useAuth } from '../context/AuthContext';
import type { RootStack } from '../navigation/types';
import { colors } from '../theme';
import { openDashboard } from './account-pages';
import { CountryAndFooter } from './service-end';
import { SiteBar } from './site-bar';

export function RegisterScreen() {
    const navigation = useNavigation<NativeStackNavigationProp<RootStack>>();
    const { register } = useAuth();
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirmation, setConfirmation] = useState('');
    const [error, setError] = useState('');
    const [pending, setPending] = useState(false);
    const scrollRef = useRef<ComponentRef<typeof ScrollView>>(null);
    const endY = useRef(0);

    async function submit() {
        setError('');
        setPending(true);
        try {
            await register(name, email, password, confirmation);
            openDashboard(navigation);
        } catch (reason) {
            setError(reason instanceof Error ? reason.message : 'Registration failed.');
        } finally {
            setPending(false);
        }
    }

    return (
        <View style={styles.screen}>
            <SiteBar />
            <ScrollView
                ref={scrollRef}
                style={styles.fill}
                contentContainerStyle={styles.content}
                keyboardShouldPersistTaps="handled"
            >
            <Text style={styles.kicker}>Customer account</Text>
            <Text style={styles.title}>Create your account</Text>
            <Text style={styles.copy}>This creates a customer on the existing Laravel site. The password must be at least 8 characters.</Text>
            {error ? <Text style={styles.error} accessibilityRole="alert">{error}</Text> : null}
            <Field label="Full name" value={name} onChangeText={setName} autoComplete="name" />
            <Field label="Email" value={email} onChangeText={setEmail} autoCapitalize="none" keyboardType="email-address" autoComplete="email" />
            <Field label="Password" value={password} onChangeText={setPassword} secureTextEntry autoComplete="new-password" />
            <Field label="Confirm password" value={confirmation} onChangeText={setConfirmation} secureTextEntry autoComplete="new-password" />
            <Pressable style={[styles.button, pending && styles.buttonDisabled]} disabled={pending} onPress={submit}>
                {pending ? <ActivityIndicator color={colors.white} /> : <Text style={styles.buttonText}>Create account</Text>}
            </Pressable>
            <Pressable onPress={() => navigation.navigate('Login')} style={styles.linkButton}>
                <Text style={styles.link}>Already have an account? Sign in</Text>
            </Pressable>
            <View style={styles.end} onLayout={(event) => { endY.current = event.nativeEvent.layout.y; }}>
                <CountryAndFooter onBrowseCountry={() => scrollRef.current?.scrollTo({ y: endY.current, animated: true })} />
            </View>
            </ScrollView>
        </View>
    );
}

function Field(props: {
    label: string;
    value: string;
    onChangeText: (value: string) => void;
    secureTextEntry?: boolean;
    autoCapitalize?: 'none' | 'sentences';
    keyboardType?: 'default' | 'email-address';
    autoComplete?: 'name' | 'email' | 'new-password';
}) {
    return (
        <>
            <Text style={styles.label}>{props.label}</Text>
            <TextInput
                style={styles.input}
                value={props.value}
                onChangeText={props.onChangeText}
                secureTextEntry={props.secureTextEntry}
                autoCapitalize={props.autoCapitalize ?? 'sentences'}
                keyboardType={props.keyboardType}
                autoComplete={props.autoComplete}
                placeholderTextColor={colors.muted}
            />
        </>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.canvas },
    fill: { flex: 1 },
    content: { paddingHorizontal: 24, paddingTop: 28, paddingBottom: 40 },
    end: { marginHorizontal: -24 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
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
});
