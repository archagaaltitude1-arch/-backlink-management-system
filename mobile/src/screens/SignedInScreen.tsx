import { Pressable, StyleSheet, Text, View } from 'react-native';

import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';

export function SignedInScreen() {
    const { customer, logout } = useAuth();

    return (
        <View style={styles.screen}>
            <Text style={styles.kicker}>Session kept</Text>
            <Text style={styles.title}>Signed in</Text>
            <View style={styles.card}>
                <Text style={styles.name}>{customer?.name}</Text>
                <Text style={styles.email}>{customer?.email}</Text>
                <Text style={styles.copy}>This session is the same Laravel customer session used by the website.</Text>
            </View>
            <Pressable style={styles.button} onPress={() => { void logout(); }}>
                <Text style={styles.buttonText}>Log out</Text>
            </Pressable>
        </View>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.canvas, paddingHorizontal: 24, paddingTop: 28 },
    kicker: { color: colors.brandDark, fontSize: 12, fontWeight: '800', letterSpacing: 1, textTransform: 'uppercase' },
    title: { marginTop: 8, color: colors.navy, fontSize: 34, fontWeight: '800' },
    card: { marginTop: 20, backgroundColor: colors.white, borderRadius: 20, borderWidth: 1, borderColor: colors.line, padding: 20 },
    name: { color: colors.navy, fontSize: 22, fontWeight: '800' },
    email: { marginTop: 4, color: colors.muted, fontSize: 15 },
    copy: { marginTop: 12, color: colors.muted, fontSize: 14, lineHeight: 21 },
    button: { marginTop: 20, minHeight: 48, borderRadius: 12, backgroundColor: colors.navy, alignItems: 'center', justifyContent: 'center' },
    buttonText: { color: colors.white, fontSize: 16, fontWeight: '800' },
});
